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

            $running = $openingBalance;
            foreach ($txns as $t) {
                $effect = 0.0;
                $type = strtolower(trim($t->type ?? ''));
                if ($type === 'debit') {
                    $effect = -((float)$t->amount);
                    $totalDebit += (float)$t->amount;
                } else {
                    $effect = (float)$t->amount;
                    $totalCredit += (float)$t->amount;
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
            'clientId','reportType','date','from','to','rangeLabel','openingBalance','closingBalance'
        ));
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
