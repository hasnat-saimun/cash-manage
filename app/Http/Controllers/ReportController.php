<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\clientCreation;
use App\Models\transaction;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        // validation: custom must have both dates
        if ($reportType === 'custom' && (empty($from) || empty($to))) {
            // allow showing page but return validation message if client was selected
            if ($clientId) {
                return redirect()->route('reports.clientTransaction')
                    ->withInput()
                    ->withErrors(['date' => 'Please provide both From and To dates for custom date range.']);
            }
        }

        $rows = [];
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $rangeLabel = '';

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
                return redirect()->route('reports.clientTransaction')->withErrors(['date' => 'Invalid date format. Use YYYY-MM-DD.']);
            }

            $txns = transaction::where('transaction_client_name', $clientId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('date')
                ->get();

            foreach ($txns as $t) {
                $debit = 0.0;
                $credit = 0.0;
                $type = strtolower(trim($t->type ?? $t->transaction_type ?? ''));
                if ($type === 'debit') {
                    $debit = (float) $t->amount;
                    $totalDebit += $debit;
                } else {
                    $credit = (float) $t->amount;
                    $totalCredit += $credit;
                }

                // Resolve source to a display name:
                $sourceVal = $t->transaction_source ?? $t->source ?? null;
                $sourceName = '';
                if ($sourceVal) {
                    if (is_numeric($sourceVal)) {
                        $s = \App\Models\source::find($sourceVal);
                        $sourceName = $s ? ($s->source_name ?? (string)$sourceVal) : (string)$sourceVal;
                    } else {
                        $sourceName = (string) $sourceVal;
                    }
                }

                $rows[] = [
                    'date' => (string) ($t->date ?? $t->transaction_date ?? ''),
                    'description' => $t->description ?? '',
                    'source' => $sourceName,
                    'debit' => $debit,
                    'credit' => $credit,
                ];
            }
        }

        $grandTotal = $totalCredit - $totalDebit;

        return view('reports.clientTransaction', compact(
            'clients',
            'rows',
            'totalDebit',
            'totalCredit',
            'grandTotal',
            'clientId',
            'reportType',
            'date',
            'from',
            'to',
            'rangeLabel'
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

        // validation for custom export
        if ($reportType === 'custom' && (empty($from) || empty($to))) {
            return redirect()->route('reports.clientTransaction')
                ->withInput()
                ->withErrors(['date' => 'Please provide both From and To dates for custom date range before exporting.']);
        }

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

        $txns = transaction::where('transaction_client_name', $clientId)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        $client = clientCreation::find($clientId);
        $clientNamePart = $client?->client_name ? preg_replace('/[^A-Za-z0-9_\-]/','_',substr($client->client_name,0,30)) : $clientId;
        $fileName = 'client-transactions-' . $clientNamePart . '-' . $start->toDateString() . '.csv';

        $response = new StreamedResponse(function() use ($txns) {
            $handle = fopen('php://output', 'w');
            // header row — include Source
            fputcsv($handle, ['Date','Description','Source','Debit','Credit']);

            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($txns as $t) {
                $type = strtolower(trim($t->type ?? $t->transaction_type ?? ''));
                $debit = 0.0;
                $credit = 0.0;
                if ($type === 'debit') {
                    $debit = (float)$t->amount;
                    $totalDebit += $debit;
                } else {
                    $credit = (float)$t->amount;
                    $totalCredit += $credit;
                }

                // Resolve source to display name for CSV
                $sourceVal = $t->transaction_source ?? $t->source ?? null;
                $sourceName = '';
                if ($sourceVal) {
                    if (is_numeric($sourceVal)) {
                        $s = \App\Models\source::find($sourceVal);
                        $sourceName = $s ? ($s->source_name ?? (string)$sourceVal) : (string)$sourceVal;
                    } else {
                        $sourceName = (string) $sourceVal;
                    }
                }

                fputcsv($handle, [
                    $t->date ?? $t->transaction_date ?? '',
                    $t->description ?? '',
                    $sourceName,
                    $debit ? number_format($debit,2,'.','') : '',
                    $credit ? number_format($credit,2,'.','') : ''
                ]);
            }

            // blank row then totals (adjusted for new column)
            fputcsv($handle, []);
            fputcsv($handle, ['Totals','','', ''.number_format($totalDebit,2,'.',''), ''.number_format($totalCredit,2,'.','')]);
            fputcsv($handle, ['Grand Total (Credit - Debit)', '', '', '', ''.number_format(($totalCredit - $totalDebit),2,'.','')]);

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}\"");
        return $response;
    }
}
