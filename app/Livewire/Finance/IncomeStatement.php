<?php

namespace App\Livewire\Finance;

use App\Models\CompanyInformation;
use App\Models\CustomerInvoice;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\SupplierInvoice;
use Livewire\Component;

class IncomeStatement extends Component
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

        session()->flash('status', 'Income statement generated successfully.');
    }

    public function exportPdf()
    {
        // This would be implemented with PDF generation
        session()->flash('status', 'Income statement exported to PDF.');
    }

    public function render()
    {
        $company = CompanyInformation::first();

        // Revenue (Sales)
        $totalRevenue = CustomerInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('total');

        $revenueDetails = CustomerInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->with('customer')
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('customer.name');

        // Cost of Goods Sold / Direct Costs (Supplier Invoices)
        $totalCogs = SupplierInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('total');

        $cogsDetails = SupplierInvoice::whereBetween('date', [$this->startDate, $this->endDate])
            ->with('supplier')
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('supplier.name');

        // Gross Profit
        $grossProfit = $totalRevenue - $totalCogs;
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        // Operating Expenses (Categorized)
        $expensesByCategory = Expense::whereBetween('date', [$this->startDate, $this->endDate])
            ->with('category')
            ->get()
            ->groupBy(function ($expense) {
                return $expense->category?->name ?? 'Uncategorized';
            })
            ->map(function ($expenses) {
                return [
                    'total' => $expenses->sum('total'),
                    'count' => $expenses->count(),
                    'expenses' => $expenses,
                ];
            });

        $totalOperatingExpenses = Expense::whereBetween('date', [$this->startDate, $this->endDate])
            ->sum('total');

        // Net Profit
        $netProfit = $grossProfit - $totalOperatingExpenses;
        $netProfitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        return view('livewire.finance.income-statement', [
            'company' => $company,
            'totalRevenue' => $totalRevenue,
            'revenueDetails' => $revenueDetails,
            'totalCogs' => $totalCogs,
            'cogsDetails' => $cogsDetails,
            'grossProfit' => $grossProfit,
            'grossProfitMargin' => $grossProfitMargin,
            'expensesByCategory' => $expensesByCategory,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'netProfit' => $netProfit,
            'netProfitMargin' => $netProfitMargin,
        ]);
    }
}
