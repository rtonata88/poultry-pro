<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Payment Methods')" :subheading="__('Configure accepted payment methods for your business')">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <flux:text class="text-green-700 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </flux:text>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Payment Methods') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Manage your accepted payment options') }}</p>
                </div>
                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Payment Method') }}
                    </flux:button>
                @endif
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Payment Method') : __('New Payment Method') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4 sm:space-y-5">
                        <flux:input wire:model="name" :label="__('Method Name')" type="text" required placeholder="e.g., Credit Card, Bank Transfer, Cash" />
                        <flux:textarea wire:model="description" :label="__('Description')" rows="3" placeholder="Brief description of this payment method..." />

                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <flux:checkbox wire:model="is_active" :label="__('Active')" />
                            <flux:text class="text-xs text-zinc-500">{{ __('Inactive methods will not appear in payment forms') }}</flux:text>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Method') : __('Create Method') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Payment Methods List -->
            <div class="space-y-3">
                @forelse ($methods as $method)
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                    <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $method->name }}</h4>
                                    <flux:badge :variant="$method->is_active ? 'success' : 'danger'" size="sm">
                                        {{ $method->is_active ? __('Active') : __('Inactive') }}
                                    </flux:badge>
                                </div>
                                @if ($method->description)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2 leading-relaxed">{{ $method->description }}</p>
                                @endif
                                <div class="text-xs text-zinc-500 dark:text-zinc-500 mt-3">
                                    {{ __('Created') }} {{ $method->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                                <flux:button wire:click="edit({{ $method->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                    <span class="sm:inline">{{ __('Edit') }}</span>
                                </flux:button>
                                <flux:button
                                    wire:click="delete({{ $method->id }})"
                                    wire:confirm="Are you sure you want to delete this payment method? This action cannot be undone."
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No payment methods yet') }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Add your first payment method to start accepting different types of payments.') }}</p>
                        <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                            {{ __('Create First Method') }}
                        </flux:button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($methods->hasPages())
                <div class="mt-6">
                    {{ $methods->links() }}
                </div>
            @endif
        </div>
    </x-settings.layout>
</section>
