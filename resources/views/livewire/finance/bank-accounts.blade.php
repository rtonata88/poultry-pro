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
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Bank Accounts') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Manage your business bank accounts') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <flux:select wire:model.live="filterStatus" placeholder="{{ __('Filter by status') }}" class="flex-1">
                    <option value="">{{ __('All Accounts') }}</option>
                    <option value="1">{{ __('Active') }}</option>
                    <option value="0">{{ __('Inactive') }}</option>
                </flux:select>

                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Bank Account') }}
                    </flux:button>
                @endif
            </div>
        </div>

        <!-- Add/Edit Form -->
        @if ($showForm)
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    {{ $editingId ? __('Edit Bank Account') : __('New Bank Account') }}
                </h4>

                <form wire:submit="save" class="space-y-4 sm:space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <flux:input wire:model="account_name" :label="__('Account Name')" type="text" required placeholder="Business Checking Account" />

                        <flux:input wire:model="account_number" :label="__('Account Number')" type="text" required placeholder="1234567890" />

                        <flux:input wire:model="bank_name" :label="__('Bank Name')" type="text" required placeholder="Standard Bank" />

                        <flux:input wire:model="branch" :label="__('Branch')" type="text" placeholder="Main Branch" />

                        <flux:select wire:model="account_type" :label="__('Account Type')" required>
                            <option value="checking">{{ __('Checking') }}</option>
                            <option value="savings">{{ __('Savings') }}</option>
                            <option value="business">{{ __('Business') }}</option>
                            <option value="other">{{ __('Other') }}</option>
                        </flux:select>

                        <flux:input wire:model="currency" :label="__('Currency')" type="text" maxlength="3" required placeholder="NAD" />

                        <flux:input wire:model="swift_code" :label="__('SWIFT Code')" type="text" placeholder="SBNMNANX" />

                        <flux:input wire:model="iban" :label="__('IBAN')" type="text" placeholder="NA12 1234 1234 1234 1234 1234 1234" />

                        <flux:input wire:model="opening_balance" :label="__('Opening Balance')" type="number" step="0.01" min="0" required />

                        <flux:input wire:model="current_balance" :label="__('Current Balance')" type="number" step="0.01" required />

                        <div class="flex items-center gap-2">
                            <flux:checkbox wire:model="is_active" :label="__('Active')" />
                        </div>
                    </div>

                    <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Additional notes..." />

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                            {{ $editingId ? __('Update Account') : __('Create Account') }}
                        </flux:button>
                        <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                            {{ __('Cancel') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Bank Accounts List -->
        <div class="space-y-3">
            @forelse ($bankAccounts as $account)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $account->account_name }}</h4>
                                <flux:badge variant="default" size="sm">{{ $account->bank_name }}</flux:badge>
                                <flux:badge :variant="$account->is_active ? 'success' : 'warning'" size="sm">
                                    {{ $account->is_active ? __('Active') : __('Inactive') }}
                                </flux:badge>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 mt-3 text-xs">
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Account Number') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $account->account_number }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Account Type') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ ucfirst($account->account_type) }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Currency') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $account->currency }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Opening Balance') }}</span>
                                    <p class="font-semibold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($account->opening_balance, 2) }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Current Balance') }}</span>
                                    <p class="font-semibold text-green-600 dark:text-green-400 mt-1">{{ number_format($account->current_balance, 2) }}</p>
                                </div>
                            </div>

                            @if ($account->branch || $account->swift_code || $account->iban)
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mt-3 text-xs">
                                    @if ($account->branch)
                                        <div>
                                            <span class="text-zinc-500 block">{{ __('Branch') }}</span>
                                            <p class="font-medium text-zinc-900 dark:text-zinc-100 mt-1">{{ $account->branch }}</p>
                                        </div>
                                    @endif
                                    @if ($account->swift_code)
                                        <div>
                                            <span class="text-zinc-500 block">{{ __('SWIFT Code') }}</span>
                                            <p class="font-medium text-zinc-900 dark:text-zinc-100 mt-1">{{ $account->swift_code }}</p>
                                        </div>
                                    @endif
                                    @if ($account->iban)
                                        <div>
                                            <span class="text-zinc-500 block">{{ __('IBAN') }}</span>
                                            <p class="font-medium text-zinc-900 dark:text-zinc-100 mt-1">{{ $account->iban }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($account->notes)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3">{{ $account->notes }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 sm:ml-4">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button size="sm" variant="ghost" icon="ellipsis-vertical" icon-only>
                                    {{ __('Actions') }}
                                </flux:button>

                                <flux:menu class="min-w-48">
                                    <flux:menu.item href="{{ route('finance.bank-statements', ['bank_account_id' => $account->id]) }}" icon="document-text" wire:navigate>
                                        {{ __('View Statement') }}
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item wire:click="edit({{ $account->id }})" icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item
                                        wire:click="delete({{ $account->id }})"
                                        wire:confirm="Are you sure you want to delete this bank account? This action cannot be undone."
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No bank accounts yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Add your first bank account to start tracking finances.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                        {{ __('Create First Account') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($bankAccounts->hasPages())
            <div class="mt-6">
                {{ $bankAccounts->links() }}
            </div>
        @endif
    </div>
</section>
