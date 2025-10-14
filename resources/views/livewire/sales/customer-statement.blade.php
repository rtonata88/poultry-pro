<section class="w-full">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Customer Statement') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $customer->name }}</p>
            </div>
            <div class="flex items-center gap-3">
                <flux:button href="{{ route('sales.customers') }}" variant="ghost" icon="arrow-left" wire:navigate>
                    {{ __('Back to Customers') }}
                </flux:button>
                <flux:button wire:click="downloadPdf" variant="primary" icon="arrow-down-tray">
                    {{ __('Download PDF') }}
                </flux:button>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:input wire:model.live="startDate" :label="__('Start Date')" type="date" />
                <flux:input wire:model.live="endDate" :label="__('End Date')" type="date" />
            </div>
        </div>

        <!-- Customer Details -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
            <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ __('Customer Details') }}</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</p>
                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $customer->name }}</p>
                </div>
                @if($customer->email)
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $customer->email }}</p>
                    </div>
                @endif
                @if($customer->phone)
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Phone') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $customer->phone }}</p>
                    </div>
                @endif
                @if($customer->address)
                    <div>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Address') }}</p>
                        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $customer->address }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Statement Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Opening Balance') }}</p>
                <p class="text-2xl font-bold {{ $openingBalance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} mt-1">
                    NAD {{ number_format(abs($openingBalance), 2) }}
                </p>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Total Invoiced') }}</p>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                    NAD {{ number_format($transactions->where('type', 'invoice')->sum('debit'), 2) }}
                </p>
            </div>
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Total Paid') }}</p>
                <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">
                    NAD {{ number_format($transactions->where('type', 'payment')->sum('credit'), 2) }}
                </p>
            </div>
        </div>

        <!-- Statement Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('Statement Period') }}: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                </h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Reference') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Description') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Debit') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Credit') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        <!-- Opening Balance -->
                        <tr class="bg-zinc-50 dark:bg-zinc-900">
                            <td colspan="3" class="px-4 py-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ __('Opening Balance') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right text-zinc-900 dark:text-zinc-100">-</td>
                            <td class="px-4 py-3 text-sm text-right text-zinc-900 dark:text-zinc-100">-</td>
                            <td class="px-4 py-3 text-sm text-right font-semibold {{ $openingBalance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                NAD {{ number_format(abs($openingBalance), 2) }}
                            </td>
                        </tr>

                        <!-- Transactions -->
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($transaction['date'])->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $transaction['reference'] }}
                                </td>
                                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                    {{ $transaction['description'] }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right {{ $transaction['debit'] > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-zinc-400' }}">
                                    {{ $transaction['debit'] > 0 ? 'NAD ' . number_format($transaction['debit'], 2) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right {{ $transaction['credit'] > 0 ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-zinc-400' }}">
                                    {{ $transaction['credit'] > 0 ? 'NAD ' . number_format($transaction['credit'], 2) : '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold {{ $transaction['balance'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    NAD {{ number_format(abs($transaction['balance']), 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500">
                                    {{ __('No transactions in this period') }}
                                </td>
                            </tr>
                        @endforelse

                        <!-- Closing Balance -->
                        <tr class="bg-zinc-50 dark:bg-zinc-900 border-t-2 border-zinc-300 dark:border-zinc-600">
                            <td colspan="3" class="px-4 py-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ __('Closing Balance') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-zinc-900 dark:text-zinc-100">
                                NAD {{ number_format($transactions->sum('debit'), 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-semibold text-zinc-900 dark:text-zinc-100">
                                NAD {{ number_format($transactions->sum('credit'), 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-bold text-lg {{ $closingBalance > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                NAD {{ number_format(abs($closingBalance), 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($closingBalance > 0)
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <p class="text-sm text-red-800 dark:text-red-400">
                    <strong>{{ __('Amount Owed by Customer') }}:</strong> NAD {{ number_format($closingBalance, 2) }}
                </p>
            </div>
        @elseif($closingBalance < 0)
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <p class="text-sm text-green-800 dark:text-green-400">
                    <strong>{{ __('Credit Balance') }}:</strong> NAD {{ number_format(abs($closingBalance), 2) }}
                </p>
            </div>
        @else
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <p class="text-sm text-blue-800 dark:text-blue-400">
                    <strong>{{ __('Account Status') }}:</strong> {{ __('Settled') }}
                </p>
            </div>
        @endif
    </div>
</section>
