<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Supplier Statement - {{ $supplier->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .supplier-details {
            margin-bottom: 20px;
        }
        .supplier-details h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .supplier-details p {
            margin: 3px 0;
        }
        .period {
            text-align: center;
            margin: 20px 0;
            font-weight: bold;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background-color: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .bg-gray {
            background-color: #f9fafb;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-red {
            color: #dc2626;
        }
        .text-green {
            color: #16a34a;
        }
        .summary {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9fafb;
            border: 1px solid #ddd;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SUPPLIER STATEMENT</h1>
    </div>

    <div class="supplier-details">
        <h3>{{ $supplier->name }}</h3>
        @if($supplier->address)
            <p>{{ $supplier->address }}</p>
        @endif
        @if($supplier->phone)
            <p>Phone: {{ $supplier->phone }}</p>
        @endif
        @if($supplier->email)
            <p>Email: {{ $supplier->email }}</p>
        @endif
    </div>

    <div class="period">
        Statement Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Credit</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            <!-- Opening Balance -->
            <tr class="bg-gray">
                <td colspan="3" class="font-bold">Opening Balance</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right font-bold {{ $openingBalance > 0 ? 'text-red' : 'text-green' }}">
                    NAD {{ number_format(abs($openingBalance), 2) }}
                </td>
            </tr>

            <!-- Transactions -->
            @foreach($transactions['transactions'] as $transaction)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}</td>
                    <td>{{ $transaction['reference'] }}</td>
                    <td>{{ $transaction['description'] }}</td>
                    <td class="text-right {{ $transaction['debit'] > 0 ? 'text-red' : '' }}">
                        {{ $transaction['debit'] > 0 ? 'NAD ' . number_format($transaction['debit'], 2) : '-' }}
                    </td>
                    <td class="text-right {{ $transaction['credit'] > 0 ? 'text-green' : '' }}">
                        {{ $transaction['credit'] > 0 ? 'NAD ' . number_format($transaction['credit'], 2) : '-' }}
                    </td>
                    <td class="text-right {{ $transaction['balance'] > 0 ? 'text-red' : 'text-green' }}">
                        NAD {{ number_format(abs($transaction['balance']), 2) }}
                    </td>
                </tr>
            @endforeach

            <!-- Closing Balance -->
            <tr class="bg-gray">
                <td colspan="3" class="font-bold">Closing Balance</td>
                <td class="text-right font-bold">
                    NAD {{ number_format($transactions['transactions']->sum('debit'), 2) }}
                </td>
                <td class="text-right font-bold">
                    NAD {{ number_format($transactions['transactions']->sum('credit'), 2) }}
                </td>
                <td class="text-right font-bold {{ $transactions['closingBalance'] > 0 ? 'text-red' : 'text-green' }}" style="font-size: 14px;">
                    NAD {{ number_format(abs($transactions['closingBalance']), 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        @if($transactions['closingBalance'] > 0)
            <div class="summary-item">
                <strong>Amount Owed:</strong>
                <strong class="text-red">NAD {{ number_format($transactions['closingBalance'], 2) }}</strong>
            </div>
        @elseif($transactions['closingBalance'] < 0)
            <div class="summary-item">
                <strong>Overpayment:</strong>
                <strong class="text-green">NAD {{ number_format(abs($transactions['closingBalance']), 2) }}</strong>
            </div>
        @else
            <div class="summary-item">
                <strong>Account Status:</strong>
                <strong>Settled</strong>
            </div>
        @endif
    </div>

    <div style="margin-top: 40px; text-align: center; color: #666; font-size: 10px;">
        <p>Generated on {{ now()->format('M d, Y H:i') }}</p>
    </div>
</body>
</html>
