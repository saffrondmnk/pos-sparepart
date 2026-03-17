<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            margin-bottom: 10px;
        }
        .date-range {
            color: #666;
            margin-bottom: 20px;
        }
        .summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            font-size: 10px;
            color: #666;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($settings->logo_path && file_exists(public_path($settings->logo_path)))
        <div style="margin-bottom: 10px;">
            <img src="{{ public_path($settings->logo_path) }}" alt="Logo" style="max-height: 50px; max-width: 100px;">
        </div>
        @endif
        <div class="company-name">{{ $settings->company_name }}</div>
        <div class="report-title">Sales Report</div>
        <div class="date-range">
            @if($dateFrom && $dateTo)
                Period: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
            @elseif($dateFrom)
                From: {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}
            @elseif($dateTo)
                Until: {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
            @else
                All Time
            @endif
            <br>
            Generated: {{ now()->format('M d, Y H:i:s') }}
        </div>
    </div>
    
    <div class="summary">
        <div class="summary-item">
            <div class="summary-label">Total Transactions</div>
            <div class="summary-value">{{ $transactions->count() }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Revenue</div>
            <div class="summary-value">{{ format_currency($totalSales) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Average Sale</div>
            <div class="summary-value">{{ format_currency($transactions->count() > 0 ? $totalSales / $transactions->count() : 0) }}</div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Transaction #</th>
                <th>Cashier</th>
                <th>Payment</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $index => $transaction)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                <td>{{ $transaction->transaction_number }}</td>
                <td>{{ $transaction->user->name }}</td>
                <td class="text-center">{{ ucfirst($transaction->payment_method) }}</td>
                <td class="text-right">{{ format_currency($transaction->total_amount) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">Total:</td>
                <td class="text-right">{{ format_currency($totalSales) }}</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>{{ $settings->company_name }} - Sales Report</p>
        @if($settings->receipt_phone)
        <p>Tel: {{ $settings->receipt_phone }}</p>
        @endif
        <p>Thank you for your business!</p>
    </div>
</body>
</html>
