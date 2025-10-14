<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Key Metrics Cards -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <!-- Revenue Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Revenue (This Month)') }}</span>
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($currentMonthRevenue, 2) }}
                </div>
                <div class="mt-2 flex items-center text-sm">
                    @if($revenueChange > 0)
                        <span class="text-green-600 dark:text-green-400">↑ {{ number_format(abs($revenueChange), 1) }}%</span>
                    @elseif($revenueChange < 0)
                        <span class="text-red-600 dark:text-red-400">↓ {{ number_format(abs($revenueChange), 1) }}%</span>
                    @else
                        <span class="text-zinc-500">{{ __('No change') }}</span>
                    @endif
                    <span class="ml-2 text-zinc-500">{{ __('from last month') }}</span>
                </div>
            </div>

            <!-- Expenses Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Expenses (This Month)') }}</span>
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($currentMonthExpenses, 2) }}
                </div>
                <div class="mt-2 flex items-center text-sm">
                    @if($expensesChange > 0)
                        <span class="text-red-600 dark:text-red-400">↑ {{ number_format(abs($expensesChange), 1) }}%</span>
                    @elseif($expensesChange < 0)
                        <span class="text-green-600 dark:text-green-400">↓ {{ number_format(abs($expensesChange), 1) }}%</span>
                    @else
                        <span class="text-zinc-500">{{ __('No change') }}</span>
                    @endif
                    <span class="ml-2 text-zinc-500">{{ __('from last month') }}</span>
                </div>
            </div>

            <!-- Outstanding Receivables Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Outstanding Receivables') }}</span>
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($outstandingReceivables, 2) }}
                </div>
                @if($overdueReceivables > 0)
                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                        {{ $overdueReceivables }} {{ __('overdue invoice(s)') }}
                    </div>
                @else
                    <div class="mt-2 text-sm text-zinc-500">
                        {{ __('No overdue invoices') }}
                    </div>
                @endif
            </div>

            <!-- Outstanding Payables Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Outstanding Payables') }}</span>
                    <svg class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($outstandingPayables, 2) }}
                </div>
                @if($overduePayables > 0)
                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                        {{ $overduePayables }} {{ __('overdue invoice(s)') }}
                    </div>
                @else
                    <div class="mt-2 text-sm text-zinc-500">
                        {{ __('No overdue invoices') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="grid gap-4 md:grid-cols-2">
            <!-- Invoice Status Distribution -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ __('Invoice Status') }}</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Paid') }}</span>
                        </div>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $invoiceStats['paid'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Partial') }}</span>
                        </div>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $invoiceStats['partial'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Unpaid') }}</span>
                        </div>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $invoiceStats['unpaid'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Overdue') }}</span>
                        </div>
                        <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $invoiceStats['overdue'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ __('Recent Payments') }}</h3>
                <div class="space-y-3">
                    @forelse($recentCustomerPayments->concat($recentSupplierPayments)->sortByDesc('created_at')->take(5) as $payment)
                        <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-700 last:border-0">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    @if($payment instanceof \App\Models\CustomerPayment)
                                        {{ $payment->invoice->customer->name ?? 'N/A' }}
                                    @else
                                        {{ $payment->invoice->supplier->name ?? 'N/A' }}
                                    @endif
                                </p>
                                <p class="text-xs text-zinc-500">
                                    {{ $payment->date->format('M d, Y') }} •
                                    @if($payment instanceof \App\Models\CustomerPayment)
                                        <span class="text-green-600">{{ __('Received') }}</span>
                                    @else
                                        <span class="text-red-600">{{ __('Paid') }}</span>
                                    @endif
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ number_format($payment->amount, 2) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 text-center py-4">{{ __('No recent transactions') }}</p>
                    @endforelse
                </div>
            </div>
        </div>
</div>
