<section class="w-full">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Bank Statements') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('View detailed transaction history for bank accounts') }}</p>
            </div>
            @if($bank_account_id && $selectedAccount)
                <flux:button wire:click="downloadPdf" variant="primary" icon="arrow-down-tray">
                    {{ __('Download PDF') }}
                </flux:button>
            @endif
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <flux:select wire:model.live="bank_account_id" :label="__('Bank Account')" required>
                    <option value="">{{ __('Select bank account') }}</option>
                    @foreach ($bankAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model.live="start_date" :label="__('Start Date')" type="date" required />

                <flux:input wire:model.live="end_date" :label="__('End Date')" type="date" required />

                <flux:select wire:model.live="transaction_type" :label="__('Transaction Type')">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="expense">{{ __('Expense') }}</option>
                    <option value="supplier_payment">{{ __('Supplier Payment') }}</option>
                    <option value="customer_payment">{{ __('Customer Payment') }}</option>
                    <option value="transfer_out">{{ __('Transfer Out') }}</option>
                    <option value="transfer_in">{{ __('Transfer In') }}</option>
                </flux:select>
            </div>
        </div>

        @if ($bank_account_id && $selectedAccount)
            <!-- Account Summary -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ $selectedAccount->account_name }}</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-zinc-500">{{ __('Opening Balance') }}</p>
                        <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $selectedAccount->currency }} {{ number_format($openingBalance, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500">{{ __('Total In') }}</p>
                        <p class="text-lg font-semibold text-green-600 dark:text-green-400 mt-1">{{ $selectedAccount->currency }} {{ number_format($totalIn, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500">{{ __('Total Out') }}</p>
                        <p class="text-lg font-semibold text-red-600 dark:text-red-400 mt-1">{{ $selectedAccount->currency }} {{ number_format($totalOut, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500">{{ __('Closing Balance') }}</p>
                        <p class="text-lg font-semibold text-blue-600 dark:text-blue-400 mt-1">{{ $selectedAccount->currency }} {{ number_format($closingBalance, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Transactions -->
            @if ($transactions->count() > 0)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Description') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Debit') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Credit') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Balance') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($transactions as $transaction)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ $transaction->transaction_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $transaction->description }}
                                            @if ($transaction->reference)
                                                <span class="text-zinc-500 text-xs block">Ref: {{ $transaction->reference }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            <flux:badge size="sm" :variant="$transaction->amount > 0 ? 'success' : 'danger'">
                                                {{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}
                                            </flux:badge>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-red-600 dark:text-red-400 whitespace-nowrap">
                                            @if ($transaction->amount < 0)
                                                {{ number_format(abs($transaction->amount), 2) }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-green-600 dark:text-green-400 whitespace-nowrap">
                                            @if ($transaction->amount > 0)
                                                {{ number_format($transaction->amount, 2) }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right font-semibold text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ number_format($transaction->balance_after, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <div class="mx-auto w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No transactions found') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('There are no transactions for the selected period.') }}</p>
                </div>
            @endif
        @else
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                <div class="mx-auto w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('Select a bank account') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Choose a bank account to view its statement.') }}</p>
            </div>
        @endif
    </div>
</section>
