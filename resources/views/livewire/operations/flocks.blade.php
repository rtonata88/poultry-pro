<section class="w-full">
    <x-operations.layout :heading="__('Flocks')" :subheading="__('Manage your poultry flocks and batches')">
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
                <!-- Initial Birds -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Initial Birds') }}</p>
                            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100 mt-1">{{ number_format($metrics['totalInitialBirds']) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-500 mt-2">{{ __('Active flocks') }}</p>
                </div>

                <!-- Total Mortality -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Total Mortality') }}</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ number_format($metrics['totalMortality']) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 dark:bg-red-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-500 mt-2">{{ __('All time') }}</p>
                </div>

                <!-- Current Birds -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Current Birds') }}</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ number_format($metrics['currentBirds']) }}</p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-500 mt-2">{{ __('Active stock') }}</p>
                </div>

                <!-- Mortality Rate -->
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Mortality Rate') }}</p>
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">{{ number_format($metrics['mortalityRate'], 2) }}%</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-xs text-zinc-500 mt-2">{{ __('Overall rate') }}</p>
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('All Flocks') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Track and manage your bird flocks') }}</p>
                </div>
                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Flock') }}
                    </flux:button>
                @endif
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Flock') : __('New Flock') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4 sm:space-y-5">
                        <!-- Cascading Farm and Coop Selection -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:select wire:model.live="selectedFarm" :label="__('Farm')" required>
                                <option value="">{{ __('Select a farm') }}</option>
                                @foreach ($farms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->name }} ({{ $farm->code }})</option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model="coop_id" :label="__('Coop')" required :disabled="!$selectedFarm">
                                <option value="">{{ $selectedFarm ? __('Select a coop') : __('Select farm first') }}</option>
                                @foreach ($coopsForSelectedFarm as $coop)
                                    <option value="{{ $coop->id }}">{{ $coop->name }} ({{ $coop->code }})</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:input wire:model="batch_number" :label="__('Batch Number')" type="text" required placeholder="e.g., BATCH-2025-001" />
                            <flux:input wire:model="breed" :label="__('Breed')" type="text" placeholder="e.g., Rhode Island Red" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
                            <flux:input wire:model="placement_date" :label="__('Placement Date')" type="date" required />
                            <flux:input wire:model="age_in_weeks" :label="__('Age in Weeks')" type="number" min="0" required placeholder="Age at placement" />
                            <flux:input wire:model="initial_quantity" :label="__('Initial Quantity')" type="number" min="1" required placeholder="Number of birds" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:input wire:model="source" :label="__('Source')" type="text" placeholder="Hatchery or supplier name" />

                            <flux:select wire:model="status" :label="__('Status')" required>
                                <option value="active">{{ __('Active') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                                <option value="transferred">{{ __('Transferred') }}</option>
                            </flux:select>
                        </div>

                        <flux:input wire:model="expected_end_date" :label="__('Expected End Date')" type="date" />

                        <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Additional information about this flock..." />

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Flock') : __('Create Flock') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Flocks List -->
            <div class="space-y-3">
                @forelse ($flocks as $flock)
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                    <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $flock->batch_number }}</h4>
                                    @if ($flock->breed)
                                        <flux:badge size="sm" variant="outline">{{ $flock->breed }}</flux:badge>
                                    @endif
                                    <flux:badge size="sm" :variant="$flock->status === 'active' ? 'success' : ($flock->status === 'completed' ? 'neutral' : 'warning')">
                                        {{ ucfirst($flock->status) }}
                                    </flux:badge>
                                </div>

                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2 flex items-center gap-1">
                                    <span>🏠</span>
                                    <span>{{ $flock->coop->farm->name }} → {{ $flock->coop->name }}</span>
                                </p>

                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:flex lg:items-center gap-2 sm:gap-4 mt-3 text-xs text-zinc-500">
                                    <span class="flex items-center gap-1">
                                        <span>📅</span>
                                        <span>Placed: {{ $flock->placement_date->format('M d, Y') }}</span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span>🐔</span>
                                        <span>{{ number_format($flock->initial_quantity) }} birds</span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <span>📊</span>
                                        <span>Age: {{ $flock->ageInWeeks() }} weeks</span>
                                    </span>
                                    @if ($flock->source)
                                        <span class="flex items-center gap-1">
                                            <span>🏢</span>
                                            <span>{{ $flock->source }}</span>
                                        </span>
                                    @endif
                                </div>

                                @if ($flock->notes)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3 italic">{{ $flock->notes }}</p>
                                @endif
                            </div>
                            <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                                <flux:button wire:click="edit({{ $flock->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                    <span class="sm:inline">{{ __('Edit') }}</span>
                                </flux:button>
                                <flux:button
                                    wire:click="delete({{ $flock->id }})"
                                    wire:confirm="Are you sure you want to delete this flock? This will also delete all daily records."
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No flocks yet') }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Create your first flock to start tracking bird activities.') }}</p>
                        <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                            {{ __('Create First Flock') }}
                        </flux:button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($flocks->hasPages())
                <div class="mt-6">
                    {{ $flocks->links() }}
                </div>
            @endif
        </div>
    </x-operations.layout>
</section>
