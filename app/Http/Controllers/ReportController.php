<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\clientCreation;
use App\Models\transaction;
use Carbon\Carbon;

class ReportController extends Controller
{
    // Client-wise transaction report (daily or custom date range)
    public function index(Request $request)
    {
        $clients = clientCreation::orderBy('client_name')->get();

        // Read inputs (GET)
        $clientId = $request->query('client_id');
        $reportType = $request->query('report_type', 'daily'); // 'daily' or 'custom'
        $date = $request->query('date'); // yyyy-mm-dd for daily
        $from = $request->query('from_date');
        $to = $request->query('to_date');

        // Default: no data until client selected
        $rows = [];
        $totalDebit = 0;
        $totalCredit = 0;
        $rangeLabel = '';

        if ($clientId) {
            // Determine start and end dates
            try {
                if ($reportType === 'custom' && $from && $to) {
                    $start = Carbon::parse($from)->startOfDay();
                    $end = Carbon::parse($to)->endOfDay();
                    $rangeLabel = $start->toDateString() . ' — ' . $end->toDateString();
                } else {
                    // daily fallback: use provided date or today
                    $d = $date ? Carbon::parse($date) : Carbon::today();
                    $start = $d->startOfDay();
                    $end = $d->endOfDay();
                    $rangeLabel = $d->toDateString();
                }
            } catch (\Exception $e) {
                // invalid date provided
                return back()->withErrors(['date' => 'Invalid date format. Use YYYY-MM-DD.']);
            }

            // Fetch transactions for this client in the date range
            $txns = transaction::where('transaction_client_name', $clientId)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->orderBy('date')
                ->get();

            // Build rows: each txn becomes a row with debit or credit value
            foreach ($txns as $t) {
                $debit = 0;
                $credit = 0;
                $type = strtolower(trim($t->type ?? $t->transaction_type ?? ''));
                if ($type === 'debit') {
                    $debit = (float) $t->amount;
                    $totalDebit += $debit;
                } else {
                    // treat everything not explicit 'debit' as credit
                    $credit = (float) $t->amount;
                    $totalCredit += $credit;
                }
                $rows[] = [
                    'date' => (string) ($t->date ?? $t->transaction_date ?? ''),
                    'description' => $t->description ?? ($t->transaction_source ?? ''),
                    'debit' => $debit,
                    'credit' => $credit,
                ];
            }
        }

        $grandTotal = $totalCredit - $totalDebit; // net result

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
}
