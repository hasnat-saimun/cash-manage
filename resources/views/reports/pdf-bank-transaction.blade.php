<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Bank Transactions</title>
  <!-- Bengali web font for proper glyph coverage in Chrome-based PDF engines -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;700&display=swap" rel="stylesheet">
  <style>
    /* Local TTF for mPDF (filesystem path), plus web font for Chrome */
    @font-face {
      font-family: 'NotoSansBengaliLocal';
      font-style: normal;
      font-weight: 400;
      src: url('{{ public_path('fonts/NotoSansBengali-Regular.ttf') }}') format('truetype');
    }
    @font-face {
      font-family: 'NotoSansBengaliLocal';
      font-style: normal;
      font-weight: 700;
      src: url('{{ public_path('fonts/NotoSansBengali-Bold.ttf') }}') format('truetype');
    }
    body { font-family: 'Noto Sans Bengali','NotoSansBengaliLocal', DejaVu Sans, sans-serif; font-size: 12px; }
    h3 { margin: 0 0 8px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 6px 8px; }
    th { background: #f1f1f1; }
    .text-right { text-align: right; }
    .muted { color: #666; }
    @page { size: A4 portrait; margin: 20mm 15mm; }
    .header { margin-bottom: 8px; }
    .header .row { display: flex; justify-content: space-between; align-items: baseline; }
    .footer { position: fixed; bottom: -10mm; left: 0; right: 0; text-align: center; font-size: 11px; color: #666; }
    .pagenum:before { content: counter(page) " / " counter(pages); }
  </style>
</head>
<body>
  <div class="header">
    <div class="row">
      <div><strong>{{ $bizName }}</strong></div>
      <div>Generated: {{ $generatedAt }}</div>
    </div>
    <h3>Bank Account Statement</h3>
    <div class="muted">Account: <strong>{{ $accountName }}</strong> | Range: <strong>{{ $rangeLabel }}</strong></div>
  </div>
  <table>
    <thead>
      <tr>
        @if(!$isDaily)
          <th style="width:100px;">Date</th>
        @endif
        <th>Description</th>
        <th class="text-right" style="width:90px;">Debit</th>
        <th class="text-right" style="width:90px;">Credit</th>
        <th class="text-right" style="width:110px;">Balance</th>
      </tr>
    </thead>
    <tbody>
      @if(isset($openingBalance))
        <tr>
          <td @if(!$isDaily) colspan="{{ $colsBeforeBalance }}" @else colspan="{{ $colsBeforeBalance }}" @endif><strong>Opening Balance</strong></td>
          <td class="text-right"><strong>{{ number_format($openingBalance,2) }}</strong></td>
        </tr>
      @endif
      @forelse($rows as $r)
        <tr>
          @unless($isDaily)
            <td>{{ $r['date'] }}</td>
          @endunless
          <td>{{ $r['description'] }}</td>
          <td class="text-right">{{ $r['debit'] ? number_format($r['debit'],2) : '-' }}</td>
          <td class="text-right">{{ $r['credit'] ? number_format($r['credit'],2) : '-' }}</td>
          <td class="text-right">{{ number_format($r['balance'],2) }}</td>
        </tr>
      @empty
        <tr><td colspan="{{ $colCount }}" class="muted">No transactions found.</td></tr>
      @endforelse
      @if(isset($closingBalance))
        <tr>
          <td @if(!$isDaily) colspan="{{ $colsBeforeBalance }}" @else colspan="{{ $colsBeforeBalance }}" @endif><strong>Closing Balance</strong></td>
          <td class="text-right"><strong>{{ number_format($closingBalance,2) }}</strong></td>
        </tr>
      @endif
    </tbody>
    <tfoot>
      @php $labelColspan = max(1, $colCount - 3); @endphp
      <tr>
        <th colspan="{{ $labelColspan }}" class="text-right">Totals</th>
        <th class="text-right">{{ number_format($totalDebit ?? 0,2) }}</th>
        <th class="text-right">{{ number_format($totalCredit ?? 0,2) }}</th>
        <th class="text-right"></th>
      </tr>
      <tr>
        <th colspan="{{ $labelColspan }}" class="text-right">Grand Total (Credit - Debit)</th>
        <th colspan="3" class="text-right">{{ number_format($grandTotal ?? 0,2) }}</th>
      </tr>
    </tfoot>
  </table>
  <div class="footer">Page <span class="pagenum"></span></div>
</body>
</html>
