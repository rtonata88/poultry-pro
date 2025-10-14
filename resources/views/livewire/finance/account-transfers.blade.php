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
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Account Transfers') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Transfer funds between bank accounts') }}</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <flux:select wire:model.live="filterFromAccount" placeholder="{{ __('Filter by from account') }}" class="flex-1">
                    <option value="">{{ __('All Source Accounts') }}</option>
                    @foreach ($bankAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filterToAccount" placeholder="{{ __('Filter by to account') }}" class="flex-1">
                    <option value="">{{ __('All Destination Accounts') }}</option>
                    @foreach ($bankAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->account_name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filterStatus" placeholder="{{ __('Filter by status') }}" class="flex-1">
                    <option value="">{{ __('All Statuses') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="completed">{{ __('Completed') }}</option>
                    <option value="cancelled">{{ __('Cancelled') }}</option>
                </flux:select>

                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('New Transfer') }}
                    </flux:button>
                @endif
            </div>
        </div>

        <!-- Add/Edit Form -->
        @if ($showForm)
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    {{ $editingId ? __('Edit Transfer') : __('New Transfer') }}
                </h4>

                <form wire:submit="save" class="space-y-4 sm:space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <flux:input wire:model="transfer_number" :label="__('Transfer Number')" type="text" required placeholder="TRF-00001" />

                        <flux:input wire:model="transfer_date" :label="__('Transfer Date')" type="date" required />

                        <flux:select wire:model="from_account_id" :label="__('From Account')" required>
                            <option value="">{{ __('Select source account') }}</option>
                            @foreach ($bankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }} ({{ $account->currency }} {{ number_format($account->current_balance, 2) }})</option>
                            @endforeach
                        </flux:select>

                        <flux:select wire:model="to_account_id" :label="__('To Account')" required>
                            <option value="">{{ __('Select destination account') }}</option>
                            @foreach ($bankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_name }} ({{ $account->currency }} {{ number_format($account->current_balance, 2) }})</option>
                            @endforeach
                        </flux:select>

                        <flux:input wire:model="amount" :label="__('Amount')" type="number" step="0.01" min="0.01" required />

                        <flux:select wire:model="status" :label="__('Status')" required>
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="completed">{{ __('Completed') }}</option>
                            <option value="cancelled">{{ __('Cancelled') }}</option>
                        </flux:select>

                        <flux:input wire:model="reference" :label="__('Reference')" type="text" placeholder="Reference number" class="sm:col-span-2" />
                    </div>

                    <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Additional notes..." />

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                            {{ $editingId ? __('Update Transfer') : __('Create Transfer') }}
                        </flux:button>
                        <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                            {{ __('Cancel') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Transfers List -->
        <div class="space-y-3">
            @forelse ($transfers as $transfer)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $transfer->transfer_number }}</h4>
                                <flux:badge :variant="$transfer->status === 'completed' ? 'success' : ($transfer->status === 'cancelled' ? 'danger' : 'warning')" size="sm">
                                    {{ ucfirst($transfer->status) }}
                                </flux:badge>
                            </div>

                            <div class="mt-3 space-y-2">
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-zinc-500">{{ __('From') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $transfer->fromAccount->account_name }}</span>
                                    <span class="text-zinc-400">→</span>
                                    <span class="text-zinc-500">{{ __('To') }}:</span>
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $transfer->toAccount->account_name }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-3 text-xs">
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Date') }}</span>
                                    <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $transfer->transfer_date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-zinc-500 block">{{ __('Amount') }}</span>
                                    <p class="font-semibold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($transfer->amount, 2) }}</p>
                                </div>
                                @if ($transfer->reference)
                                    <div>
                                        <span class="text-zinc-500 block">{{ __('Reference') }}</span>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $transfer->reference }}</p>
                                    </div>
                                @endif
                            </div>

                            @if ($transfer->notes)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3">{{ $transfer->notes }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 sm:ml-4">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button size="sm" variant="ghost" icon="ellipsis-vertical" icon-only>
                                    {{ __('Actions') }}
                                </flux:button>

                                <flux:menu class="min-w-48">
                                    <flux:menu.item wire:click="edit({{ $transfer->id }})" icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item
                                        wire:click="delete({{ $transfer->id }})"
                                        wire:confirm="Are you sure you want to delete this transfer? This action cannot be undone and will reverse any balance changes."
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No transfers yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Create your first transfer to move funds between accounts.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                        {{ __('Create First Transfer') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($transfers->hasPages())
            <div class="mt-6">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
</section>
