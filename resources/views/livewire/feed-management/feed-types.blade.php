<section class="w-full">
    @include('partials.feed-heading')

    <x-feed-management.layout :heading="__('Feed Types')" :subheading="__('Manage different types of feed products')">
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
                    {{ __('Add Feed Type') }}
                </flux:button>
            @endif
        </div>

        @if ($showForm)
            <form wire:submit="save" class="p-6 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-4">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $editingId ? __('Edit Feed Type') : __('New Feed Type') }}
                </h4>

                <flux:input wire:model="name" :label="__('Name')" required placeholder="e.g., Layer Mash, Chick Starter" />

                <flux:select wire:model="unit" :label="__('Unit')" required>
                    <option value="kg">{{ __('Kilograms (kg)') }}</option>
                    <option value="bags">{{ __('Bags') }}</option>
                </flux:select>

                <flux:textarea wire:model="description" :label="__('Description')" rows="3" placeholder="Optional description" />

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
            @forelse ($feedTypes as $feedType)
                <div class="p-5 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-semibold text-zinc-900 dark:text-zinc-100 text-lg">{{ $feedType->name }}</h4>
                            <p class="text-sm text-zinc-500 mt-1">Unit: {{ strtoupper($feedType->unit) }}</p>
                            @if ($feedType->description)
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3">{{ $feedType->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 ml-3">
                            <flux:button wire:click="edit({{ $feedType->id }})" size="xs" variant="ghost" icon="pencil" />
                            <flux:button wire:click="delete({{ $feedType->id }})" wire:confirm="Delete this feed type?" size="xs" variant="danger" icon="trash" />
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 p-12 text-center bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <p class="text-zinc-500">{{ __('No feed types yet. Create one to get started.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="mt-4">
                        {{ __('Add Feed Type') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        @if ($feedTypes->hasPages())
            <div class="mt-6">
                {{ $feedTypes->links() }}
            </div>
        @endif
        </div>
    </x-feed-management.layout>
</section>
