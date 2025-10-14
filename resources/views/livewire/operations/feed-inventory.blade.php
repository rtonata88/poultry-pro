<section class="w-full">
    <x-operations.layout :heading="__('Feed Inventory')" :subheading="__('Manage feed types, stock levels, and receipts')">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <flux:text class="text-green-700 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </flux:text>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Farm Filter -->
            <div class="flex items-center justify-between bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Filter by Farm:') }}</span>
                    <flux:select wire:model.live="filterFarm" class="w-64">
                        <option value="">{{ __('All Farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
                @if ($filterFarm)
                    <flux:button wire:click="$set('filterFarm', '')" size="sm" variant="ghost">
                        {{ __('Clear Filter') }}
                    </flux:button>
                @endif
            </div>

            <!-- Feed Types Section -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Feed Types') }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Manage different types of feed') }}</p>
                    </div>
                    @if (!$showFeedTypeForm)
                        <flux:button wire:click="$set('showFeedTypeForm', true)" variant="primary" size="sm" icon="plus">
                            {{ __('Add Feed Type') }}
                        </flux:button>
                    @endif
                </div>

                @if ($showFeedTypeForm)
                    <form wire:submit="saveFeedType" class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <flux:input wire:model="feedTypeName" :label="__('Name')" required placeholder="e.g., Layer Mash" />
                            <flux:select wire:model="feedTypeUnit" :label="__('Unit')" required>
                                <option value="kg">{{ __('Kilograms (kg)') }}</option>
                                <option value="bags">{{ __('Bags') }}</option>
                            </flux:select>
                            <flux:textarea wire:model="feedTypeDescription" :label="__('Description')" rows="1" placeholder="Optional description" />
                        </div>
                        <div class="flex items-center gap-2 mt-4">
                            <flux:button type="submit" variant="primary" size="sm">
                                {{ $editingFeedTypeId ? __('Update') : __('Create') }}
                            </flux:button>
                            <flux:button wire:click="cancelFeedType" variant="ghost" size="sm" type="button">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse ($feedTypes as $feedType)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $feedType->name }}</h4>
                                    <p class="text-xs text-zinc-500 mt-1">Unit: {{ strtoupper($feedType->unit) }}</p>
                                    @if ($feedType->description)
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2">{{ $feedType->description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1 ml-2">
                                    <flux:button wire:click="editFeedType({{ $feedType->id }})" size="xs" variant="ghost" icon="pencil" />
                                    <flux:button wire:click="deleteFeedType({{ $feedType->id }})" wire:confirm="Delete this feed type?" size="xs" variant="danger" icon="trash" />
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 col-span-3">{{ __('No feed types yet. Create one to get started.') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Current Inventory Section -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ __('Current Stock Levels') }}</h3>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Feed Type') }}</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Farm') }}</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Current Stock') }}</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Reorder Level') }}</th>
                                <th class="text-center py-3 px-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inventory as $item)
                                <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                    <td class="py-3 px-4 text-sm text-zinc-900 dark:text-zinc-100">{{ $item->feedType->name }}</td>
                                    <td class="py-3 px-4 text-sm text-zinc-600 dark:text-zinc-400">{{ $item->farm?->name ?? __('All Farms') }}</td>
                                    <td class="py-3 px-4 text-sm text-right text-zinc-900 dark:text-zinc-100 font-semibold">
                                        {{ number_format($item->current_stock, 2) }} {{ strtoupper($item->feedType->unit) }}
                                    </td>
                                    <td class="py-3 px-4 text-sm text-right text-zinc-600 dark:text-zinc-400">
                                        {{ number_format($item->reorder_level, 2) }} {{ strtoupper($item->feedType->unit) }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if ($item->isLowStock())
                                            <flux:badge size="sm" variant="danger">{{ __('Low Stock') }}</flux:badge>
                                        @else
                                            <flux:badge size="sm" variant="success">{{ __('OK') }}</flux:badge>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-sm text-zinc-500">
                                        {{ __('No inventory data. Record a receipt to start tracking stock.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Feed Receipts Section -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Feed Receipts') }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Record feed purchases and deliveries') }}</p>
                    </div>
                    @if (!$showReceiptForm)
                        <flux:button wire:click="$set('showReceiptForm', true)" variant="primary" icon="plus">
                            {{ __('Record Receipt') }}
                        </flux:button>
                    @endif
                </div>

                @if ($showReceiptForm)
                    <form wire:submit="saveReceipt" class="mb-6 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <flux:select wire:model="receiptFeedTypeId" :label="__('Feed Type')" required>
                                <option value="">{{ __('Select feed type') }}</option>
                                @foreach ($feedTypes as $feedType)
                                    <option value="{{ $feedType->id }}">{{ $feedType->name }}</option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model="receiptFarmId" :label="__('Farm')">
                                <option value="">{{ __('All Farms') }}</option>
                                @foreach ($farms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                                @endforeach
                            </flux:select>

                            <flux:input wire:model="receiptDate" :label="__('Date')" type="date" required max="{{ now()->format('Y-m-d') }}" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <flux:input wire:model.live="receiptQuantity" :label="__('Quantity')" type="number" step="0.01" min="0" required />
                            <flux:input wire:model="receiptSupplier" :label="__('Supplier')" type="text" placeholder="Supplier name" />
                            <flux:input wire:model.live="receiptUnitPrice" :label="__('Unit Price')" type="number" step="0.01" min="0" />
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                                <span class="text-xs text-blue-700 dark:text-blue-300">{{ __('Total Cost') }}</span>
                                <p class="text-lg font-bold text-blue-900 dark:text-blue-100 mt-1">{{ number_format((float)$receiptTotalCost, 2) }}</p>
                            </div>
                        </div>

                        <flux:textarea wire:model="receiptNotes" :label="__('Notes')" rows="2" placeholder="Optional notes" />

                        <div class="flex items-center gap-2 pt-2">
                            <flux:button type="submit" variant="primary">
                                {{ $editingReceiptId ? __('Update Receipt') : __('Record Receipt') }}
                            </flux:button>
                            <flux:button wire:click="cancelReceipt" variant="ghost" type="button">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                @endif

                <div class="space-y-3">
                    @forelse ($receipts as $receipt)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3">
                                        <h4 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $receipt->date->format('M d, Y') }}</h4>
                                        <flux:badge size="sm">{{ $receipt->feedType->name }}</flux:badge>
                                        @if ($receipt->farm)
                                            <flux:badge size="sm" variant="info">{{ $receipt->farm->name }}</flux:badge>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3 text-xs">
                                        <div>
                                            <span class="text-zinc-500">Quantity</span>
                                            <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ number_format($receipt->quantity, 2) }} {{ strtoupper($receipt->feedType->unit) }}</p>
                                        </div>
                                        @if ($receipt->supplier)
                                            <div>
                                                <span class="text-zinc-500">Supplier</span>
                                                <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $receipt->supplier }}</p>
                                            </div>
                                        @endif
                                        @if ($receipt->unit_price)
                                            <div>
                                                <span class="text-zinc-500">Unit Price</span>
                                                <p class="font-semibold text-green-600 dark:text-green-400">{{ number_format($receipt->unit_price, 2) }}</p>
                                            </div>
                                        @endif
                                        @if ($receipt->total_cost)
                                            <div>
                                                <span class="text-zinc-500">Total Cost</span>
                                                <p class="font-semibold text-blue-600 dark:text-blue-400">{{ number_format($receipt->total_cost, 2) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    @if ($receipt->notes)
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3 italic">{{ $receipt->notes }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <flux:button wire:click="editReceipt({{ $receipt->id }})" size="sm" variant="ghost" icon="pencil">
                                        {{ __('Edit') }}
                                    </flux:button>
                                    <flux:button wire:click="deleteReceipt({{ $receipt->id }})" wire:confirm="Delete this receipt?" size="sm" variant="danger" icon="trash">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm text-zinc-500">
                            {{ __('No receipts recorded yet.') }}
                        </div>
                    @endforelse
                </div>

                @if ($receipts->hasPages())
                    <div class="mt-6">
                        {{ $receipts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-operations.layout>
</section>
