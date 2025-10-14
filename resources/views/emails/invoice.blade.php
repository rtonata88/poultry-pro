<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
            border-bottom: 3px solid #10B981;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #10B981;
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
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            margin-left: 10px;
        }
        .status-unpaid {
            background-color: #FEF3C7;
            color: #92400E;
        }
        .status-partial {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        .status-paid {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .status-overdue {
            background-color: #FEE2E2;
            color: #991B1B;
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
        .totals-row.balance {
            color: #DC2626;
            font-weight: bold;
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
            border-left: 4px solid #10B981;
            margin: 20px 0;
        }
        .payment-notice {
            background-color: #FEF3C7;
            border: 1px solid #FCD34D;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <p style="margin: 5px 0; font-size: 1.1em;">
            {{ $invoice->invoice_number }}
            <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
        </p>
    </div>

    <div class="info-section">
        <h3>Bill To:</h3>
        <p style="margin: 5px 0;"><strong>{{ $invoice->customer->name }}</strong></p>
        @if($invoice->customer->email)
            <p style="margin: 5px 0;">{{ $invoice->customer->email }}</p>
        @endif
        @if($invoice->customer->phone)
            <p style="margin: 5px 0;">{{ $invoice->customer->phone }}</p>
        @endif
        @if($invoice->customer->address)
            <p style="margin: 5px 0;">{{ $invoice->customer->address }}</p>
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <div>
                <span class="label">Invoice Date:</span> {{ $invoice->date->format('M d, Y') }}
            </div>
            <div>
                <span class="label">Due Date:</span> {{ $invoice->due_date->format('M d, Y') }}
            </div>
        </div>
        @if($invoice->reference)
            <div class="info-row">
                <div>
                    <span class="label">Reference:</span> {{ $invoice->reference }}
                </div>
            </div>
        @endif
        @if($invoice->customer_quotation_id)
            <div class="info-row">
                <div>
                    <span class="label">Quotation:</span> {{ $invoice->quotation->quotation_number }}
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
            @foreach($invoice->items as $item)
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
                <span>{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->vat > 0)
                <div class="totals-row">
                    <span>VAT:</span>
                    <span>{{ number_format($invoice->vat, 2) }}</span>
                </div>
            @endif
            @if($invoice->discount > 0)
                <div class="totals-row">
                    <span>Discount:</span>
                    <span>-{{ number_format($invoice->discount, 2) }}</span>
                </div>
            @endif
            <div class="totals-row total">
                <span>Total:</span>
                <span>{{ number_format($invoice->total, 2) }}</span>
            </div>
            @if($invoice->amount_paid > 0)
                <div class="totals-row">
                    <span>Amount Paid:</span>
                    <span>-{{ number_format($invoice->amount_paid, 2) }}</span>
                </div>
            @endif
            @if($invoice->balance > 0)
                <div class="totals-row balance">
                    <span>Balance Due:</span>
                    <span>{{ number_format($invoice->balance, 2) }}</span>
                </div>
            @endif
        </div>
    </div>

    @if($invoice->status !== 'paid')
        <div class="payment-notice" style="clear: both; margin-top: 20px;">
            <strong>Payment Due:</strong>
            <p style="margin: 5px 0;">
                @if($invoice->status === 'overdue')
                    This invoice is overdue. Please arrange payment as soon as possible.
                @else
                    Payment of <strong>{{ number_format($invoice->balance, 2) }}</strong> is due by <strong>{{ $invoice->due_date->format('M d, Y') }}</strong>
                @endif
            </p>
        </div>
    @endif

    @if($invoice->notes)
        <div class="notes" style="clear: both; margin-top: 20px;">
            <strong>Notes:</strong>
            <p style="margin: 5px 0;">{{ $invoice->notes }}</p>
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        @if($invoice->status === 'paid')
            <p style="font-size: 0.85em; margin-top: 10px; color: #10B981; font-weight: 600;">This invoice has been paid in full.</p>
        @endif
    </div>
</body>
</html>
