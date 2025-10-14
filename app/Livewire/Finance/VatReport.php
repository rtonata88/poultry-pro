<?php

namespace App\Livewire\Finance;

use App\Models\CompanyInformation;
use App\Models\CustomerInvoice;
use App\Models\Expense;
use App\Models\SupplierInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Component;

class VatReport extends Component
{
    public $startDate = '';
    public $endDate = '';
    public $period = 'month';

    public function mount()
    {
        // Default to current month
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedPeriod()
    {
        switch ($this->period) {
            case 'month':
                $this->startDate = now()->startOfMonth()->format('Y-m-d');
                $this->endDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'quarter':
                $this->startDate = now()->startOfQuarter()->format('Y-m-d');
                $this->endDate = now()->endOfQuarter()->format('Y-m-d');
                break;
            case 'year':
                $this->startDate = now()->startOfYear()->format('Y-m-d');
                $this->endDate = now()->endOfYear()->format('Y-m-d');
                break;
        }
    }

    public function generateReport()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        session()->flash('status', 'VAT report generated successfully.');
    }

    public function exportPdf()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $company = CompanyInformation::first();

        // Get all the data for the PDF
        $data = $this->getReportData();
        $data['company'] = $company;
        $data['startDate'] = $this->startDate;
        $data['endDate'] = $this->endDate;

        $pdf = Pdf::loadView('pdf.vat-report', $data);

        $filename = 'VAT_Report_' . date('Y-m-d', strtotime($this->startDate)) . '_to_' . date('Y-m-d', strtotime($this->endDate)) . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    private function getReportData()
    {
        // VAT on Sales (Output VAT)
        $salesVat = CustomerInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('vat');

        $salesSubtotal = CustomerInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('subtotal');

        $salesTotal = CustomerInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('total');

        // VAT on Purchases (Input VAT)
        $purchasesVat = SupplierInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('vat');

        $purchasesSubtotal = SupplierInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('subtotal');

        $purchasesTotal = SupplierInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('total');

        // VAT on Expenses (Input VAT)
        $expensesVat = Expense::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('vat');

        $expensesAmount = Expense::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('amount');

        $expensesTotal = Expense::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('total');

        // Total Input VAT
        $totalInputVat = $purchasesVat + $expensesVat;

        // Net VAT (positive = payable, negative = reclaimable)
        $netVat = $salesVat - $totalInputVat;

        // Detailed Sales Transactions
        $salesTransactions = CustomerInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->with('customer')
            ->orderBy('date', 'desc')
            ->get();

        // Detailed Purchase Transactions
        $purchaseTransactions = SupplierInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->with('supplier')
            ->orderBy('date', 'desc')
            ->get();

        // Detailed Expense Transactions
        $expenseTransactions = Expense::whereBetween('date', [$this->startDate, $this->endDate])
            ->with(['category', 'supplier'])
            ->orderBy('date', 'desc')
            ->get();

        return [
            'salesVat' => $salesVat,
            'salesSubtotal' => $salesSubtotal,
            'salesTotal' => $salesTotal,
            'purchasesVat' => $purchasesVat,
            'purchasesSubtotal' => $purchasesSubtotal,
            'purchasesTotal' => $purchasesTotal,
            'expensesVat' => $expensesVat,
            'expensesAmount' => $expensesAmount,
            'expensesTotal' => $expensesTotal,
            'totalInputVat' => $totalInputVat,
            'netVat' => $netVat,
            'salesTransactions' => $salesTransactions,
            'purchaseTransactions' => $purchaseTransactions,
            'expenseTransactions' => $expenseTransactions,
        ];
    }

    public function render()
    {
        $company = CompanyInformation::first();

        // VAT on Sales (Output VAT)
        $salesVat = CustomerInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('vat');

        $salesSubtotal = CustomerInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('subtotal');

        $salesTotal = CustomerInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('total');

        // VAT on Purchases (Input VAT)
        $purchasesVat = SupplierInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('vat');

        $purchasesSubtotal = SupplierInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('subtotal');

        $purchasesTotal = SupplierInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('total');

        // VAT on Expenses (Input VAT)
        $expensesVat = Expense::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('vat');

        $expensesAmount = Expense::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('amount');

        $expensesTotal = Expense::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('total');

        // Total Input VAT
        $totalInputVat = $purchasesVat + $expensesVat;

        // Net VAT (positive = payable, negative = reclaimable)
        $netVat = $salesVat - $totalInputVat;

        // Detailed Sales Transactions
        $salesTransactions = CustomerInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->with('customer')
            ->orderBy('date', 'desc')
            ->get();

        // Detailed Purchase Transactions
        $purchaseTransactions = SupplierInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->with('supplier')
            ->orderBy('date', 'desc')
            ->get();

        // Detailed Expense Transactions
        $expenseTransactions = Expense::whereBetween('date', [$this->startDate, $this->endDate])
            ->with(['category', 'supplier'])
            ->orderBy('date', 'desc')
            ->get();

        return view('livewire.finance.vat-report', [
            'company' => $company,
            'salesVat' => $salesVat,
            'salesSubtotal' => $salesSubtotal,
            'salesTotal' => $salesTotal,
            'purchasesVat' => $purchasesVat,
            'purchasesSubtotal' => $purchasesSubtotal,
            'purchasesTotal' => $purchasesTotal,
            'expensesVat' => $expensesVat,
            'expensesAmount' => $expensesAmount,
            'expensesTotal' => $expensesTotal,
            'totalInputVat' => $totalInputVat,
            'netVat' => $netVat,
            'salesTransactions' => $salesTransactions,
            'purchaseTransactions' => $purchaseTransactions,
            'expenseTransactions' => $expenseTransactions,
        ]);
    }
}
