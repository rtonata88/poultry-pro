<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class CustomerStatement extends Component
{
    public $customerId;
    public $startDate;
    public $endDate;
    public $customer;

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = Customer::with(['invoices', 'payments'])->findOrFail($customerId);

        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function downloadPdf()
    {
        $customer = Customer::with([
            'invoices' => fn($q) => $q->whereBetween('date', [$this->startDate, $this->endDate])->orderBy('date'),
            'payments' => fn($q) => $q->whereBetween('date', [$this->startDate, $this->endDate])->orderBy('date')
        ])->findOrFail($this->customerId);

        // Calculate opening balance (invoices and payments before start date)
        $openingInvoices = $customer->invoices()
            ->where('date', '<', $this->startDate)
            ->sum('total');
        $openingPayments = $customer->payments()
            ->where('date', '<', $this->startDate)
            ->sum('amount');
        $openingBalance = $openingInvoices - $openingPayments;

        // Get transactions within period
        $transactions = $this->getTransactions();

        $pdf = Pdf::loadView('pdf.customer-statement', [
            'customer' => $customer,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'customer-statement-' . $customer->name . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function getTransactions()
    {
        $customer = Customer::with([
            'invoices' => fn($q) => $q->whereBetween('date', [$this->startDate, $this->endDate]),
            'payments' => fn($q) => $q->whereBetween('date', [$this->startDate, $this->endDate])
        ])->findOrFail($this->customerId);

        // Calculate opening balance
        $openingInvoices = $customer->invoices()
            ->where('date', '<', $this->startDate)
            ->sum('total');
        $openingPayments = $customer->payments()
            ->where('date', '<', $this->startDate)
            ->sum('amount');
        $openingBalance = $openingInvoices - $openingPayments;

        $transactions = collect();

        // Add invoices
        foreach ($customer->invoices as $invoice) {
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
        foreach ($customer->payments as $payment) {
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

        return view('livewire.sales.customer-statement', [
            'openingBalance' => $data['openingBalance'],
            'transactions' => $data['transactions'],
            'closingBalance' => $data['closingBalance'],
        ]);
    }
}
