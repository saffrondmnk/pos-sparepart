<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $transaction->transaction_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .total {
            font-size: 16px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($settings->logo_path && file_exists(public_path($settings->logo_path)))
        <div style="margin-bottom: 10px;">
            <img src="{{ public_path($settings->logo_path) }}" alt="Logo" style="max-height: 80px; max-width: 80px; height: 80px; width: 80px; object-fit: cover; border-radius: 50%;">
        </div>
        @endif
        <div class="company-name">{{ $settings->receipt_title }}</div>
        @if($settings->receipt_address)
        <div style="font-size: 10px; color: #666; margin-top: 3px;">{{ $settings->receipt_address }}</div>
        @endif
        @if($settings->receipt_phone)
        <div style="font-size: 10px; color: #666;">Tel: {{ $settings->receipt_phone }}</div>
        @endif
    </div>
    
    <div class="info">
        <p><strong>Transaction #:</strong> {{ $transaction->transaction_number }}</p>
        <p><strong>Date:</strong> {{ $transaction->created_at->format('M d, Y H:i:s') }}</p>
        <p><strong>Cashier:</strong> {{ $transaction->user->name }}</p>
        <p><strong>Payment Method:</strong> {{ ucfirst($transaction->payment_method) }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Price</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td class="text-right">{{ format_currency($item->unit_price) }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ format_currency($item->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="total text-right">
        <p>Total: {{ format_currency($transaction->total_amount) }}</p>
    </div>
    
    <div class="footer">
        <p>Thank you for your purchase!</p>
        <p>Please come again</p>
    </div>
</body>
</html>
