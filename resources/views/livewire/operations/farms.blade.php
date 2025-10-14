<section class="w-full">
    <x-operations.layout :heading="__('Farms')" :subheading="__('Manage your farm locations and sites')">
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
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('All Farms') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Manage your farm locations and details') }}</p>
                </div>
                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Farm') }}
                    </flux:button>
                @endif
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Farm') : __('New Farm') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4 sm:space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:input wire:model="name" :label="__('Farm Name')" type="text" required placeholder="e.g., Main Farm, North Site" />
                            <flux:input wire:model="code" :label="__('Farm Code')" type="text" required placeholder="e.g., FARM-001" />
                        </div>

                        <flux:input wire:model="address" :label="__('Street Address')" type="text" placeholder="123 Main Street" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                            <flux:input wire:model="city" :label="__('City')" type="text" />
                            <flux:input wire:model="state" :label="__('State / Province')" type="text" />
                            <flux:input wire:model="zip_code" :label="__('Postal Code')" type="text" />
                        </div>

                        <flux:input wire:model="country" :label="__('Country')" type="text" />

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:input wire:model="manager_name" :label="__('Farm Manager')" type="text" placeholder="Manager name" />
                            <flux:input wire:model="phone" :label="__('Phone Number')" type="text" placeholder="+1 (555) 000-0000" />
                        </div>

                        <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Additional information about this farm..." />

                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <flux:checkbox wire:model="is_active" :label="__('Active')" />
                            <flux:text class="text-xs text-zinc-500">{{ __('Inactive farms will not appear in operational forms') }}</flux:text>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Farm') : __('Create Farm') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Farms List -->
            <div class="space-y-3">
                @forelse ($farms as $farm)
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                    <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $farm->name }}</h4>
                                    <flux:badge size="sm" variant="outline">{{ $farm->code }}</flux:badge>
                                    <flux:badge :variant="$farm->is_active ? 'success' : 'danger'" size="sm">
                                        {{ $farm->is_active ? __('Active') : __('Inactive') }}
                                    </flux:badge>
                                </div>

                                @if ($farm->address || $farm->city || $farm->state)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">
                                        {{ collect([$farm->address, $farm->city, $farm->state, $farm->zip_code, $farm->country])->filter()->implode(', ') }}
                                    </p>
                                @endif

                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mt-3 text-xs text-zinc-500">
                                    @if ($farm->manager_name)
                                        <span class="flex items-center gap-1">
                                            <span>👤</span>
                                            <span>{{ $farm->manager_name }}</span>
                                        </span>
                                    @endif
                                    @if ($farm->phone)
                                        <span class="flex items-center gap-1">
                                            <span>📞</span>
                                            <span>{{ $farm->phone }}</span>
                                        </span>
                                    @endif
                                    <span>{{ __('Created') }} {{ $farm->created_at->diffForHumans() }}</span>
                                </div>

                                @if ($farm->notes)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3 italic">{{ $farm->notes }}</p>
                                @endif
                            </div>
                            <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                                <flux:button wire:click="edit({{ $farm->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                    <span class="sm:inline">{{ __('Edit') }}</span>
                                </flux:button>
                                <flux:button
                                    wire:click="delete({{ $farm->id }})"
                                    wire:confirm="Are you sure you want to delete this farm? This will also delete all associated coops and flocks."
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No farms yet') }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Create your first farm to start managing your poultry operations.') }}</p>
                        <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                            {{ __('Create First Farm') }}
                        </flux:button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($farms->hasPages())
                <div class="mt-6">
                    {{ $farms->links() }}
                </div>
            @endif
        </div>
    </x-operations.layout>
</section>
