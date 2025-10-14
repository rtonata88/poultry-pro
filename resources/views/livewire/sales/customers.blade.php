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
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('All Customers') }}</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Manage your customer relationships') }}</p>
            </div>
            @if (!$showForm)
                <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                    {{ __('Add Customer') }}
                </flux:button>
            @endif
        </div>

        <!-- Add/Edit Form -->
        @if ($showForm)
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                    {{ $editingId ? __('Edit Customer') : __('New Customer') }}
                </h4>

                <form wire:submit="save" class="space-y-4 sm:space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <flux:input wire:model="name" :label="__('Customer Name')" type="text" required placeholder="Company or person name" class="sm:col-span-2" />

                        <flux:input wire:model="contact_person" :label="__('Contact Person')" type="text" placeholder="Primary contact" />

                        <flux:input wire:model="phone" :label="__('Phone')" type="tel" placeholder="+1234567890" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <flux:input wire:model="email" :label="__('Email')" type="email" placeholder="customer@example.com" />

                        <flux:input wire:model="tax_id" :label="__('Tax ID / VAT')" type="text" placeholder="Tax identification number" />
                    </div>

                    <flux:textarea wire:model="address" :label="__('Address')" rows="3" placeholder="Full address..." />

                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <flux:checkbox wire:model="is_active" :label="__('Active')" />
                        <flux:text class="text-xs text-zinc-500">{{ __('Inactive customers will not appear in sales forms') }}</flux:text>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                            {{ $editingId ? __('Update Customer') : __('Create Customer') }}
                        </flux:button>
                        <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                            {{ __('Cancel') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        @endif

        <!-- Customers List -->
        <div class="space-y-3">
            @forelse ($customers as $customer)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $customer->name }}</h4>
                                <flux:badge :variant="$customer->is_active ? 'success' : 'danger'" size="sm">
                                    {{ $customer->is_active ? __('Active') : __('Inactive') }}
                                </flux:badge>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mt-3 text-xs">
                                @if ($customer->contact_person)
                                    <div>
                                        <span class="text-zinc-500 block">{{ __('Contact') }}</span>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $customer->contact_person }}</p>
                                    </div>
                                @endif
                                @if ($customer->email)
                                    <div>
                                        <span class="text-zinc-500 block">{{ __('Email') }}</span>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $customer->email }}</p>
                                    </div>
                                @endif
                                @if ($customer->phone)
                                    <div>
                                        <span class="text-zinc-500 block">{{ __('Phone') }}</span>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $customer->phone }}</p>
                                    </div>
                                @endif
                                @if ($customer->tax_id)
                                    <div>
                                        <span class="text-zinc-500 block">{{ __('Tax ID') }}</span>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $customer->tax_id }}</p>
                                    </div>
                                @endif
                            </div>

                            @if ($customer->address)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3">{{ $customer->address }}</p>
                            @endif
                        </div>
                        <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                            <flux:button href="{{ route('sales.customer-statement', ['customerId' => $customer->id]) }}" size="sm" variant="outline" icon="document-text" wire:navigate class="flex-1 sm:flex-none sm:w-auto">
                                <span class="sm:inline">{{ __('Statement') }}</span>
                            </flux:button>
                            <flux:button wire:click="edit({{ $customer->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                <span class="sm:inline">{{ __('Edit') }}</span>
                            </flux:button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <div class="mx-auto w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No customers yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Add your first customer to start managing sales.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                        {{ __('Create First Customer') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($customers->hasPages())
            <div class="mt-6">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</section>
