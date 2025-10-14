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
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Expense Tracking') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Track and manage business expenses') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <flux:select wire:model.live="filterCategory" placeholder="{{ __('Filter by category') }}" class="flex-1">
                    <option value="">{{ __('All Categories') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filterSupplier" placeholder="{{ __('Filter by supplier') }}" class="flex-1">
                    <option value="">{{ __('All Suppliers') }}</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filterStatus" placeholder="{{ __('Filter by status') }}" class="flex-1">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="paid">{{ __('Paid') }}</option>
                </flux:select>

                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Expense') }}
                    </flux:button>
                @endif
            </div>
        </div>

        <!-- Add/Edit Form -->
        @if ($showForm)
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    {{ $editingId ? __('Edit Expense') : __('New Expense') }}
                </h4>

                <form wire:submit="save" class="space-y-4 sm:space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <flux:input wire:model="expense_number" :label="__('Expense Number')" type="text" required placeholder="EXP-001" />

                        <flux:select wire:model="expense_category_id" :label="__('Category')">
                            <option value="">{{ __('Select category') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model="supplier_id" :label="__('Supplier/Vendor')">
                            <option value="">{{ __('Select supplier') }}</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </flux:select>

                        <flux:input wire:model="date" :label="__('Date')" type="date" required />

                        <flux:input wire:model.blur="amount" :label="__('Amount')" type="number" step="0.01" min="0" required />

                        <flux:input wire:model.blur="vat" :label="__('VAT')" type="number" step="0.01" min="0" />

                        <flux:select wire:model="payment_method_id" :label="__('Payment Method')">
                            <option value="">{{ __('Select payment method') }}</option>
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model="bank_account_id" :label="__('Bank Account')" :required="$status === 'paid'">
                            <option value="">{{ __('Select bank account') }}</option>
                            @foreach ($bankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }} ({{ $account->currency }} {{ number_format($account->current_balance, 2) }})</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model="status" :label="__('Status')" required>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="paid">{{ __('Paid') }}</option>
                        </flux:select>

                        <flux:input wire:model="reference" :label="__('Reference')" type="text" placeholder="Invoice or reference number" class="sm:col-span-2" />
                    </div>

                    <!-- Total -->
                    <div class="bg-zinc-50 dark:bg-zinc-900 p-4 rounded-lg">
                        <label class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Total') }}</label>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ number_format($total, 2) }}</p>
                    </div>

                    <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Additional notes..." />

                    <!-- Document Upload -->
                    <div class="space-y-3">
                        <label class="block text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ __('Expense Document') }}</label>

                        @if($existingDocumentPath)
                            <div class="flex items-center gap-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ __('Attachment') }}</p>
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
                            {{ $editingId ? __('Update Expense') : __('Create Expense') }}
                        </flux:button>
                        <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                            {{ __('Cancel') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Expenses List -->
        <div class="space-y-3">
            @forelse ($expenses as $expense)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $expense->expense_number }}</h4>
                                @if ($expense->category)
                                    <flux:badge variant="info" size="sm">{{ $expense->category->name }}</flux:badge>
                                @endif
                                @if ($expense->supplier)
                                    <flux:badge variant="default" size="sm">{{ $expense->supplier->name }}</flux:badge>
                                @endif
                                <flux:badge :variant="$expense->status === 'paid' ? 'success' : 'warning'" size="sm">
                                    {{ ucfirst($expense->status) }}
                                </flux:badge>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 mt-3 text-xs">
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Date') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $expense->date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Amount') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ number_format($expense->amount, 2) }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('VAT') }}</span>
                                    <p class="font-semibold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($expense->vat, 2) }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Total') }}</span>
                                    <p class="font-semibold text-red-600 dark:text-red-400 mt-1">{{ number_format($expense->total, 2) }}</p>
                                </div>
                                @if ($expense->paymentMethod)
                                    <div>
                                        <span class="text-zinc-500 block">{{ __('Payment') }}</span>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $expense->paymentMethod->name }}</p>
                                    </div>
                                @endif
                            </div>

                            @if ($expense->reference)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3">{{ __('Ref') }}: {{ $expense->reference }}</p>
                            @endif
                            @if ($expense->notes)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">{{ $expense->notes }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 sm:ml-4">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button size="sm" variant="ghost" icon="ellipsis-vertical" icon-only>
                                    {{ __('Actions') }}
                                </flux:button>

                                <flux:menu class="min-w-48">
                                    <flux:menu.item wire:click="edit({{ $expense->id }})" icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item
                                        wire:click="delete({{ $expense->id }})"
                                        wire:confirm="Are you sure you want to delete this expense? This action cannot be undone."
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No expenses yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Add your first expense to start tracking business costs.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                        {{ __('Create First Expense') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($expenses->hasPages())
            <div class="mt-6">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
</section>
