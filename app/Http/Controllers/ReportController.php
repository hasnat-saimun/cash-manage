<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\clientCreation;
use App\Models\transaction;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportController extends Controller
{
    // Client-wise transaction report (daily or custom date range)
    public function index(Request $request)
    {
        $clients = clientCreation::orderBy('client_name')->get();

        $clientId = $request->query('client_id');
        $reportType = $request->query('report_type', 'daily'); // 'daily' or 'custom'
        $date = $request->query('date');
        $from = $request->query('from_date');
        $to = $request->query('to_date');

        // validate custom
        if ($reportType === 'custom' && (empty($from) || empty($to))) {
            return redirect()->route('reports.clientTransaction')
                ->withInput()
                ->withErrors(['date' => 'Please provide both From and To dates for custom date range.']);
        }

        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $rangeLabel = '';
        $openingBalance = 0.0;
        $closingBalance = null; // ensure variable always exists to avoid undefined variable in compact()
        $txnCount = 0;
        $debitTxnCount = 0;
        $creditTxnCount = 0;

        if ($clientId) {
            try {
                if ($reportType === 'custom' && $from && $to) {
                    $start = Carbon::parse($from)->startOfDay();
                    $end = Carbon::parse($to)->endOfDay();
                    $rangeLabel = $start->toDateString() . ' — ' . $end->toDateString();
                } else {
                    $dVal = $date ?? $from ?? Carbon::today()->toDateString();
                    $d = Carbon::parse($dVal);
                    $start = $d->startOfDay();
                    $end = $d->endOfDay();
                    $rangeLabel = $d->toDateString();
                }
            } catch (\Exception $e) {
                return back()->withErrors(['date' => 'Invalid date format. Use YYYY-MM-DD.']);
            }

            // current (latest) balance from client_balances (single source of truth)
            $currentBalance = (float) DB::table('client_balances')->where('client_id', $clientId)->value('balance') ?? 0.0;

            // sum of effects from start date up to now (transactions with date >= start)
            $sumFromStartToNow = DB::table('transactions')
                ->where('transaction_client_name', $clientId)
                ->where('date', '>=', $start->toDateString())
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN LOWER(type) = 'credit' THEN amount ELSE 0 END),0) as csum,
                    COALESCE(SUM(CASE WHEN LOWER(type) = 'debit' THEN amount ELSE 0 END),0) as dsum
                ")->first();

            $sumFromStartToNow = (float)($sumFromStartToNow->csum ?? 0) - (float)($sumFromStartToNow->dsum ?? 0);

            // opening balance at start = currentBalance - effects from start..now
            $openingBalance = $currentBalance - $sumFromStartToNow;

            // fetch transactions inside the requested range
            $txns = transaction::where('transaction_client_name', $clientId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('date')
                ->get();
            $txnCount = $txns->count();

            $running = $openingBalance;
            foreach ($txns as $t) {
                $effect = 0.0;
                $type = strtolower(trim($t->type ?? ''));
                if ($type === 'debit') {
                    $effect = -((float)$t->amount);
                    $totalDebit += (float)$t->amount;
                    $debitTxnCount++;
                } else {
                    $effect = (float)$t->amount;
                    $totalCredit += (float)$t->amount;
                    $creditTxnCount++;
                }
                $running += $effect;

                $sourceVal = $t->transaction_source ?? $t->source ?? null;
                $sourceName = '';
                if ($sourceVal) {
                    $sourceName = is_numeric($sourceVal)
                        ? (DB::table('sources')->where('id', $sourceVal)->value('source_name') ?? (string)$sourceVal)
                        : (string)$sourceVal;
                }

                $rows[] = [
                    'date' => (string) ($t->date ?? ''),
                    'description' => $t->description ?? '',
                    'source' => $sourceName,
                    'debit' => $type === 'debit' ? (float)$t->amount : 0.0,
                    'credit' => $type !== 'debit' ? (float)$t->amount : 0.0,
                    'balance' => $running,
                ];
            }

            // set closingBalance after processing transactions
            $closingBalance = $running;
        }

        $grandTotal = $totalCredit - $totalDebit;

        // pass closingBalance to view
        return view('reports.clientTransaction', compact(
            'clients','rows','totalDebit','totalCredit','grandTotal',
            'clientId','reportType','date','from','to','rangeLabel','openingBalance','closingBalance','txnCount','debitTxnCount','creditTxnCount'
        ));
    }

    // Client report PDF
    public function clientTransactionPdf(Request $request)
    {
        $clientId = $request->query('client_id');
        if (!$clientId) {
            return redirect()->route('reports.clientTransaction')->with('error','Please select a client to download PDF.');
        }

        $reportType = $request->query('report_type', 'daily');
        $date = $request->query('date');
        $from = $request->query('from_date');
        $to = $request->query('to_date');

        try {
            if ($reportType === 'custom' && $from && $to) {
                $start = Carbon::parse($from)->startOfDay();
                $end = Carbon::parse($to)->endOfDay();
                $rangeLabel = $start->toDateString() . ' — ' . $end->toDateString();
            } else {
                $dVal = $date ?? $from ?? Carbon::today()->toDateString();
                $d = Carbon::parse($dVal);
                $start = $d->startOfDay();
                $end = $d->endOfDay();
                $rangeLabel = $d->toDateString();
            }
        } catch (\Exception $e) {
            return redirect()->route('reports.clientTransaction')->with('error','Invalid date format.');
        }

        $currentBalance = (float) DB::table('client_balances')->where('client_id', $clientId)->value('balance') ?? 0.0;
        $sumFromStartToNow = DB::table('transactions')
            ->where('transaction_client_name', $clientId)
            ->where('date', '>=', $start->toDateString())
            ->selectRaw("COALESCE(SUM(CASE WHEN LOWER(type)='credit' THEN amount ELSE 0 END),0) as csum, COALESCE(SUM(CASE WHEN LOWER(type)='debit' THEN amount ELSE 0 END),0) as dsum")
            ->first();
        $sumFromStartToNow = (float)($sumFromStartToNow->csum ?? 0) - (float)($sumFromStartToNow->dsum ?? 0);
        $openingBalance = $currentBalance - $sumFromStartToNow;

        // Build rows and totals exactly like the screen report
        $txns = transaction::where('transaction_client_name', $clientId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();
        $rows = [];
        $totalDebit = 0.0; $totalCredit = 0.0; $running = $openingBalance;
        foreach ($txns as $t) {
            $type = strtolower(trim($t->type ?? ''));
            $debit = $type === 'debit' ? (float)$t->amount : 0.0;
            $credit = $type === 'debit' ? 0.0 : (float)$t->amount;
            $totalDebit += $debit; $totalCredit += $credit; $running += ($credit - $debit);
            $sourceVal = $t->transaction_source ?? $t->source ?? null;
            $sourceName = '';
            if ($sourceVal) {
                $sourceName = is_numeric($sourceVal)
                    ? (DB::table('sources')->where('id', $sourceVal)->value('source_name') ?? (string)$sourceVal)
                    : (string)$sourceVal;
            }
            $rows[] = [
                'date' => (string) ($t->date ?? ''),
                'description' => $t->description ?? '',
                'source' => $sourceName,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $running,
            ];
        }
        $closingBalance = $running;
        $grandTotal = $totalCredit - $totalDebit;
        $isDaily = ($reportType === 'daily');
        $colCount = $isDaily ? 5 : 6; // same as view: daily excludes Date
        $colsBeforeBalance = $colCount - 1;

        $client = clientCreation::find($clientId);
        $clientName = $client?->client_name ?? (string)$clientId;
        $bizId = request()->session()->get('business_id');
        $bizName = DB::table('businesses')->where('id', $bizId)->value('name') ?? config('app.name');
        $generatedAt = Carbon::now()->format('Y-m-d H:i');

        $useBanglaDigits = true; // keep mPDF with Bengali numerals
        $html = view('reports.pdf-client-transaction', compact(
            'client','clientName','rangeLabel','openingBalance','rows','closingBalance','totalDebit','totalCredit','grandTotal','isDaily','colCount','colsBeforeBalance','bizName','generatedAt','useBanglaDigits'
        ))->render();

        $fileName = 'client-transactions-' . preg_replace('/[^A-Za-z0-9_\-]/','_', substr($clientName,0,40)) . '-' . $start->toDateString() . '.pdf';

        // Prefer Browsershot (Chromium/Chrome) for full Unicode/Indic shaping if enabled
        if (env('PDF_ENGINE') === 'browsershot' && class_exists('Spatie\\Browsershot\\Browsershot')) {
            try {
                $bs = \Spatie\Browsershot\Browsershot::html($html)
                    ->showBackground()
                    ->format('A4')
                    ->margins(10, 10, 10, 10)
                    ->timeout(120);
                if ($chromePath = env('CHROME_PATH')) {
                    $bs->setChromePath($chromePath);
                }
                $binary = $bs->pdf();
                return response($binary, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
                ]);
            } catch (\Throwable $e) {
                // fall back to DomPDF below
            }
        }

        // mPDF engine with OpenType layout support for Indic shaping
        if (env('PDF_ENGINE') === 'mpdf' && class_exists('Mpdf\\Mpdf')) {
            try {
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'margin_top' => 10,
                    'margin_right' => 10,
                    'margin_bottom' => 10,
                    'margin_left' => 10,
                ]);
                $mpdf->autoScriptToLang = true;
                $mpdf->autoLangToFont = true;
                if (property_exists($mpdf, 'useOTL')) { $mpdf->useOTL = 0xFF; }
                if (property_exists($mpdf, 'useKerning')) { $mpdf->useKerning = true; }
                if (property_exists($mpdf, 'useSubstitutions')) { $mpdf->useSubstitutions = 1; }
                if (method_exists($mpdf, 'SetDefaultFont')) { $mpdf->SetDefaultFont('notosansbengali'); }
                // If local NotoSansBengali fonts exist under public/fonts, prefer them
                $fontDir = public_path('fonts');
                $reg = $fontDir.DIRECTORY_SEPARATOR.'NotoSansBengali-Regular.ttf';
                $bold = $fontDir.DIRECTORY_SEPARATOR.'NotoSansBengali-Bold.ttf';
                if (is_file($reg) && is_file($bold)) {
                    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
                    $fontDirs = $defaultConfig['fontDir'];
                    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
                    $fontData = $defaultFontConfig['fontdata'];
                    $mpdf = new \Mpdf\Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'margin_top' => 10,
                        'margin_right' => 10,
                        'margin_bottom' => 10,
                        'margin_left' => 10,
                        'fontDir' => array_merge($fontDirs, [$fontDir]),
                        'fontdata' => $fontData + [
                            'notosansbengali' => [
                                'R' => 'NotoSansBengali-Regular.ttf',
                                'B' => 'NotoSansBengali-Bold.ttf',
                            ],
                        ],
                        'default_font' => 'notosansbengali',
                    ]);
                    $mpdf->autoScriptToLang = true;
                    $mpdf->autoLangToFont = true;
                    if (property_exists($mpdf, 'useOTL')) { $mpdf->useOTL = 0xFF; }
                    if (property_exists($mpdf, 'useKerning')) { $mpdf->useKerning = true; }
                    if (property_exists($mpdf, 'useSubstitutions')) { $mpdf->useSubstitutions = 1; }
                    if (method_exists($mpdf, 'SetDefaultFont')) { $mpdf->SetDefaultFont('notosansbengali'); }
                }
                $mpdf->WriteHTML($html);
                return response($mpdf->Output($fileName, \Mpdf\Output\Destination::STRING_RETURN), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
                ]);
            } catch (\Throwable $e) {
                // fall back below
            }
        }

        if (class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4','portrait');
            return $pdf->download($fileName);
        }
        if (class_exists('Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
            ]);
        }
        return redirect()->route('reports.clientTransaction')->with('error','PDF export not available. Please run: composer require barryvdh/laravel-dompdf');
    }

    // Export CSV for client transactions using same filters as index
    public function export(Request $request)
    {
        $clientId = $request->query('client_id');
        if (!$clientId) {
            return redirect()->route('reports.clientTransaction')->with('error','Please select a client to export.');
        }

        $reportType = $request->query('report_type', 'daily');
        $date = $request->query('date');
        $from = $request->query('from_date');
        $to = $request->query('to_date');

        if ($reportType === 'custom' && (empty($from) || empty($to))) {
            return redirect()->route('reports.clientTransaction')
                ->withInput()
                ->withErrors(['date' => 'Please provide both From and To dates for custom date range before exporting.']);
        }

        try {
            if ($reportType === 'custom' && $from && $to) {
                $start = Carbon::parse($from)->startOfDay();
                $end = Carbon::parse($to)->endOfDay();
            } else {
                $dVal = $date ?? $from ?? Carbon::today()->toDateString();
                $d = Carbon::parse($dVal);
                $start = $d->startOfDay();
                $end = $d->endOfDay();
            }
        } catch (\Exception $e) {
            return redirect()->route('reports.clientTransaction')->with('error','Invalid date format.');
        }

        // get current balance and opening as in index
        $currentBalance = (float) DB::table('client_balances')->where('client_id', $clientId)->value('balance') ?? 0.0;
        $sumFromStartToNow = DB::table('transactions')
            ->where('transaction_client_name', $clientId)
            ->where('date', '>=', $start->toDateString())
            ->selectRaw("
                COALESCE(SUM(CASE WHEN LOWER(type) = 'credit' THEN amount ELSE 0 END),0) as csum,
                COALESCE(SUM(CASE WHEN LOWER(type) = 'debit' THEN amount ELSE 0 END),0) as dsum
            ")->first();
        $sumFromStartToNow = (float)($sumFromStartToNow->csum ?? 0) - (float)($sumFromStartToNow->dsum ?? 0);
        $openingBalance = $currentBalance - $sumFromStartToNow;

        $txns = transaction::where('transaction_client_name', $clientId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $client = clientCreation::find($clientId);
        $clientNamePart = $client?->client_name ? preg_replace('/[^A-Za-z0-9_\-]/','_',substr($client->client_name,0,30)) : $clientId;
        $fileName = 'client-transactions-' . $clientNamePart . '-' . $start->toDateString() . '.csv';

        $response = new StreamedResponse(function() use ($txns, $openingBalance) {
            $handle = fopen('php://output', 'w');

            // Write opening balance row first
            fputcsv($handle, ['Opening Balance','', ''.number_format($openingBalance,2,'.','')]);
            fputcsv($handle, []); // blank row

            // header row — include Source and Balance
            fputcsv($handle, ['Date','Description','Source','Debit','Credit','Balance']);

            $running = $openingBalance;
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($txns as $t) {
                $type = strtolower(trim($t->type ?? ''));
                $debit = 0.0;
                $credit = 0.0;
                if ($type === 'debit') {
                    $debit = (float)$t->amount;
                    $totalDebit += $debit;
                    $running -= $debit;
                } else {
                    $credit = (float)$t->amount;
                    $totalCredit += $credit;
                    $running += $credit;
                }

                $sourceVal = $t->transaction_source ?? $t->source ?? null;
                $sourceName = '';
                if ($sourceVal) {
                    $sourceName = is_numeric($sourceVal)
                        ? (DB::table('sources')->where('id', $sourceVal)->value('source_name') ?? (string)$sourceVal)
                        : (string)$sourceVal;
                }

                fputcsv($handle, [
                    $t->date ?? '',
                    $t->description ?? '',
                    $sourceName,
                    $debit ? number_format($debit,2,'.','') : '',
                    $credit ? number_format($credit,2,'.','') : '',
                    number_format($running,2,'.','')
                ]);
            }

            // Write closing balance row before totals
            fputcsv($handle, []);
            fputcsv($handle, ['Closing Balance','', ''.number_format($running,2,'.','')]);

            // blank row then totals (adjusted for new column)
            fputcsv($handle, []);
            fputcsv($handle, ['Totals','','',''.number_format($totalDebit,2,'.',''),''.number_format($totalCredit,2,'.',''), '']);
            fputcsv($handle, ['Grand Total (Credit - Debit)', '', '', '', '', ''.number_format(($totalCredit - $totalDebit),2,'.','')]);

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}\"");
        return $response;
    }

    // Account-wise bank transaction report (view)
    public function bankTransactionReport(Request $request)
    {
        $accounts = DB::table('bank_accounts')
            ->where('bank_accounts.business_id', request()->session()->get('business_id'))
            ->orderBy('account_name')
            ->get();

        $accountId = $request->query('account_id');
        $reportType = $request->query('report_type', 'daily');
        $date = $request->query('date');
        $from = $request->query('from_date');
        $to = $request->query('to_date');

        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $rangeLabel = '';
        $openingBalance = 0.0;
        $closingBalance = null;

        if ($accountId) {
            try {
                if ($reportType === 'custom' && ($from && $to)) {
                    $start = Carbon::parse($from)->startOfDay();
                    $end = Carbon::parse($to)->endOfDay();
                    $rangeLabel = $start->toDateString() . ' — ' . $end->toDateString();
                } else {
                    $dVal = $date ?? $from ?? Carbon::today()->toDateString();
                    $d = Carbon::parse($dVal);
                    $start = $d->startOfDay();
                    $end = $d->endOfDay();
                    $rangeLabel = $d->toDateString();
                }
            } catch (\Exception $e) {
                return back()->withErrors(['date' => 'Invalid date format.']);
            }

            // detect columns once
            $typeCol = $this->detectBankTransactionTypeColumn();
            $amtCol  = $this->detectBankTransactionAmountColumn();
            $dateCol = $this->detectBankTransactionDateColumn();

            $typeColQ = "`" . str_replace("`", "", $typeCol) . "`";
            $amtColQ  = "`" . str_replace("`", "", $amtCol) . "`";
            $dateColQ = "`" . str_replace("`", "", $dateCol) . "`";

            $currentBalance = $this->resolveBankAccountCurrentBalance((int) $accountId);

            $sumFromStartToNow = \DB::table('bank_transactions')
                ->where('bank_account_id', $accountId)
                ->where($dateCol, '>=', $start->toDateString())
                ->selectRaw("
                    COALESCE(SUM(CASE WHEN LOWER({$typeColQ}) = 'credit' THEN {$amtColQ} ELSE 0 END),0) as csum,
                    COALESCE(SUM(CASE WHEN LOWER({$typeColQ}) = 'debit' THEN {$amtColQ} ELSE 0 END),0) as dsum
                ")->first();

            $sumFromStartToNow = (float)($sumFromStartToNow->csum ?? 0) - (float)($sumFromStartToNow->dsum ?? 0);

            $openingBalance = $currentBalance - $sumFromStartToNow;

            $txns = \DB::table('bank_transactions')
                ->where('bank_account_id', $accountId)
                ->whereBetween($dateCol, [$start->toDateString(), $end->toDateString()])
                ->orderBy($dateCol)
                ->get();

            $running = $openingBalance;
            foreach ($txns as $t) {
                $tType = strtolower(trim((string)($t->{$typeCol} ?? '')));
                $tAmt  = (float) ($t->{$amtCol} ?? 0);

                $debit = 0.0;
                $credit = 0.0;
                if ($tType === 'debit') {
                    $debit = $tAmt;
                    $effect = -$debit;
                    $totalDebit += $debit;
                } else {
                    $credit = $tAmt;
                    $effect = $credit;
                    $totalCredit += $credit;
                }
                $running += $effect;

                $rows[] = [
                    'date' => (string)($t->{$dateCol} ?? ''),
                    'description' => $t->description ?? ($t->narration ?? ''),
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance' => $running,
                ];
            }

            $closingBalance = $running;
        }

        $grandTotal = $totalCredit - $totalDebit;

        return view('reports.bankTransactionReport', compact(
            'accounts','rows','totalDebit','totalCredit','grandTotal',
            'accountId','reportType','date','from','to','rangeLabel','openingBalance','closingBalance'
        ));
    }

    // CSV export for bank transactions
    public function bankTransactionExport(Request $request)
    {
        $accountId = $request->query('account_id');
        if (!$accountId) {
            return redirect()->route('reports.bankTransaction')->with('error','Please select an account to export.');
        }

        $reportType = $request->query('report_type', 'daily');
        $date = $request->query('date');
        $from = $request->query('from_date');
        $to = $request->query('to_date');

        try {
            if ($reportType === 'custom' && ($from && $to)) {
                $start = Carbon::parse($from)->startOfDay();
                $end = Carbon::parse($to)->endOfDay();
            } else {
                $dVal = $date ?? $from ?? Carbon::today()->toDateString();
                $d = Carbon::parse($dVal);
                $start = $d->startOfDay();
                $end = $d->endOfDay();
            }
        } catch (\Exception $e) {
            return redirect()->route('reports.bankTransaction')->with('error','Invalid date format.');
        }

        // detect columns
        $typeCol = $this->detectBankTransactionTypeColumn();
        $amtCol  = $this->detectBankTransactionAmountColumn();
        $dateCol = $this->detectBankTransactionDateColumn();

        $typeColQ = "`" . str_replace("`", "", $typeCol) . "`";
        $amtColQ = "`" . str_replace("`", "", $amtCol) . "`";
        $dateColQ = "`" . str_replace("`", "", $dateCol) . "`";

        $currentBalance = $this->resolveBankAccountCurrentBalance((int) $accountId);
        $sumFromStartToNow = \DB::table('bank_transactions')
            ->where('bank_account_id', $accountId)
            ->where($dateCol, '>=', $start->toDateString())
            ->selectRaw("
                COALESCE(SUM(CASE WHEN LOWER({$typeColQ}) = 'credit' THEN {$amtColQ} ELSE 0 END),0) as csum,
                COALESCE(SUM(CASE WHEN LOWER({$typeColQ}) = 'debit' THEN {$amtColQ} ELSE 0 END),0) as dsum
            ")->first();

        $sumFromStartToNow = (float)($sumFromStartToNow->csum ?? 0) - (float)($sumFromStartToNow->dsum ?? 0);
        $openingBalance = $currentBalance - $sumFromStartToNow;

        $txns = \DB::table('bank_transactions')
            ->where('bank_account_id', $accountId)
            ->whereBetween($dateCol, [$start->toDateString(), $end->toDateString()])
            ->orderBy($dateCol)
            ->get();

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function() use ($txns, $openingBalance, $typeCol, $amtCol, $dateCol) {
            $handle = fopen('php://output', 'w');
            // Opening balance
            fputcsv($handle, ['Opening Balance','','', ''.number_format($openingBalance,2,'.','')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Date','Description','Debit','Credit','Balance']);

            $running = $openingBalance;
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($txns as $t) {
                $tType = strtolower(trim((string)($t->{$typeCol} ?? '')));
                $tAmt  = (float) ($t->{$amtCol} ?? 0);

                $debit = 0.0; $credit = 0.0;
                if ($tType === 'debit') {
                    $debit = $tAmt;
                    $running -= $debit;
                    $totalDebit += $debit;
                } else {
                    $credit = $tAmt;
                    $running += $credit;
                    $totalCredit += $credit;
                }

                fputcsv($handle, [
                    $t->{$dateCol} ?? '',
                    $t->description ?? ($t->narration ?? ''),
                    $debit ? number_format($debit,2,'.','') : '',
                    $credit ? number_format($credit,2,'.','') : '',
                    number_format($running,2,'.','')
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Closing Balance','', ''.number_format($running,2,'.','')]);
            fputcsv($handle, []);
            fputcsv($handle, ['Totals','',''.number_format($totalDebit,2,'.',''),''.number_format($totalCredit,2,'.','')]);
            fclose($handle);
        });

        $account = \DB::table('bank_accounts')->where('id', $accountId)->first();
        $accountNamePart = $account?->account_name ? preg_replace('/[^A-Za-z0-9_\-]/','_',substr($account->account_name,0,30)) : $accountId;
        $fileName = 'bank-transactions-' . $accountNamePart . '-' . $start->toDateString() . '.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}\"");
        return $response;
    }

    // Bank report PDF
    public function bankTransactionPdf(Request $request)
    {
        $accountId = $request->query('account_id');
        if (!$accountId) {
            return redirect()->route('reports.bankTransaction')->with('error','Please select an account to download PDF.');
        }

        $reportType = $request->query('report_type', 'daily');
        $date = $request->query('date');
        $from = $request->query('from_date');
        $to = $request->query('to_date');

        try {
            if ($reportType === 'custom' && ($from && $to)) {
                $start = Carbon::parse($from)->startOfDay();
                $end = Carbon::parse($to)->endOfDay();
                $rangeLabel = $start->toDateString() . ' — ' . $end->toDateString();
            } else {
                $dVal = $date ?? $from ?? Carbon::today()->toDateString();
                $d = Carbon::parse($dVal);
                $start = $d->startOfDay();
                $end = $d->endOfDay();
                $rangeLabel = $d->toDateString();
            }
        } catch (\Exception $e) {
            return redirect()->route('reports.bankTransaction')->with('error','Invalid date format.');
        }

        $typeCol = $this->detectBankTransactionTypeColumn();
        $amtCol  = $this->detectBankTransactionAmountColumn();
        $dateCol = $this->detectBankTransactionDateColumn();

        $currentBalance = $this->resolveBankAccountCurrentBalance((int) $accountId);
        $sumFromStartToNow = \DB::table('bank_transactions')
            ->where('bank_account_id', $accountId)
            ->where($dateCol, '>=', $start->toDateString())
            ->selectRaw("COALESCE(SUM(CASE WHEN LOWER({$typeCol})='credit' THEN {$amtCol} ELSE 0 END),0) as csum, COALESCE(SUM(CASE WHEN LOWER({$typeCol})='debit' THEN {$amtCol} ELSE 0 END),0) as dsum")
            ->first();
        $sumFromStartToNow = (float)($sumFromStartToNow->csum ?? 0) - (float)($sumFromStartToNow->dsum ?? 0);
        $openingBalance = $currentBalance - $sumFromStartToNow;

        $txns = \DB::table('bank_transactions')
            ->where('bank_account_id', $accountId)
            ->whereBetween($dateCol, [$start->toDateString(), $end->toDateString()])
            ->orderBy($dateCol)
            ->get();

        // Build rows and totals to mirror the screen report
        $rows = [];
        $totalDebit = 0.0; $totalCredit = 0.0; $running = $openingBalance;
        foreach ($txns as $t) {
            $tType = strtolower(trim((string)($t->{$typeCol} ?? '')));
            $amt = (float) ($t->{$amtCol} ?? 0);
            $debit = $tType === 'debit' ? $amt : 0.0;
            $credit = $tType === 'debit' ? 0.0 : $amt;
            $totalDebit += $debit; $totalCredit += $credit; $running += ($credit - $debit);
            $rows[] = [
                'date' => (string)($t->{$dateCol} ?? ''),
                'description' => $t->description ?? ($t->narration ?? ''),
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $running,
            ];
        }
        $closingBalance = $running;
        $grandTotal = $totalCredit - $totalDebit;
        $isDaily = ($reportType === 'daily');
        $colCount = $isDaily ? 4 : 5;
        $colsBeforeBalance = $colCount - 1;

        $account = \DB::table('bank_accounts')->where('id', $accountId)->first();
        $accountName = $account?->account_name ?? ('Account '.$accountId);
        $bizId = request()->session()->get('business_id');
        $bizName = DB::table('businesses')->where('id', $bizId)->value('name') ?? config('app.name');
        $generatedAt = Carbon::now()->format('Y-m-d H:i');

        $useBanglaDigits = true;
        $html = view('reports.pdf-bank-transaction', compact(
            'account','accountName','rangeLabel','openingBalance','rows','closingBalance','totalDebit','totalCredit','grandTotal','isDaily','colCount','colsBeforeBalance','bizName','generatedAt','useBanglaDigits'
        ))->render();

        $fileName = 'bank-transactions-' . preg_replace('/[^A-Za-z0-9_\-]/','_', substr($accountName,0,40)) . '-' . $start->toDateString() . '.pdf';

        // Prefer Browsershot (Chromium/Chrome) for full Unicode/Indic shaping if enabled
        if (env('PDF_ENGINE') === 'browsershot' && class_exists('Spatie\\Browsershot\\Browsershot')) {
            try {
                $bs = \Spatie\Browsershot\Browsershot::html($html)
                    ->showBackground()
                    ->format('A4')
                    ->margins(10, 10, 10, 10)
                    ->timeout(120);
                if ($chromePath = env('CHROME_PATH')) {
                    $bs->setChromePath($chromePath);
                }
                $binary = $bs->pdf();
                return response($binary, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
                ]);
            } catch (\Throwable $e) {
                // fall back to DomPDF below
            }
        }

        // mPDF engine with OpenType layout support for Indic shaping
        if (env('PDF_ENGINE') === 'mpdf' && class_exists('Mpdf\\Mpdf')) {
            try {
                $mpdf = new \Mpdf\Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'margin_top' => 10,
                    'margin_right' => 10,
                    'margin_bottom' => 10,
                    'margin_left' => 10,
                ]);
                $mpdf->autoScriptToLang = true;
                $mpdf->autoLangToFont = true;
                if (property_exists($mpdf, 'useOTL')) { $mpdf->useOTL = 0xFF; }
                if (property_exists($mpdf, 'useKerning')) { $mpdf->useKerning = true; }
                if (property_exists($mpdf, 'useSubstitutions')) { $mpdf->useSubstitutions = 1; }
                if (method_exists($mpdf, 'SetDefaultFont')) { $mpdf->SetDefaultFont('notosansbengali'); }
                $fontDir = public_path('fonts');
                $reg = $fontDir.DIRECTORY_SEPARATOR.'NotoSansBengali-Regular.ttf';
                $bold = $fontDir.DIRECTORY_SEPARATOR.'NotoSansBengali-Bold.ttf';
                if (is_file($reg) && is_file($bold)) {
                    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
                    $fontDirs = $defaultConfig['fontDir'];
                    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
                    $fontData = $defaultFontConfig['fontdata'];
                    $mpdf = new \Mpdf\Mpdf([
                        'mode' => 'utf-8',
                        'format' => 'A4',
                        'margin_top' => 10,
                        'margin_right' => 10,
                        'margin_bottom' => 10,
                        'margin_left' => 10,
                        'fontDir' => array_merge($fontDirs, [$fontDir]),
                        'fontdata' => $fontData + [
                            'notosansbengali' => [
                                'R' => 'NotoSansBengali-Regular.ttf',
                                'B' => 'NotoSansBengali-Bold.ttf',
                            ],
                        ],
                        'default_font' => 'notosansbengali',
                    ]);
                    $mpdf->autoScriptToLang = true;
                    $mpdf->autoLangToFont = true;
                    if (property_exists($mpdf, 'useOTL')) { $mpdf->useOTL = 0xFF; }
                    if (property_exists($mpdf, 'useKerning')) { $mpdf->useKerning = true; }
                    if (property_exists($mpdf, 'useSubstitutions')) { $mpdf->useSubstitutions = 1; }
                    if (method_exists($mpdf, 'SetDefaultFont')) { $mpdf->SetDefaultFont('notosansbengali'); }
                }
                $mpdf->WriteHTML($html);
                return response($mpdf->Output($fileName, \Mpdf\Output\Destination::STRING_RETURN), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
                ]);
            } catch (\Throwable $e) {
                // fall back below
            }
        }

        if (class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4','portrait');
            return $pdf->download($fileName);
        }
        if (class_exists('Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'"'
            ]);
        }
        return redirect()->route('reports.bankTransaction')->with('error','PDF export not available. Please run: composer require barryvdh/laravel-dompdf');
    }

    // --- column detection helpers (if already present keep them; otherwise add)
    protected function detectBankTransactionTypeColumn(): string
    {
        $candidates = ['type','transaction_type','txn_type','payment_type','tran_type','txnType','trx_type'];
        foreach ($candidates as $c) {
            if (\Schema::hasColumn('bank_transactions', $c)) return $c;
        }
        // fallback (still safer to return a name that may not exist)
        return 'type';
    }

    protected function detectBankTransactionAmountColumn(): string
    {
        $candidates = ['amount','txn_amount','transaction_amount','credit_amount','debit_amount','amount_paid'];
        foreach ($candidates as $c) {
            if (\Schema::hasColumn('bank_transactions', $c)) return $c;
        }
        return 'amount';
    }

    // Add this helper to detect the date column in bank_transactions
    protected function detectBankTransactionDateColumn(): string
    {
        $candidates = ['date', 'txn_date', 'transaction_date', 'trans_date', 'entry_date'];
        foreach ($candidates as $c) {
            if (\Schema::hasColumn('bank_transactions', $c)) return $c;
        }
        // fallback
        return 'date';
    }

    /**
     * Safely resolve the current balance for a bank account.
     * Uses detected column names for bank_transactions to avoid SQL errors.
     */
    protected function resolveBankAccountCurrentBalance(int $accountId): float
    {
        $bal = \DB::table('bank_balances')
            ->where('bank_account_id', $accountId)
            ->where('bank_balances.business_id', request()->session()->get('business_id'))
            ->value('balance');
        return (float) ($bal ?? 0);
    }

    // Capital Account summary for current business
    public function capitalAccount()
    {
        $bizId = request()->session()->get('business_id');
        if (!$bizId) {
            return redirect()->route('business.index')->with('error','Please select or create a business first.');
        }

        $totalBank = \DB::table('bank_balances')
            ->where('business_id', $bizId)
            ->sum('balance');

        $totalClients = \DB::table('client_balances')
            ->where('business_id', $bizId)
            ->sum('balance');

        // Mobile banking totals: use today's entries
        $today = now()->toDateString();
        $totalMobileBalance = \DB::table('mobile_entries')
            ->join('mobile_accounts','mobile_entries.mobile_account_id','=','mobile_accounts.id')
            ->where('mobile_accounts.business_id', $bizId)
            ->where('mobile_entries.date', $today)
            ->sum('mobile_entries.balance');
        $totalMobileProfit = \DB::table('mobile_entries')
            ->join('mobile_accounts','mobile_entries.mobile_account_id','=','mobile_accounts.id')
            ->where('mobile_accounts.business_id', $bizId)
            ->where('mobile_entries.date', $today)
            ->sum('mobile_entries.profit');

        $capitalTotal = (float)$totalBank + (float)$totalClients + (float)$totalMobileBalance;

        $bankAccounts = \DB::table('bank_accounts')
            ->leftJoin('bank_balances', function($join){
                $join->on('bank_balances.bank_account_id','=','bank_accounts.id')
                     ->where('bank_balances.business_id', request()->session()->get('business_id'));
            })
            ->where('bank_accounts.business_id', $bizId)
            ->select('bank_accounts.account_name','bank_accounts.account_number','bank_balances.balance')
            ->orderBy('bank_accounts.account_name')
            ->get();

        $clients = \DB::table('client_creations')
            ->leftJoin('client_balances', function($join){
                $join->on('client_balances.client_id','=','client_creations.id')
                     ->where('client_balances.business_id', request()->session()->get('business_id'));
            })
            ->where('client_creations.business_id', $bizId)
            ->select('client_creations.client_name','client_creations.client_phone','client_balances.balance')
            ->orderBy('client_creations.client_name')
            ->get();

        return view('reports.capitalAccount', compact('totalBank','totalClients','capitalTotal','bankAccounts','clients','totalMobileBalance','totalMobileProfit'));
    }
}
