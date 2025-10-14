<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>VAT Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        .report-period {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .summary-section {
            margin-bottom: 30px;
        }
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-card {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary-card h3 {
            font-size: 11px;
            color: #666;
            margin: 0 0 10px 0;
            font-weight: normal;
        }
        .summary-card .amount {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-card .subtext {
            font-size: 10px;
            color: #999;
        }
        .summary-card.output { border-left: 4px solid #10b981; }
        .summary-card.input { border-left: 4px solid #3b82f6; }
        .summary-card.net { border-left: 4px solid #ef4444; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
            font-size: 11px;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #333;
        }
        .text-right {
            text-align: right;
        }
        .text-green {
            color: #10b981;
        }
        .text-blue {
            color: #3b82f6;
        }
        .text-red {
            color: #ef4444;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'Company Name' }}</div>
        @if($company)
            <div style="font-size: 10px; color: #666;">
                @if($company->address) {{ $company->address }}<br> @endif
                @if($company->phone) Phone: {{ $company->phone }} @endif
                @if($company->email) | Email: {{ $company->email }} @endif
            </div>
        @endif
        <div class="report-title">VAT Report</div>
        <div class="report-period">Period: {{ date('d M Y', strtotime($startDate)) }} to {{ date('d M Y', strtotime($endDate)) }}</div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-card output">
                <h3>OUTPUT VAT (SALES)</h3>
                <div class="amount text-green">N$ {{ number_format($salesVat, 2) }}</div>
                <div class="subtext">Sales Total: N$ {{ number_format($salesTotal, 2) }}</div>
            </div>
            <div class="summary-card input">
                <h3>INPUT VAT (PURCHASES)</h3>
                <div class="amount text-blue">N$ {{ number_format($totalInputVat, 2) }}</div>
                <div class="subtext">Purchases: N$ {{ number_format($purchasesVat, 2) }} | Expenses: N$ {{ number_format($expensesVat, 2) }}</div>
            </div>
            <div class="summary-card net">
                <h3>NET VAT</h3>
                <div class="amount text-red">N$ {{ number_format(abs($netVat), 2) }}</div>
                <div class="subtext">{{ $netVat >= 0 ? 'VAT Payable' : 'VAT Reclaimable' }}</div>
            </div>
        </div>
    </div>

    <!-- Sales Transactions -->
    @if($salesTransactions->count() > 0)
    <div class="section-title">Sales Transactions (Output VAT)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice #</th>
                <th>Customer</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">VAT</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesTransactions as $transaction)
            <tr>
                <td>{{ $transaction->date->format('d/m/Y') }}</td>
                <td>{{ $transaction->invoice_number }}</td>
                <td>{{ $transaction->customer->name ?? 'N/A' }}</td>
                <td class="text-right">N$ {{ number_format($transaction->subtotal, 2) }}</td>
                <td class="text-right">N$ {{ number_format($transaction->vat, 2) }}</td>
                <td class="text-right">N$ {{ number_format($transaction->total, 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f9fafb;">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-right">N$ {{ number_format($salesSubtotal, 2) }}</td>
                <td class="text-right">N$ {{ number_format($salesVat, 2) }}</td>
                <td class="text-right">N$ {{ number_format($salesTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Purchase Transactions -->
    @if($purchaseTransactions->count() > 0)
    <div class="section-title">Purchase Transactions (Input VAT)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice #</th>
                <th>Supplier</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">VAT</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseTransactions as $transaction)
            <tr>
                <td>{{ $transaction->date->format('d/m/Y') }}</td>
                <td>{{ $transaction->invoice_number }}</td>
                <td>{{ $transaction->supplier->name ?? 'N/A' }}</td>
                <td class="text-right">N$ {{ number_format($transaction->subtotal, 2) }}</td>
                <td class="text-right">N$ {{ number_format($transaction->vat, 2) }}</td>
                <td class="text-right">N$ {{ number_format($transaction->total, 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f9fafb;">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-right">N$ {{ number_format($purchasesSubtotal, 2) }}</td>
                <td class="text-right">N$ {{ number_format($purchasesVat, 2) }}</td>
                <td class="text-right">N$ {{ number_format($purchasesTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Expense Transactions -->
    @if($expenseTransactions->count() > 0)
    <div class="section-title">Expense Transactions (Input VAT)</div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Supplier</th>
                <th class="text-right">Amount</th>
                <th class="text-right">VAT</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenseTransactions as $transaction)
            <tr>
                <td>{{ $transaction->date->format('d/m/Y') }}</td>
                <td>{{ $transaction->category->name ?? 'N/A' }}</td>
                <td>{{ $transaction->supplier->name ?? 'N/A' }}</td>
                <td class="text-right">N$ {{ number_format($transaction->amount, 2) }}</td>
                <td class="text-right">N$ {{ number_format($transaction->vat, 2) }}</td>
                <td class="text-right">N$ {{ number_format($transaction->total, 2) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f9fafb;">
                <td colspan="3" class="text-right">TOTAL:</td>
                <td class="text-right">N$ {{ number_format($expensesAmount, 2) }}</td>
                <td class="text-right">N$ {{ number_format($expensesVat, 2) }}</td>
                <td class="text-right">N$ {{ number_format($expensesTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        Generated on {{ date('d F Y, H:i:s') }} | {{ $company->name ?? 'Poultry Management System' }}
    </div>
</body>
</html>
