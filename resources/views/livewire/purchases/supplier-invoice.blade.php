<section class="w-full">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Supplier Invoice') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $invoice->invoice_number }}</p>
            </div>
            <div class="flex items-center gap-3">
                <flux:button href="{{ route('purchases.invoices') }}" variant="ghost" icon="arrow-left" wire:navigate>
                    {{ __('Back to Invoices') }}
                </flux:button>
                <flux:button wire:click="downloadPdf" variant="primary" icon="arrow-down-tray">
                    {{ __('Download PDF') }}
                </flux:button>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:justify-between gap-6 mb-6">
                <div>
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-3">{{ __('Supplier Details') }}</h4>
                    <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ $invoice->supplier->name }}</p>
                    @if($invoice->supplier->email)
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $invoice->supplier->email }}</p>
                    @endif
                    @if($invoice->supplier->phone)
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $invoice->supplier->phone }}</p>
                    @endif
                    @if($invoice->supplier->address)
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">{{ $invoice->supplier->address }}</p>
                    @endif
                </div>
                <div class="text-left sm:text-right">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-3">{{ __('Invoice Details') }}</h4>
                    <div class="space-y-1">
                        <p class="text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Invoice Number') }}:</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100 ml-2">{{ $invoice->invoice_number }}</span>
                        </p>
                        <p class="text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Date') }}:</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100 ml-2">{{ $invoice->date->format('M d, Y') }}</span>
                        </p>
                        <p class="text-sm">
                            <span class="text-zinc-500 dark:text-zinc-400">{{ __('Due Date') }}:</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100 ml-2">{{ $invoice->due_date->format('M d, Y') }}</span>
                        </p>
                        @if($invoice->reference)
                            <p class="text-sm">
                                <span class="text-zinc-500 dark:text-zinc-400">{{ __('Reference') }}:</span>
                                <span class="font-semibold text-zinc-900 dark:text-zinc-100 ml-2">{{ $invoice->reference }}</span>
                            </p>
                        @endif
                        <div class="pt-2">
                            <flux:badge :variant="$invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning')">
                                {{ ucfirst($invoice->status) }}
                            </flux:badge>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="mt-6">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-3">{{ __('Line Items') }}</h4>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Description') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Quantity') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Unit Price') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($invoice->items as $item)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-zinc-900 dark:text-zinc-100">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-zinc-900 dark:text-zinc-100">NAD {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-zinc-900 dark:text-zinc-100">NAD {{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals -->
            <div class="mt-6 flex justify-end">
                <div class="w-full sm:w-1/2 lg:w-1/3 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Subtotal') }}</span>
                        <span class="font-semibold text-zinc-900 dark:text-zinc-100">NAD {{ number_format($invoice->subtotal, 2) }}</span>
                    </div>
                    @if($invoice->vat > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('VAT') }}</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">NAD {{ number_format($invoice->vat, 2) }}</span>
                        </div>
                    @endif
                    @if($invoice->discount > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('Discount') }}</span>
                            <span class="font-semibold text-zinc-900 dark:text-zinc-100">-NAD {{ number_format($invoice->discount, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-base font-bold border-t border-zinc-200 dark:border-zinc-700 pt-2">
                        <span class="text-zinc-900 dark:text-zinc-100">{{ __('Total') }}</span>
                        <span class="text-zinc-900 dark:text-zinc-100">NAD {{ number_format($invoice->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Amount Paid') }}</span>
                        <span class="font-semibold text-green-600 dark:text-green-400">NAD {{ number_format($invoice->amount_paid ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-bold border-t border-zinc-200 dark:border-zinc-700 pt-2">
                        <span class="text-zinc-900 dark:text-zinc-100">{{ __('Balance Due') }}</span>
                        <span class="text-red-600 dark:text-red-400">NAD {{ number_format($invoice->balance, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($invoice->notes)
                <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-2">{{ __('Notes') }}</h4>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $invoice->notes }}</p>
                </div>
            @endif

            <!-- Document -->
            @if($invoice->document_path)
                <div class="mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-3">{{ __('Attached Document') }}</h4>
                    <div class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ __('Attachment') }}</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ \Storage::url($invoice->document_path) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                {{ __('View') }}
                            </a>
                            <a href="{{ \Storage::url($invoice->document_path) }}" download="{{ $invoice->invoice_number . '.' . pathinfo($invoice->document_path, PATHINFO_EXTENSION) }}" class="flex items-center gap-2 px-4 py-2 bg-zinc-600 hover:bg-zinc-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                {{ __('Download') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Payments -->
        @if($invoice->payments->count() > 0)
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Payment History') }}</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Payment Number') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Method') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($invoice->payments as $payment)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                        {{ $payment->date->format('M d, Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $payment->payment_number }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                        {{ $payment->paymentMethod->name ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-green-600 dark:text-green-400">
                                        NAD {{ number_format($payment->amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</section>
