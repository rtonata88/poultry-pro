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
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Supplier Invoices') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Track invoices from suppliers') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <flux:select wire:model.live="filterSupplier" placeholder="{{ __('Filter by supplier') }}" class="flex-1">
                    <option value="">{{ __('All Suppliers') }}</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
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

                        <flux:select wire:model="supplier_id" :label="__('Supplier')" required>
                            <option value="">{{ __('Select supplier') }}</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
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
                                <div class="col-span-12 sm:col-span-5">
                                    <flux:input wire:model.live.debounce.500ms="items.{{ $index }}.description" placeholder="Item description" required />
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

                    <!-- Document Upload -->
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Invoice Document') }}</label>

                        @if($existingDocumentPath)
                            <div class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ __('Document uploaded') }}</p>
                                    <p class="text-xs text-blue-700 dark:text-blue-300">{{ basename($existingDocumentPath) }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ \Storage::url($existingDocumentPath) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <button type="button" wire:click="removeDocument" wire:confirm="Are you sure you want to remove this document?" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif

                        <div>
                            <input type="file" wire:model="document" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-zinc-900 dark:text-zinc-100 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-blue-900/20 dark:file:text-blue-400 dark:hover:file:bg-blue-900/40">
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('PDF, JPG, JPEG, or PNG. Max 10MB.') }}</p>
                            @error('document') <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span> @enderror
                        </div>

                        @if($document)
                            <div class="flex items-center gap-2 p-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                                <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-sm text-green-900 dark:text-green-100">{{ __('New document ready to upload') }}: {{ $document->getClientOriginalName() }}</p>
                            </div>
                        @endif
                    </div>

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
                                <flux:badge variant="info" size="sm">{{ $invoice->supplier->name }}</flux:badge>
                                <flux:badge :variant="$invoice->status === 'paid' ? 'success' : ($invoice->status === 'overdue' ? 'danger' : 'warning')" size="sm">
                                    {{ ucfirst($invoice->status) }}
                                </flux:badge>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-3 text-xs">
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Date') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $invoice->date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Due Date') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $invoice->due_date->format('M d, Y') }}</p>
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
                        <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                            <flux:button href="{{ route('purchases.supplier-invoice', ['invoiceId' => $invoice->id]) }}" size="sm" variant="outline" icon="document-text" wire:navigate class="flex-1 sm:flex-none sm:w-auto">
                                <span class="sm:inline">{{ __('View') }}</span>
                            </flux:button>
                            <flux:button wire:click="edit({{ $invoice->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                <span class="sm:inline">{{ __('Edit') }}</span>
                            </flux:button>
                            <flux:button
                                wire:click="delete({{ $invoice->id }})"
                                wire:confirm="Are you sure you want to delete this invoice? This action cannot be undone."
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No invoices yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Add your first supplier invoice to start tracking purchases.') }}</p>
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
