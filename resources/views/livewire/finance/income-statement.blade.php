<section class="w-full">
    @if (session('status'))
        <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
            <flux:text class="text-green-700 dark:text-green-400 font-medium">
                {{ session('status') }}
            </flux:text>
        </div>
    @endif

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Income Statement') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Profit and loss statement for your business') }}</p>
            </div>
        </div>

        <!-- Date Range Selection -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
            <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ __('Report Period') }}</h4>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <flux:select wire:model.live="period" :label="__('Quick Period')">
                    <option value="month">{{ __('Current Month') }}</option>
                    <option value="quarter">{{ __('Current Quarter') }}</option>
                    <option value="year">{{ __('Current Year') }}</option>
                    <option value="custom">{{ __('Custom Range') }}</option>
                </flux:select>

                <flux:input wire:model="startDate" :label="__('Start Date')" type="date" required />
                <flux:input wire:model="endDate" :label="__('End Date')" type="date" required />

                <div class="flex items-end">
                    <flux:button wire:click="generateReport" variant="primary" class="w-full">
                        {{ __('Generate Report') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <!-- Financial Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Revenue -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Total Revenue') }}</h4>
                </div>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($totalRevenue, 2) }}</p>
            </div>

            <!-- COGS -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Cost of Goods') }}</h4>
                </div>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($totalCogs, 2) }}</p>
            </div>

            <!-- Gross Profit -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Gross Profit') }}</h4>
                </div>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($grossProfit, 2) }}</p>
                <p class="text-xs text-zinc-500 mt-1">{{ number_format($grossProfitMargin, 1) }}% {{ __('margin') }}</p>
            </div>

            <!-- Net Profit -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Net Profit') }}</h4>
                </div>
                <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($netProfit, 2) }}</p>
                <p class="text-xs text-zinc-500 mt-1">{{ number_format($netProfitMargin, 1) }}% {{ __('margin') }}</p>
            </div>
        </div>

        <!-- Detailed Income Statement -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Detailed Statement') }}</h4>
                <flux:button wire:click="exportPdf" variant="ghost" size="sm" icon="arrow-down-tray">
                    {{ __('Export PDF') }}
                </flux:button>
            </div>

            <div class="space-y-8">
                <!-- Revenue Section -->
                <div>
                    <div class="flex items-center justify-between py-3 border-b-2 border-zinc-900 dark:border-zinc-100">
                        <h5 class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ __('REVENUE') }}</h5>
                        <span class="text-base font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totalRevenue, 2) }}</span>
                    </div>

                    @if ($revenueDetails->count() > 0)
                        <div class="mt-3 ml-4 space-y-2">
                            @foreach ($revenueDetails as $customerName => $invoices)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $customerName }} ({{ $invoices->count() }} {{ __('invoices') }})</span>
                                    <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ number_format($invoices->sum('total'), 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Cost of Goods Sold Section -->
                <div>
                    <div class="flex items-center justify-between py-3 border-b border-zinc-300 dark:border-zinc-600">
                        <h5 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ __('COST OF GOODS SOLD') }}</h5>
                        <span class="text-base font-semibold text-red-600 dark:text-red-400">({{ number_format($totalCogs, 2) }})</span>
                    </div>

                    @if ($cogsDetails->count() > 0)
                        <div class="mt-3 ml-4 space-y-2">
                            @foreach ($cogsDetails as $supplierName => $invoices)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $supplierName }} ({{ $invoices->count() }} {{ __('invoices') }})</span>
                                    <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ number_format($invoices->sum('total'), 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Gross Profit -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                        <h5 class="text-base font-bold text-blue-900 dark:text-blue-100">{{ __('GROSS PROFIT') }}</h5>
                        <div class="text-right">
                            <span class="text-base font-bold text-blue-900 dark:text-blue-100">{{ number_format($grossProfit, 2) }}</span>
                            <p class="text-xs text-blue-700 dark:text-blue-300 mt-1">{{ number_format($grossProfitMargin, 1) }}% {{ __('margin') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Operating Expenses Section -->
                <div>
                    <div class="flex items-center justify-between py-3 border-b border-zinc-300 dark:border-zinc-600">
                        <h5 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ __('OPERATING EXPENSES') }}</h5>
                        <span class="text-base font-semibold text-red-600 dark:text-red-400">({{ number_format($totalOperatingExpenses, 2) }})</span>
                    </div>

                    @if ($expensesByCategory->count() > 0)
                        <div class="mt-3 ml-4 space-y-2">
                            @foreach ($expensesByCategory as $categoryName => $categoryData)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ $categoryName }} ({{ $categoryData['count'] }} {{ __('expenses') }})</span>
                                    <span class="text-zinc-900 dark:text-zinc-100 font-medium">{{ number_format($categoryData['total'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-3 ml-4">
                            <p class="text-sm text-zinc-500">{{ __('No operating expenses in this period') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Net Profit -->
                <div class="bg-{{ $netProfit >= 0 ? 'green' : 'red' }}-50 dark:bg-{{ $netProfit >= 0 ? 'green' : 'red' }}-900/20 p-4 rounded-lg border-2 border-{{ $netProfit >= 0 ? 'green' : 'red' }}-200 dark:border-{{ $netProfit >= 0 ? 'green' : 'red' }}-800">
                    <div class="flex items-center justify-between">
                        <h5 class="text-lg font-bold text-{{ $netProfit >= 0 ? 'green' : 'red' }}-900 dark:text-{{ $netProfit >= 0 ? 'green' : 'red' }}-100">{{ __('NET PROFIT') }}</h5>
                        <div class="text-right">
                            <span class="text-lg font-bold text-{{ $netProfit >= 0 ? 'green' : 'red' }}-900 dark:text-{{ $netProfit >= 0 ? 'green' : 'red' }}-100">{{ number_format($netProfit, 2) }}</span>
                            <p class="text-xs text-{{ $netProfit >= 0 ? 'green' : 'red' }}-700 dark:text-{{ $netProfit >= 0 ? 'green' : 'red' }}-300 mt-1">{{ number_format($netProfitMargin, 1) }}% {{ __('margin') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Financial Metrics -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                        <p class="text-xs text-zinc-600 dark:text-zinc-400 mb-1">{{ __('Revenue') }}</p>
                        <p class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totalRevenue, 2) }}</p>
                    </div>
                    <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                        <p class="text-xs text-zinc-600 dark:text-zinc-400 mb-1">{{ __('Total Expenses') }}</p>
                        <p class="text-lg font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($totalCogs + $totalOperatingExpenses, 2) }}</p>
                    </div>
                    <div class="text-center p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                        <p class="text-xs text-zinc-600 dark:text-zinc-400 mb-1">{{ __('Profit Margin') }}</p>
                        <p class="text-lg font-bold {{ $netProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ number_format($netProfitMargin, 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
