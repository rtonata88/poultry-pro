<section class="w-full">
    <x-operations.layout :heading="__('Egg Production')" :subheading="__('Track daily egg production and inventory')">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <flux:text class="text-green-700 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </flux:text>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Farm Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1">
                    <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Filter by Farm:') }}</span>
                    <flux:select wire:model.live="filterFarm" class="w-full sm:w-64">
                        <option value="">{{ __('All Farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                        @endforeach
                    </flux:select>
                </div>
                @if ($filterFarm)
                    <flux:button wire:click="$set('filterFarm', '')" size="sm" variant="ghost" class="w-full sm:w-auto">
                        {{ __('Clear Filter') }}
                    </flux:button>
                @endif
            </div>

            <!-- Metrics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Current Stock -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Current Stock') }}</p>
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($metrics['currentStock']) }}</p>
                            <p class="text-xs text-zinc-500 mt-1">({{ number_format($metrics['currentTrays']) }} trays)</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-500 mt-2">{{ __('Eggs in inventory') }}</p>
                </div>

                <!-- Production Rate -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Production Rate') }}</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($metrics['productionRate'], 2) }}%</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-500 mt-2">{{ __('Average per day') }}</p>
                </div>

                <!-- Damage Rate -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Damage Rate') }}</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($metrics['damageRate'], 2) }}%</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-500 mt-2">{{ __('Damaged eggs') }}</p>
                </div>

                <!-- Dispatch Rate -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Dispatch Rate') }}</p>
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ number_format($metrics['dispatchRate'], 2) }}%</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-500 mt-2">{{ __('Dispatched eggs') }}</p>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Production Records') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Record daily egg production for layer flocks') }}</p>
                </div>
                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Record') }}
                    </flux:button>
                @endif
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Production Record') : __('New Production Record') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4 sm:space-y-5">
                        <!-- Cascading Farm, Coop, and Flock Selection -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                            <flux:select wire:model.live="selectedFarm" :label="__('Farm')" required>
                                <option value="">{{ __('Select a farm') }}</option>
                                @foreach ($farms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model.live="selectedCoop" :label="__('Coop (Layers only)')" required :disabled="!$selectedFarm">
                                <option value="">{{ $selectedFarm ? __('Select a coop') : __('Select farm first') }}</option>
                                @foreach ($coopsForSelectedFarm as $coop)
                                    <option value="{{ $coop->id }}">{{ $coop->name }}</option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model.live="flock_id" :label="__('Flock')" required :disabled="!$selectedCoop" class="sm:col-span-2 lg:col-span-1">
                                <option value="">{{ $selectedCoop ? __('Select a flock') : __('Select coop first') }}</option>
                                @foreach ($flocksForSelectedCoop as $flock)
                                    <option value="{{ $flock->id }}">{{ $flock->batch_number }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                            <flux:input wire:model.live="date" :label="__('Date')" type="date" required max="{{ now()->format('Y-m-d') }}" />

                            <div>
                                <flux:input wire:model.live="opening_stock" :label="__('Opening Stock')" type="number" min="0" required />
                                <p class="text-xs text-zinc-500 mt-1">{{ __('Auto-filled from previous day') }}</p>
                            </div>

                            <flux:input wire:model.live="eggs_produced" :label="__('Eggs Produced')" type="number" min="0" required placeholder="Number of eggs collected" class="sm:col-span-2 lg:col-span-1" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:input wire:model.live="damaged" :label="__('Damaged/Broken')" type="number" min="0" value="0" />

                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <span class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ __('Closing Stock') }}</span>
                                    <span class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format((int)$closing_stock) }}</span>
                                </div>
                                <p class="text-xs text-blue-700 dark:text-blue-300 mt-2">
                                    {{ __('Opening') }} ({{ number_format((int)$opening_stock) }}) + {{ __('Produced') }} ({{ number_format((int)$eggs_produced) }}) - {{ __('Damaged') }} ({{ number_format((int)$damaged) }})
                                </p>
                            </div>
                        </div>

                        <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Any observations or additional information..." />

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Record') : __('Create Record') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Records Table (Desktop) -->
            @if ($records->count() > 0)
                <div class="hidden lg:block bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Location') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Opening') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Produced') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Damaged') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Closing') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Rate') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Available') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($records as $record)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ $record->date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                            <div class="max-w-xs">
                                                <div class="font-medium">{{ $record->flock->batch_number }}</div>
                                                <div class="text-xs text-zinc-500">{{ $record->flock->coop->name }} • {{ $record->flock->coop->farm->name }}</div>
                                                @if ($record->notes)
                                                    <div class="text-xs text-zinc-500 mt-1 italic">{{ $record->notes }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ number_format($record->opening_stock) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            <span class="text-green-600 dark:text-green-400 font-semibold">
                                                {{ number_format($record->eggs_produced) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            <span class="{{ $record->damaged > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-zinc-900 dark:text-zinc-100' }}">
                                                {{ number_format($record->damaged) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-blue-600 dark:text-blue-400 font-semibold whitespace-nowrap">
                                            {{ number_format($record->closing_stock) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-green-600 dark:text-green-400 font-semibold whitespace-nowrap">
                                            {{ number_format($record->productionRate(), 2) }}%
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            <div class="text-blue-600 dark:text-blue-400 font-semibold">{{ number_format($record->availableStock()) }}</div>
                                            <div class="text-xs text-zinc-500">({{ floor($record->availableStock() / 30) }} trays)</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <flux:button href="{{ route('operations.egg-dispatches', ['filterFarm' => $record->flock->coop->farm_id]) }}" size="sm" variant="outline" icon="truck" wire:navigate />
                                                <flux:button wire:click="edit({{ $record->id }})" size="sm" variant="ghost" icon="pencil" />
                                                <flux:button
                                                    wire:click="delete({{ $record->id }})"
                                                    wire:confirm="Are you sure you want to delete this production record?"
                                                    size="sm"
                                                    variant="danger"
                                                    icon="trash"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Records Cards (Mobile) -->
                <div class="lg:hidden space-y-3">
                    @foreach ($records as $record)
                        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                            <div class="flex flex-col gap-4">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                        <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $record->date->format('M d, Y') }}
                                        </h4>
                                        @if ($record->eggs_produced > 0)
                                            <flux:badge size="sm" variant="success">{{ number_format($record->eggs_produced) }} eggs</flux:badge>
                                        @endif
                                    </div>

                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2 flex items-center gap-1">
                                        <span>🏠</span>
                                        <span>{{ $record->flock->coop->farm->name }} → {{ $record->flock->coop->name }} → {{ $record->flock->batch_number }}</span>
                                    </p>

                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mt-3 text-xs">
                                        <div>
                                            <span class="text-zinc-500 block">Opening</span>
                                            <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ number_format($record->opening_stock) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 block">Produced</span>
                                            <p class="font-semibold text-green-600 dark:text-green-400 mt-1">{{ number_format($record->eggs_produced) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 block">Damaged</span>
                                            <p class="font-semibold text-red-600 dark:text-red-400 mt-1">{{ number_format($record->damaged) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 block">Closing</span>
                                            <p class="font-semibold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($record->closing_stock) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 block">Production Rate</span>
                                            <p class="font-semibold text-green-600 dark:text-green-400 mt-1">{{ number_format($record->productionRate(), 2) }}%</p>
                                        </div>
                                    </div>

                                    <div class="mt-3 text-xs bg-blue-50 dark:bg-blue-900/20 rounded p-2">
                                        <span class="text-blue-600 dark:text-blue-400 block">Farm Available Stock</span>
                                        <p class="font-semibold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($record->availableStock()) }} eggs ({{ floor($record->availableStock() / 30) }} trays)</p>
                                    </div>

                                    @if ($record->notes)
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3 italic">{{ $record->notes }}</p>
                                    @endif
                                </div>
                                <div class="flex flex-col sm:flex-row items-stretch gap-2 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                                    <flux:button href="{{ route('operations.egg-dispatches', ['filterFarm' => $record->flock->coop->farm_id]) }}" size="sm" variant="outline" icon="truck" wire:navigate class="flex-1 sm:flex-none sm:w-auto">
                                        <span>{{ __('View Farm Dispatches') }}</span>
                                    </flux:button>
                                    <flux:button wire:click="edit({{ $record->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                        <span>{{ __('Edit') }}</span>
                                    </flux:button>
                                    <flux:button
                                        wire:click="delete({{ $record->id }})"
                                        wire:confirm="Are you sure you want to delete this production record?"
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        class="flex-1 sm:flex-none sm:w-auto">
                                        <span>{{ __('Delete') }}</span>
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <div class="mx-auto w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No production records yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Start tracking daily egg production for your layer flocks.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                        {{ __('Create First Record') }}
                    </flux:button>
                </div>
            @endif

            <!-- Pagination -->
            @if ($records->hasPages())
                <div class="mt-6">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </x-operations.layout>
</section>
