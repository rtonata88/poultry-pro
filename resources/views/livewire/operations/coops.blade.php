<section class="w-full">
    <x-operations.layout :heading="__('Coops')" :subheading="__('Manage coops and houses within your farms')">
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
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('All Coops') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Manage your coops and houses') }}</p>
                </div>
                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Coop') }}
                    </flux:button>
                @endif
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Coop') : __('New Coop') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4 sm:space-y-5">
                        <flux:select wire:model="farm_id" :label="__('Farm')" required>
                            <option value="">{{ __('Select a farm') }}</option>
                            @foreach ($farms as $farm)
                                <option value="{{ $farm->id }}">{{ $farm->name }} ({{ $farm->code }})</option>
                            @endforeach
                        </flux:select>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:input wire:model="name" :label="__('Coop Name')" type="text" required placeholder="e.g., House A, Coop 1" />
                            <flux:input wire:model="code" :label="__('Coop Code')" type="text" required placeholder="e.g., C-001" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:input wire:model="capacity" :label="__('Capacity')" type="number" min="0" placeholder="Maximum bird capacity" />

                            <flux:select wire:model="type" :label="__('Coop Type')" required>
                                <option value="layers">{{ __('Layers') }}</option>
                                <option value="broilers">{{ __('Broilers') }}</option>
                                <option value="breeders">{{ __('Breeders') }}</option>
                            </flux:select>
                        </div>

                        <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Additional information about this coop..." />

                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <flux:checkbox wire:model="is_active" :label="__('Active')" />
                            <flux:text class="text-xs text-zinc-500">{{ __('Inactive coops will not appear in operational forms') }}</flux:text>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Coop') : __('Create Coop') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Coops List -->
            <div class="space-y-3">
                @forelse ($coops as $coop)
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                    <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $coop->name }}</h4>
                                    <flux:badge size="sm" variant="outline">{{ $coop->code }}</flux:badge>
                                    <flux:badge size="sm" variant="info">{{ ucfirst($coop->type) }}</flux:badge>
                                    <flux:badge :variant="$coop->is_active ? 'success' : 'danger'" size="sm">
                                        {{ $coop->is_active ? __('Active') : __('Inactive') }}
                                    </flux:badge>
                                </div>

                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2 flex items-center gap-1">
                                    <span>🏠</span>
                                    <span>{{ $coop->farm->name }} ({{ $coop->farm->code }})</span>
                                </p>

                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mt-3 text-xs text-zinc-500">
                                    @if ($coop->capacity)
                                        <span class="flex items-center gap-1">
                                            <span>📊</span>
                                            <span>Capacity: {{ number_format($coop->capacity) }} birds</span>
                                        </span>
                                    @endif
                                    <span>{{ __('Created') }} {{ $coop->created_at->diffForHumans() }}</span>
                                </div>

                                @if ($coop->notes)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3 italic">{{ $coop->notes }}</p>
                                @endif
                            </div>
                            <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                                <flux:button wire:click="edit({{ $coop->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                    <span class="sm:inline">{{ __('Edit') }}</span>
                                </flux:button>
                                <flux:button
                                    wire:click="delete({{ $coop->id }})"
                                    wire:confirm="Are you sure you want to delete this coop? This will also delete all associated flocks."
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No coops yet') }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Create your first coop to start organizing your flocks.') }}</p>
                        <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                            {{ __('Create First Coop') }}
                        </flux:button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($coops->hasPages())
                <div class="mt-6">
                    {{ $coops->links() }}
                </div>
            @endif
        </div>
    </x-operations.layout>
</section>
