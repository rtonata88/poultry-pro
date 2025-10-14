<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quotation_number }}</title>
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
            border-bottom: 3px solid #4F46E5;
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
            color: #4F46E5;
        }
        .company-info p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }
        .quotation-title {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        .quotation-title h1 {
            color: #4F46E5;
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .quotation-title p {
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
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-section h3 {
            font-size: 14px;
            margin: 0 0 8px 0;
            color: #4F46E5;
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
            border-left: 4px solid #4F46E5;
            margin: 15px 0;
            clear: both;
        }
        .notes strong {
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
            color: #4F46E5;
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
            <div class="quotation-title">
                <h1>QUOTATION</h1>
                <p>{{ $quotation->quotation_number }}<span class="status-badge">{{ ucfirst($quotation->status) }}</span></p>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h3>Bill To:</h3>
        <p><strong>{{ $quotation->customer->name }}</strong></p>
        @if($quotation->customer->email)
            <p>{{ $quotation->customer->email }}</p>
        @endif
        @if($quotation->customer->phone)
            <p>{{ $quotation->customer->phone }}</p>
        @endif
        @if($quotation->customer->address)
            <p>{{ $quotation->customer->address }}</p>
        @endif
        @if($quotation->customer->tax_id)
            <p><strong>VAT Number: {{ $quotation->customer->tax_id }}</strong></p>
        @endif
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-col">
                <span class="label">Date:</span> {{ $quotation->date->format('M d, Y') }}
            </div>
            <div class="info-col">
                <span class="label">Valid Until:</span> {{ $quotation->valid_until->format('M d, Y') }}
            </div>
        </div>
        @if($quotation->reference)
            <div>
                <span class="label">Reference:</span> {{ $quotation->reference }}
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

    <div class="totals">
        <div class="totals-row">
            <div class="label">Subtotal:</div>
            <div class="value">{{ number_format($quotation->subtotal, 2) }}</div>
        </div>
        @if($quotation->vat > 0)
            <div class="totals-row">
                <div class="label">VAT:</div>
                <div class="value">{{ number_format($quotation->vat, 2) }}</div>
            </div>
        @endif
        @if($quotation->discount > 0)
            <div class="totals-row">
                <div class="label">Discount:</div>
                <div class="value">-{{ number_format($quotation->discount, 2) }}</div>
            </div>
        @endif
        <div class="totals-row total">
            <div class="label">Total:</div>
            <div class="value">{{ number_format($quotation->total, 2) }}</div>
        </div>
    </div>

    @if($quotation->notes)
        <div class="notes">
            <strong>Notes:</strong>
            <p style="margin: 0;">{{ $quotation->notes }}</p>
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
        <p style="margin-top: 5px;">This quotation is valid until {{ $quotation->valid_until->format('M d, Y') }}</p>
    </div>
</body>
</html>
