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
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('VAT Report') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('View VAT summary for sales and purchases') }}</p>
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

        <!-- VAT Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Output VAT (Sales) -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Output VAT (Sales)') }}</h4>
                    <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($salesVat, 2) }}</p>
                <p class="text-xs text-zinc-500 mt-2">{{ __('Sales Total') }}: {{ number_format($salesTotal, 2) }}</p>
            </div>

            <!-- Input VAT (Purchases + Expenses) -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Input VAT (Purchases)') }}</h4>
                    <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($totalInputVat, 2) }}</p>
                <p class="text-xs text-zinc-500 mt-2">{{ __('Purchases') }}: {{ number_format($purchasesVat, 2) }} | {{ __('Expenses') }}: {{ number_format($expensesVat, 2) }}</p>
            </div>

            <!-- Net VAT -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Net VAT') }}</h4>
                    <div class="p-2 {{ $netVat >= 0 ? 'bg-red-100 dark:bg-red-900/20' : 'bg-green-100 dark:bg-green-900/20' }} rounded-lg">
                        <svg class="w-5 h-5 {{ $netVat >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold {{ $netVat >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ number_format(abs($netVat), 2) }}</p>
                <p class="text-xs text-zinc-500 mt-2">{{ $netVat >= 0 ? __('VAT Payable') : __('VAT Reclaimable') }}</p>
            </div>
        </div>

        <!-- Detailed Breakdown -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">{{ __('VAT Breakdown') }}</h4>
                <flux:button wire:click="exportPdf" variant="ghost" size="sm" icon="arrow-down-tray">
                    {{ __('Export PDF') }}
                </flux:button>
            </div>

            <div class="space-y-6">
                <!-- Sales Section -->
                <div>
                    <h5 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-3 flex items-center gap-2">
                        <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                        {{ __('Sales (Output VAT)') }}
                    </h5>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                    <th class="text-left py-2 text-zinc-600 dark:text-zinc-400">{{ __('Date') }}</th>
                                    <th class="text-left py-2 text-zinc-600 dark:text-zinc-400">{{ __('Invoice') }}</th>
                                    <th class="text-left py-2 text-zinc-600 dark:text-zinc-400">{{ __('Customer') }}</th>
                                    <th class="text-right py-2 text-zinc-600 dark:text-zinc-400">{{ __('Subtotal') }}</th>
                                    <th class="text-right py-2 text-zinc-600 dark:text-zinc-400">{{ __('VAT') }}</th>
                                    <th class="text-right py-2 text-zinc-600 dark:text-zinc-400">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salesTransactions as $transaction)
                                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                        <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ $transaction->date->format('M d, Y') }}</td>
                                        <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ $transaction->invoice_number }}</td>
                                        <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ $transaction->customer->name }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($transaction->subtotal, 2) }}</td>
                                        <td class="py-2 text-right text-green-600 dark:text-green-400 font-semibold">{{ number_format($transaction->vat, 2) }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($transaction->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-zinc-500">{{ __('No sales transactions in this period') }}</td>
                                    </tr>
                                @endforelse
                                @if ($salesTransactions->count() > 0)
                                    <tr class="font-semibold bg-zinc-50 dark:bg-zinc-900">
                                        <td colspan="3" class="py-2 text-zinc-900 dark:text-zinc-100">{{ __('Total Sales') }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($salesSubtotal, 2) }}</td>
                                        <td class="py-2 text-right text-green-600 dark:text-green-400">{{ number_format($salesVat, 2) }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($salesTotal, 2) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Purchases Section -->
                <div>
                    <h5 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-3 flex items-center gap-2">
                        <span class="w-3 h-3 bg-blue-500 rounded-full"></span>
                        {{ __('Purchases (Input VAT)') }}
                    </h5>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                    <th class="text-left py-2 text-zinc-600 dark:text-zinc-400">{{ __('Date') }}</th>
                                    <th class="text-left py-2 text-zinc-600 dark:text-zinc-400">{{ __('Invoice') }}</th>
                                    <th class="text-left py-2 text-zinc-600 dark:text-zinc-400">{{ __('Supplier') }}</th>
                                    <th class="text-right py-2 text-zinc-600 dark:text-zinc-400">{{ __('Subtotal') }}</th>
                                    <th class="text-right py-2 text-zinc-600 dark:text-zinc-400">{{ __('VAT') }}</th>
                                    <th class="text-right py-2 text-zinc-600 dark:text-zinc-400">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($purchaseTransactions as $transaction)
                                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                        <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ $transaction->date->format('M d, Y') }}</td>
                                        <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ $transaction->invoice_number }}</td>
                                        <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ $transaction->supplier->name }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($transaction->subtotal, 2) }}</td>
                                        <td class="py-2 text-right text-blue-600 dark:text-blue-400 font-semibold">{{ number_format($transaction->vat, 2) }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($transaction->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-zinc-500">{{ __('No purchase transactions in this period') }}</td>
                                    </tr>
                                @endforelse
                                @if ($purchaseTransactions->count() > 0)
                                    <tr class="font-semibold bg-zinc-50 dark:bg-zinc-900">
                                        <td colspan="3" class="py-2 text-zinc-900 dark:text-zinc-100">{{ __('Total Purchases') }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($purchasesSubtotal, 2) }}</td>
                                        <td class="py-2 text-right text-blue-600 dark:text-blue-400">{{ number_format($purchasesVat, 2) }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($purchasesTotal, 2) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Expenses Section -->
                <div>
                    <h5 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-3 flex items-center gap-2">
                        <span class="w-3 h-3 bg-purple-500 rounded-full"></span>
                        {{ __('Expenses (Input VAT)') }}
                    </h5>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                    <th class="text-left py-2 text-zinc-600 dark:text-zinc-400">{{ __('Date') }}</th>
                                    <th class="text-left py-2 text-zinc-600 dark:text-zinc-400">{{ __('Number') }}</th>
                                    <th class="text-left py-2 text-zinc-600 dark:text-zinc-400">{{ __('Category') }}</th>
                                    <th class="text-right py-2 text-zinc-600 dark:text-zinc-400">{{ __('Amount') }}</th>
                                    <th class="text-right py-2 text-zinc-600 dark:text-zinc-400">{{ __('VAT') }}</th>
                                    <th class="text-right py-2 text-zinc-600 dark:text-zinc-400">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($expenseTransactions as $transaction)
                                    <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                        <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ $transaction->date->format('M d, Y') }}</td>
                                        <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ $transaction->expense_number }}</td>
                                        <td class="py-2 text-zinc-900 dark:text-zinc-100">{{ $transaction->category?->name ?? 'N/A' }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($transaction->amount, 2) }}</td>
                                        <td class="py-2 text-right text-purple-600 dark:text-purple-400 font-semibold">{{ number_format($transaction->vat, 2) }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($transaction->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-4 text-center text-zinc-500">{{ __('No expense transactions in this period') }}</td>
                                    </tr>
                                @endforelse
                                @if ($expenseTransactions->count() > 0)
                                    <tr class="font-semibold bg-zinc-50 dark:bg-zinc-900">
                                        <td colspan="3" class="py-2 text-zinc-900 dark:text-zinc-100">{{ __('Total Expenses') }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($expensesAmount, 2) }}</td>
                                        <td class="py-2 text-right text-purple-600 dark:text-purple-400">{{ number_format($expensesVat, 2) }}</td>
                                        <td class="py-2 text-right text-zinc-900 dark:text-zinc-100">{{ number_format($expensesTotal, 2) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
