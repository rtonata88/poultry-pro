<section class="w-full">
    @include('partials.feed-heading')

    <x-feed-management.layout :heading="__('Stock Levels')" :subheading="__('Monitor and manage feed inventory levels')">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <flux:text class="text-green-700 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </flux:text>
            </div>
        @endif

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            @if (!$showForm)
                <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                    {{ __('Add Stock Level') }}
                </flux:button>
            @endif
        </div>

        @if ($showForm)
            <form wire:submit="save" class="p-6 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-4">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $editingId ? __('Edit Stock Level') : __('New Stock Level') }}
                </h4>

                <flux:select wire:model="feed_type_id" :label="__('Feed Type')" required>
                    <option value="">{{ __('Select feed type') }}</option>
                    @foreach ($feedTypes as $feedType)
                        <option value="{{ $feedType->id }}">{{ $feedType->name }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="farm_id" :label="__('Farm')" placeholder="All farms">
                    <option value="">{{ __('All farms') }}</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="current_stock" :label="__('Current Stock')" type="number" step="0.01" required />

                <flux:input wire:model="reorder_level" :label="__('Reorder Level')" type="number" step="0.01" required />

                <div class="flex items-center gap-2 pt-2">
                    <flux:button type="submit" variant="primary">
                        {{ $editingId ? __('Update') : __('Create') }}
                    </flux:button>
                    <flux:button wire:click="cancel" variant="ghost" type="button">
                        {{ __('Cancel') }}
                    </flux:button>
                </div>
            </form>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($inventories as $inventory)
                <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 {{ $inventory->isLowStock() ? 'border-red-500 dark:border-red-700' : '' }}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-zinc-900 dark:text-zinc-100 text-lg">{{ $inventory->feedType->name }}</h4>
                            <p class="text-sm text-zinc-500 mt-1">{{ $inventory->farm ? $inventory->farm->name : __('All farms') }}</p>

                            <div class="mt-4 space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Current Stock') }}:</span>
                                    <span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($inventory->current_stock, 2) }} {{ strtoupper($inventory->feedType->unit) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Reorder Level') }}:</span>
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ number_format($inventory->reorder_level, 2) }} {{ strtoupper($inventory->feedType->unit) }}</span>
                                </div>
                            </div>

                            @if ($inventory->isLowStock())
                                <div class="mt-3 flex items-center gap-2 text-sm text-red-600 dark:text-red-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="font-medium">{{ __('Low stock') }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 ml-3">
                            <flux:button wire:click="edit({{ $inventory->id }})" size="xs" variant="ghost" icon="pencil" />
                            <flux:button wire:click="delete({{ $inventory->id }})" wire:confirm="Delete this stock level?" size="xs" variant="danger" icon="trash" />
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 p-12 text-center bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <p class="text-zinc-500">{{ __('No stock levels yet. Create one to get started.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="mt-4">
                        {{ __('Add Stock Level') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        @if ($inventories->hasPages())
            <div class="mt-6">
                {{ $inventories->links() }}
            </div>
        @endif
        </div>
    </x-feed-management.layout>
</section>
