<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation {{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background-color: #f3f4f6;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .totals-row.total {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 10px;
            padding-top: 10px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 0.9em;
        }
        .notes {
            background-color: #f9fafb;
            padding: 15px;
            border-left: 4px solid #4F46E5;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>QUOTATION</h1>
        <p style="margin: 5px 0; font-size: 1.1em;">{{ $quotation->quotation_number }}</p>
    </div>

    <div class="info-section">
        <h3>Bill To:</h3>
        <p style="margin: 5px 0;"><strong>{{ $quotation->customer->name }}</strong></p>
        @if($quotation->customer->email)
            <p style="margin: 5px 0;">{{ $quotation->customer->email }}</p>
        @endif
        @if($quotation->customer->phone)
            <p style="margin: 5px 0;">{{ $quotation->customer->phone }}</p>
        @endif
        @if($quotation->customer->address)
            <p style="margin: 5px 0;">{{ $quotation->customer->address }}</p>
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <div>
                <span class="label">Date:</span> {{ $quotation->date->format('M d, Y') }}
            </div>
            <div>
                <span class="label">Valid Until:</span> {{ $quotation->valid_until->format('M d, Y') }}
            </div>
        </div>
        @if($quotation->reference)
            <div class="info-row">
                <div>
                    <span class="label">Reference:</span> {{ $quotation->reference }}
                </div>
            </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="clear: both;">
        <div class="totals">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>{{ number_format($quotation->subtotal, 2) }}</span>
            </div>
            @if($quotation->vat > 0)
                <div class="totals-row">
                    <span>VAT:</span>
                    <span>{{ number_format($quotation->vat, 2) }}</span>
                </div>
            @endif
            @if($quotation->discount > 0)
                <div class="totals-row">
                    <span>Discount:</span>
                    <span>-{{ number_format($quotation->discount, 2) }}</span>
                </div>
            @endif
            <div class="totals-row total">
                <span>Total:</span>
                <span>{{ number_format($quotation->total, 2) }}</span>
            </div>
        </div>
    </div>

    @if($quotation->notes)
        <div class="notes" style="clear: both; margin-top: 20px;">
            <strong>Notes:</strong>
            <p style="margin: 5px 0;">{{ $quotation->notes }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p style="font-size: 0.85em; margin-top: 10px;">This quotation is valid until {{ $quotation->valid_until->format('M d, Y') }}</p>
    </div>
</body>
</html>
