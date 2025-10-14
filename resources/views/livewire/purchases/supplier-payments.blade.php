<section class="w-full">
    @if (session('status'))
        <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
            <flux:text class="text-green-700 dark:text-green-400 font-medium">
                {{ session('status') }}
            </flux:text>
        </div>
    @endif

    <div class="space-y-6">
        <!-- Header Actions -->
        <div class="flex flex-col gap-4">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Supplier Payments') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Record payments to suppliers') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <flux:select wire:model.live="filterSupplier" placeholder="{{ __('Filter by supplier') }}" class="flex-1">
                    <option value="">{{ __('All Suppliers') }}</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </flux:select>

                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Payment') }}
                    </flux:button>
                @endif
            </div>
        </div>

        <!-- Add/Edit Form -->
        @if ($showForm)
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    {{ $editingId ? __('Edit Payment') : __('New Payment') }}
                </h4>

                <form wire:submit="save" class="space-y-4 sm:space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <flux:input wire:model="payment_number" :label="__('Payment Number')" type="text" required placeholder="PAY-00001" />

                        <flux:input wire:model="date" :label="__('Payment Date')" type="date" required />

                        <flux:select wire:model.live="supplier_id" :label="__('Supplier')" required>
                            <option value="">{{ __('Select supplier') }}</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="supplier_invoice_id" :label="__('Invoice')" required>
                            <option value="">{{ __('Select invoice') }}</option>
                            @foreach ($unpaidInvoices as $invoice)
                                <option value="{{ $invoice->id }}">
                                    {{ $invoice->invoice_number }} - Balance: {{ number_format($invoice->balance, 2) }}
                                </option>
                            @endforeach
                        </flux:select>

                        <flux:input wire:model.live.debounce.500ms="amount" :label="__('Amount')" type="number" step="0.01" min="0.01" required placeholder="0.00" />

                        <flux:select wire:model="payment_method_id" :label="__('Payment Method')" required>
                            <option value="">{{ __('Select payment method') }}</option>
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model="bank_account_id" :label="__('Bank Account')" required>
                            <option value="">{{ __('Select bank account') }}</option>
                            @foreach ($bankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }} ({{ $account->currency }} {{ number_format($account->current_balance, 2) }})</option>
                            @endforeach
                        </flux:select>

                        <flux:input wire:model="reference" :label="__('Reference')" type="text" placeholder="Transaction reference" class="sm:col-span-2" />
                    </div>

                    <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Additional notes..." />

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                            {{ $editingId ? __('Update Payment') : __('Create Payment') }}
                        </flux:button>
                        <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                            {{ __('Cancel') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Payments List -->
        <div class="space-y-3">
            @forelse ($payments as $payment)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $payment->payment_number }}</h4>
                                <flux:badge variant="info" size="sm">{{ $payment->supplier->name }}</flux:badge>
                                <flux:badge variant="success" size="sm">{{ $payment->paymentMethod->name }}</flux:badge>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-3 text-xs">
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Date') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $payment->date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Invoice') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $payment->supplierInvoice->invoice_number }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Amount') }}</span>
                                    <p class="font-semibold text-green-600 dark:text-green-400 mt-1">{{ number_format($payment->amount, 2) }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Method') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $payment->paymentMethod->name }}</p>
                                </div>
                            </div>

                            @if ($payment->reference)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3">{{ __('Ref') }}: {{ $payment->reference }}</p>
                            @endif
                        </div>
                        <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                            <flux:button wire:click="edit({{ $payment->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                <span class="sm:inline">{{ __('Edit') }}</span>
                            </flux:button>
                            <flux:button
                                wire:click="delete({{ $payment->id }})"
                                wire:confirm="Are you sure you want to delete this payment? This will reverse the payment on the invoice."
                                size="sm"
                                variant="danger"
                                icon="trash"
                                class="flex-1 sm:flex-none sm:w-auto">
                                <span class="sm:inline">{{ __('Delete') }}</span>
                            </flux:button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <div class="mx-auto w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No payments yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Record your first supplier payment to start tracking.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                        {{ __('Create First Payment') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($payments->hasPages())
            <div class="mt-6">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</section>
