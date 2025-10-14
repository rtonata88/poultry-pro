<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #10B981;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-top {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .company-info {
            display: table-cell;
            width: 60%;
        }
        .company-info h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #10B981;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }
        .invoice-title {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        .invoice-title h1 {
            color: #10B981;
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .invoice-title p {
            margin: 0;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
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
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h3 {
            font-size: 14px;
            margin: 0 0 8px 0;
            color: #10B981;
        }
        .info-section p {
            margin: 3px 0;
            font-size: 12px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .info-col {
            display: table-cell;
            width: 50%;
        }
        .label {
            font-weight: bold;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            background-color: #f3f4f6;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            font-size: 11px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 15px;
            float: right;
            width: 250px;
        }
        .totals-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .totals-row > div {
            display: table-cell;
        }
        .totals-row .label {
            text-align: left;
        }
        .totals-row .value {
            text-align: right;
        }
        .totals-row.total {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
            padding-top: 8px;
        }
        .totals-row.balance {
            color: #DC2626;
            font-weight: bold;
        }
        .footer {
            margin-top: 80px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #666;
            font-size: 11px;
        }
        .notes {
            background-color: #f9fafb;
            padding: 12px;
            border-left: 4px solid #10B981;
            margin: 15px 0;
            clear: both;
        }
        .notes strong {
            display: block;
            margin-bottom: 5px;
        }
        .payment-notice {
            background-color: #FEF3C7;
            border: 1px solid #FCD34D;
            padding: 12px;
            border-radius: 5px;
            margin: 15px 0;
            clear: both;
        }
        .payment-notice strong {
            display: block;
            margin-bottom: 5px;
        }
        .banking-details {
            background-color: #f9fafb;
            padding: 12px;
            border: 1px solid #e5e7eb;
            margin: 15px 0;
            clear: both;
        }
        .banking-details h4 {
            margin: 0 0 8px 0;
            font-size: 13px;
            color: #10B981;
        }
        .banking-details p {
            margin: 3px 0;
            font-size: 11px;
        }
        .banking-details .label {
            display: inline-block;
            width: 150px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-top">
            <div class="company-info">
                @php
                    $company = \App\Models\CompanyInformation::first();
                @endphp
                @if($company)
                    <h2>{{ $company->name }}</h2>
                    @if($company->address)
                        <p>{{ $company->address }}</p>
                    @endif
                    @if($company->city || $company->state || $company->zip_code)
                        <p>
                            @if($company->city){{ $company->city }}@endif
                            @if($company->state), {{ $company->state }}@endif
                            @if($company->zip_code) {{ $company->zip_code }}@endif
                        </p>
                    @endif
                    @if($company->country)
                        <p>{{ $company->country }}</p>
                    @endif
                    @if($company->phone)
                        <p>Phone: {{ $company->phone }}</p>
                    @endif
                    @if($company->email)
                        <p>Email: {{ $company->email }}</p>
                    @endif
                    @if($company->tax_number)
                        <p><strong>VAT Number: {{ $company->tax_number }}</strong></p>
                    @endif
                @endif
            </div>
            <div class="invoice-title">
                <h1>TAX INVOICE</h1>
                <p>{{ $invoice->invoice_number }}<span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></p>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h3>Bill To:</h3>
        <p><strong>{{ $invoice->customer->name }}</strong></p>
        @if($invoice->customer->email)
            <p>{{ $invoice->customer->email }}</p>
        @endif
        @if($invoice->customer->phone)
            <p>{{ $invoice->customer->phone }}</p>
        @endif
        @if($invoice->customer->address)
            <p>{{ $invoice->customer->address }}</p>
        @endif
        @if($invoice->customer->tax_id)
            <p><strong>VAT Number: {{ $invoice->customer->tax_id }}</strong></p>
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-col">
                <span class="label">Invoice Date:</span> {{ $invoice->date->format('M d, Y') }}
            </div>
            <div class="info-col">
                <span class="label">Due Date:</span> {{ $invoice->due_date->format('M d, Y') }}
            </div>
        </div>
        @if($invoice->reference)
            <div>
                <span class="label">Reference:</span> {{ $invoice->reference }}
            </div>
        @endif
        @if($invoice->customer_quotation_id)
            <div>
                <span class="label">Quotation:</span> {{ $invoice->quotation->quotation_number }}
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

    <div class="totals">
        <div class="totals-row">
            <div class="label">Subtotal:</div>
            <div class="value">{{ number_format($invoice->subtotal, 2) }}</div>
        </div>
        @if($invoice->vat > 0)
            <div class="totals-row">
                <div class="label">VAT:</div>
                <div class="value">{{ number_format($invoice->vat, 2) }}</div>
            </div>
        @endif
        @if($invoice->discount > 0)
            <div class="totals-row">
                <div class="label">Discount:</div>
                <div class="value">-{{ number_format($invoice->discount, 2) }}</div>
            </div>
        @endif
        <div class="totals-row total">
            <div class="label">Total:</div>
            <div class="value">{{ number_format($invoice->total, 2) }}</div>
        </div>
        @if($invoice->amount_paid > 0)
            <div class="totals-row">
                <div class="label">Amount Paid:</div>
                <div class="value">-{{ number_format($invoice->amount_paid, 2) }}</div>
            </div>
        @endif
        @if($invoice->balance > 0)
            <div class="totals-row balance">
                <div class="label">Balance Due:</div>
                <div class="value">{{ number_format($invoice->balance, 2) }}</div>
            </div>
        @endif
    </div>

    @if($invoice->status !== 'paid')
        <div class="payment-notice">
            <strong>Payment Due:</strong>
            <p style="margin: 0;">
                @if($invoice->status === 'overdue')
                    This invoice is overdue. Please arrange payment as soon as possible.
                @else
                    Payment of <strong>{{ number_format($invoice->balance, 2) }}</strong> is due by <strong>{{ $invoice->due_date->format('M d, Y') }}</strong>
                @endif
            </p>
        </div>
    @endif

    @if($invoice->notes)
        <div class="notes">
            <strong>Notes:</strong>
            <p style="margin: 0;">{{ $invoice->notes }}</p>
        </div>
    @endif

    @php
        $company = \App\Models\CompanyInformation::first();
    @endphp
    @if($company && ($company->bank_name || $company->bank_account_number))
        <div class="banking-details">
            <h4>Banking Details for Payment:</h4>
            @if($company->bank_name)
                <p><span class="label">Bank Name:</span> {{ $company->bank_name }}</p>
            @endif
            @if($company->bank_account_name)
                <p><span class="label">Account Name:</span> {{ $company->bank_account_name }}</p>
            @endif
            @if($company->bank_account_number)
                <p><span class="label">Account Number:</span> {{ $company->bank_account_number }}</p>
            @endif
            @if($company->bank_routing_number)
                <p><span class="label">Routing Number:</span> {{ $company->bank_routing_number }}</p>
            @endif
            @if($company->bank_swift_code)
                <p><span class="label">SWIFT Code:</span> {{ $company->bank_swift_code }}</p>
            @endif
            @if($company->bank_iban)
                <p><span class="label">IBAN:</span> {{ $company->bank_iban }}</p>
            @endif
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        @if($invoice->status === 'paid')
            <p style="margin-top: 5px; color: #10B981; font-weight: 600;">This invoice has been paid in full.</p>
        @endif
    </div>
</body>
</html>
