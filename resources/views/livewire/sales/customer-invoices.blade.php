<section class="w-full">
    @if (session('status'))
        <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
            <flux:text class="text-green-700 dark:text-green-400 font-medium">
                {{ session('status') }}
            </flux:text>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
            <flux:text class="text-red-700 dark:text-red-400 font-medium">
                {{ session('error') }}
            </flux:text>
        </div>
    @endif

    <div class="space-y-6">
        <!-- Header Actions -->
        <div class="flex flex-col gap-4">
            <div>
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Customer Invoices') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Track invoices to customers') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <flux:select wire:model.live="filterCustomer" placeholder="{{ __('Filter by customer') }}" class="flex-1">
                    <option value="">{{ __('All Customers') }}</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filterStatus" placeholder="{{ __('Filter by status') }}" class="flex-1">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="unpaid">{{ __('Unpaid') }}</option>
                    <option value="partial">{{ __('Partial') }}</option>
                    <option value="paid">{{ __('Paid') }}</option>
                    <option value="overdue">{{ __('Overdue') }}</option>
                </flux:select>

                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Invoice') }}
                    </flux:button>
                @endif
            </div>
        </div>

        <!-- Add/Edit Form -->
        @if ($showForm)
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    {{ $editingId ? __('Edit Invoice') : __('New Invoice') }}
                </h4>

                <form wire:submit="save" class="space-y-4 sm:space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <flux:input wire:model="invoice_number" :label="__('Invoice Number')" type="text" required placeholder="INV-001" />

                        <flux:select wire:model.live="customer_id" :label="__('Customer')" required>
                            <option value="">{{ __('Select customer') }}</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model.live="customer_quotation_id" :label="__('Link to Quotation (Optional)')" class="sm:col-span-2">
                            <option value="">{{ __('No quotation') }}</option>
                            @foreach ($acceptedQuotations as $quotation)
                                <option value="{{ $quotation->id }}">
                                    {{ $quotation->quotation_number }} - {{ number_format($quotation->total, 2) }} ({{ $quotation->date->format('M d, Y') }})
                                </option>
                            @endforeach
                        </flux:select>

                        <flux:input wire:model="date" :label="__('Invoice Date')" type="date" required />

                        <flux:input wire:model="due_date" :label="__('Due Date')" type="date" required />

                        <flux:input wire:model="reference" :label="__('Reference')" type="text" placeholder="PO number or reference" class="sm:col-span-2" />
                    </div>

                    <!-- Line Items -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h5 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Line Items') }}</h5>
                            <flux:button type="button" wire:click="addItem" variant="ghost" size="sm" icon="plus">
                                {{ __('Add Item') }}
                            </flux:button>
                        </div>

                        @foreach ($items as $index => $item)
                            <div class="grid grid-cols-12 gap-2 sm:gap-3 p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg">
                                <div class="col-span-12 sm:col-span-5 space-y-2">
                                    <flux:select wire:change="selectProduct({{ $index }}, $event.target.value)" wire:model="items.{{ $index }}.product_id" placeholder="Select product">
                                        <option value="">{{ __('Select product') }}</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} - {{ number_format($product->price, 2) }}</option>
                                        @endforeach
                                    </flux:select>
                                    <flux:input wire:model.live.debounce.500ms="items.{{ $index }}.description" placeholder="Item description or customize" required />
                                </div>
                                <div class="col-span-4 sm:col-span-2">
                                    <flux:input wire:model.blur="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01" placeholder="Qty" required />
                                </div>
                                <div class="col-span-4 sm:col-span-2">
                                    <flux:input wire:model.blur="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" placeholder="Price" required />
                                </div>
                                <div class="col-span-3 sm:col-span-2">
                                    <flux:input value="{{ number_format($item['total'], 2) }}" placeholder="Total" disabled />
                                </div>
                                <div class="col-span-1 flex items-center justify-center">
                                    @if (count($items) > 1)
                                        <flux:button type="button" wire:click="removeItem({{ $index }})" variant="danger" size="sm" icon="trash" icon-only />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Totals -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5 bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                        <div>
                            <label class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Subtotal') }}</label>
                            <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ number_format($subtotal, 2) }}</p>
                        </div>
                        <flux:input wire:model.blur="vat" :label="__('VAT')" type="number" step="0.01" min="0" value="0" />
                        <flux:input wire:model.blur="discount" :label="__('Discount')" type="number" step="0.01" min="0" value="0" />
                        <div class="sm:col-span-3">
                            <label class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Total') }}</label>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ number_format($total, 2) }}</p>
                        </div>
                    </div>

                    <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Additional notes..." />

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                            {{ $editingId ? __('Update Invoice') : __('Create Invoice') }}
                        </flux:button>
                        <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                            {{ __('Cancel') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Invoices List -->
        <div class="space-y-3">
            @forelse ($invoices as $invoice)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $invoice->invoice_number }}</h4>
                                <flux:badge variant="info" size="sm">{{ $invoice->customer->name }}</flux:badge>
                                <flux:badge :variant="$invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning')" size="sm">
                                    {{ ucfirst($invoice->status) }}
                                </flux:badge>
                                @if ($invoice->customer_quotation_id)
                                    <flux:badge variant="default" size="sm">{{ $invoice->quotation->quotation_number }}</flux:badge>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mt-3 text-xs">
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Date') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $invoice->date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Due Date') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $invoice->due_date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Subtotal') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ number_format($invoice->subtotal, 2) }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('VAT') }}</span>
                                    <p class="font-semibold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($invoice->vat, 2) }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Total') }}</span>
                                    <p class="font-semibold text-green-600 dark:text-green-400 mt-1">{{ number_format($invoice->total, 2) }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Balance') }}</span>
                                    <p class="font-semibold text-red-600 dark:text-red-400 mt-1">{{ number_format($invoice->balance, 2) }}</p>
                                </div>
                            </div>

                            @if ($invoice->reference)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3">{{ __('Ref') }}: {{ $invoice->reference }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 sm:ml-4">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button size="sm" variant="ghost" icon="ellipsis-vertical" icon-only>
                                    {{ __('Actions') }}
                                </flux:button>

                                <flux:menu class="min-w-48">
                                    <flux:menu.item wire:click="edit({{ $invoice->id }})" icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>

                                    @if ($invoice->status !== 'paid')
                                        <flux:menu.item wire:click="recordPayment({{ $invoice->id }})" icon="banknotes">
                                            {{ __('Record Payment') }}
                                        </flux:menu.item>
                                    @endif

                                    <flux:menu.item wire:click="downloadPdf({{ $invoice->id }})" icon="arrow-down-tray">
                                        {{ __('Download PDF') }}
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item
                                        wire:click="delete({{ $invoice->id }})"
                                        wire:confirm="Are you sure you want to delete this invoice? This action cannot be undone."
                                        icon="trash"
                                        variant="danger">
                                        {{ __('Delete') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <div class="mx-auto w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No invoices yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Add your first customer invoice to start tracking sales.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                        {{ __('Create First Invoice') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($invoices->hasPages())
            <div class="mt-6">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</section>
