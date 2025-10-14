<?php

namespace App\Livewire\Dashboard;

use App\Models\CustomerInvoice;
use App\Models\CustomerPayment;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use Livewire\Component;

class Finance extends Component
{
    public function render()
    {
        // Revenue this month (paid and partial invoices)
        $currentMonthRevenue = CustomerInvoice::whereIn('status', ['paid', 'partial'])
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount_paid');

        // Previous month revenue for comparison
        $previousMonthRevenue = CustomerInvoice::whereIn('status', ['paid', 'partial'])
            ->whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->sum('amount_paid');

        $revenueChange = $previousMonthRevenue > 0
            ? (($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100
            : 0;

        // Expenses this month (supplier invoices)
        $currentMonthExpenses = SupplierInvoice::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total');

        $previousMonthExpenses = SupplierInvoice::whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->sum('total');

        $expensesChange = $previousMonthExpenses > 0
            ? (($currentMonthExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100
            : 0;

        // Outstanding receivables
        $outstandingReceivables = CustomerInvoice::whereIn('status', ['unpaid', 'partial'])
            ->sum('balance');

        $overdueReceivables = CustomerInvoice::where('status', 'overdue')
            ->count();

        // Outstanding payables
        $outstandingPayables = SupplierInvoice::whereIn('status', ['unpaid', 'partial'])
            ->sum('balance');

        $overduePayables = SupplierInvoice::where('status', 'overdue')
            ->count();

        // Recent transactions (last 10)
        $recentCustomerPayments = CustomerPayment::with('invoice.customer')
            ->latest()
            ->take(5)
            ->get();

        $recentSupplierPayments = SupplierPayment::with('invoice.supplier')
            ->latest()
            ->take(5)
            ->get();

        // Invoice status distribution
        $invoiceStats = [
            'paid' => CustomerInvoice::where('status', 'paid')->count(),
            'partial' => CustomerInvoice::where('status', 'partial')->count(),
            'unpaid' => CustomerInvoice::where('status', 'unpaid')->count(),
            'overdue' => CustomerInvoice::where('status', 'overdue')->count(),
        ];

        return view('livewire.dashboard.finance', [
            'currentMonthRevenue' => $currentMonthRevenue,
            'revenueChange' => $revenueChange,
            'currentMonthExpenses' => $currentMonthExpenses,
            'expensesChange' => $expensesChange,
            'outstandingReceivables' => $outstandingReceivables,
            'overdueReceivables' => $overdueReceivables,
            'outstandingPayables' => $outstandingPayables,
            'overduePayables' => $overduePayables,
            'recentCustomerPayments' => $recentCustomerPayments,
            'recentSupplierPayments' => $recentSupplierPayments,
            'invoiceStats' => $invoiceStats,
        ])->layout('components.layouts.app', ['title' => __('Finance Dashboard')]);
    }
}
