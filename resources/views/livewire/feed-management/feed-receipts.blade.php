<section class="w-full">
    @include('partials.feed-heading')

    <x-feed-management.layout :heading="__('Feed Receipts')" :subheading="__('Track feed purchases and deliveries')">
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
                    {{ __('Add Receipt') }}
                </flux:button>
            @endif
        </div>

        @if ($showForm)
            <form wire:submit="save" class="p-6 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 space-y-4">
                <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ $editingId ? __('Edit Receipt') : __('New Receipt') }}
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

                <flux:input wire:model="date" :label="__('Date')" type="date" required />

                <flux:input wire:model="quantity" :label="__('Quantity')" type="number" step="0.01" required />

                <flux:input wire:model="supplier" :label="__('Supplier')" placeholder="Optional" />

                <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Optional notes" />

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

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-900/50 border-y border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Date') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Feed Type') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Farm') }}</th>
                        <th class="px-4 py-3 text-right font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Quantity') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Supplier') }}</th>
                        <th class="px-4 py-3 text-center font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($receipts as $receipt)
                        <tr class="bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $receipt->date->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ $receipt->feedType->name }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $receipt->farm ? $receipt->farm->name : __('All farms') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($receipt->quantity, 2) }} {{ strtoupper($receipt->feedType->unit) }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $receipt->supplier ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <flux:button wire:click="edit({{ $receipt->id }})" size="xs" variant="ghost" icon="pencil" />
                                    <flux:button wire:click="delete({{ $receipt->id }})" wire:confirm="Delete this receipt? This will also update the inventory." size="xs" variant="danger" icon="trash" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
                                {{ __('No receipts yet. Create one to get started.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($receipts->hasPages())
            <div class="mt-6">
                {{ $receipts->links() }}
            </div>
        @endif
        </div>
    </x-feed-management.layout>
</section>
