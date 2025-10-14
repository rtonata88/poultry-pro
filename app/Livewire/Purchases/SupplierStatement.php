<?php

namespace App\Livewire\Purchases;

use App\Models\Supplier;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierStatement extends Component
{
    public $supplierId;
    public $startDate;
    public $endDate;
    public $supplier;

    public function mount($supplierId)
    {
        $this->supplierId = $supplierId;
        $this->supplier = Supplier::with(['invoices', 'payments'])->findOrFail($supplierId);

        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function downloadPdf()
    {
        $supplier = Supplier::with([
            'invoices' => fn($q) => $q->whereBetween('date', [$this->startDate, $this->endDate])->orderBy('date'),
            'payments' => fn($q) => $q->whereBetween('date', [$this->startDate, $this->endDate])->orderBy('date')
        ])->findOrFail($this->supplierId);

        // Calculate opening balance (invoices and payments before start date)
        $openingInvoices = $supplier->invoices()
            ->where('date', '<', $this->startDate)
            ->sum('total');
        $openingPayments = $supplier->payments()
            ->where('date', '<', $this->startDate)
            ->sum('amount');
        $openingBalance = $openingInvoices - $openingPayments;

        // Get transactions within period
        $transactions = $this->getTransactions();

        $pdf = Pdf::loadView('pdf.supplier-statement', [
            'supplier' => $supplier,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'supplier-statement-' . $supplier->name . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function getTransactions()
    {
        $supplier = Supplier::with([
            'invoices' => fn($q) => $q->whereBetween('date', [$this->startDate, $this->endDate]),
            'payments' => fn($q) => $q->whereBetween('date', [$this->startDate, $this->endDate])
        ])->findOrFail($this->supplierId);

        // Calculate opening balance
        $openingInvoices = $supplier->invoices()
            ->where('date', '<', $this->startDate)
            ->sum('total');
        $openingPayments = $supplier->payments()
            ->where('date', '<', $this->startDate)
            ->sum('amount');
        $openingBalance = $openingInvoices - $openingPayments;

        $transactions = collect();

        // Add invoices
        foreach ($supplier->invoices as $invoice) {
            $transactions->push([
                'date' => $invoice->date,
                'type' => 'invoice',
                'reference' => $invoice->invoice_number,
                'description' => 'Invoice',
                'debit' => $invoice->total,
                'credit' => 0,
                'invoice' => $invoice,
            ]);
        }

        // Add payments
        foreach ($supplier->payments as $payment) {
            $transactions->push([
                'date' => $payment->date,
                'type' => 'payment',
                'reference' => $payment->payment_number,
                'description' => 'Payment' . ($payment->reference ? ' - ' . $payment->reference : ''),
                'debit' => 0,
                'credit' => $payment->amount,
                'payment' => $payment,
            ]);
        }

        // Sort by date
        $transactions = $transactions->sortBy('date')->values();

        // Calculate running balance
        $balance = $openingBalance;
        $transactions = $transactions->map(function ($transaction) use (&$balance) {
            $balance += $transaction['debit'] - $transaction['credit'];
            $transaction['balance'] = $balance;
            return $transaction;
        });

        return [
            'openingBalance' => $openingBalance,
            'transactions' => $transactions,
            'closingBalance' => $balance,
        ];
    }

    public function render()
    {
        $data = $this->getTransactions();

        return view('livewire.purchases.supplier-statement', [
            'openingBalance' => $data['openingBalance'],
            'transactions' => $data['transactions'],
            'closingBalance' => $data['closingBalance'],
        ]);
    }
}
