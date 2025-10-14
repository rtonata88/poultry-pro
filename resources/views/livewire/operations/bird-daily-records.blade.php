<section class="w-full">
    <x-operations.layout :heading="__('Daily Bird Records')" :subheading="__('Track daily bird activities, mortality, and stock levels')">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <flux:text class="text-green-700 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </flux:text>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Summary Statistics -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4">
                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">Total Records</div>
                    <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ number_format($stats['total_records']) }}</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800 p-4">
                    <div class="text-xs text-red-600 dark:text-red-400 mb-1">Total Mortality</div>
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ number_format($stats['total_mortality']) }}
                        <span class="text-sm font-normal">({{ number_format($stats['total_mortality_percentage'], 2) }}%)</span>
                    </div>
                </div>
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-800 p-4">
                    <div class="text-xs text-orange-600 dark:text-orange-400 mb-1">Total Culled</div>
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($stats['total_culled']) }}</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 p-4">
                    <div class="text-xs text-green-600 dark:text-green-400 mb-1">Total Sold</div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['total_sold']) }}</div>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-4">
                    <div class="text-xs text-blue-600 dark:text-blue-400 mb-1">Avg Mortality</div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($stats['avg_mortality'], 1) }}
                        <span class="text-sm font-normal">({{ number_format($stats['avg_mortality_percentage'], 2) }}%)</span>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-zinc-50 dark:bg-zinc-900/50 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Filters</h4>
                    @if($filterFarm || $filterCoop || $filterFlock || $filterDateFrom || $filterDateTo || $filterMortality !== 'all' || $filterSearch)
                        <flux:button wire:click="clearFilters" size="sm" variant="ghost">Clear All</flux:button>
                    @endif
                </div>

                <!-- Quick Date Buttons -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <flux:button wire:click="setQuickDate('today')" size="sm" variant="outline">Today</flux:button>
                    <flux:button wire:click="setQuickDate('yesterday')" size="sm" variant="outline">Yesterday</flux:button>
                    <flux:button wire:click="setQuickDate('this_week')" size="sm" variant="outline">This Week</flux:button>
                    <flux:button wire:click="setQuickDate('this_month')" size="sm" variant="outline">This Month</flux:button>
                    <flux:button wire:click="setQuickDate('last_7_days')" size="sm" variant="outline">Last 7 Days</flux:button>
                    <flux:button wire:click="setQuickDate('last_30_days')" size="sm" variant="outline">Last 30 Days</flux:button>
                </div>

                <!-- Filter Controls -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <flux:select wire:model.live="filterFarm" label="Farm" size="sm">
                        <option value="">All Farms</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="filterCoop" label="Coop" size="sm" :disabled="!$filterFarm">
                        <option value="">{{ $filterFarm ? 'All Coops' : 'Select Farm First' }}</option>
                        @foreach ($filterCoopsForFarm as $coop)
                            <option value="{{ $coop->id }}">{{ $coop->name }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="filterFlock" label="Flock" size="sm" :disabled="!$filterCoop">
                        <option value="">{{ $filterCoop ? 'All Flocks' : 'Select Coop First' }}</option>
                        @foreach ($filterFlocksForCoop as $flock)
                            <option value="{{ $flock->id }}">{{ $flock->batch_number }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="filterMortality" label="Mortality" size="sm">
                        <option value="all">All Records</option>
                        <option value="with_mortality">With Mortality</option>
                        <option value="no_mortality">No Mortality</option>
                    </flux:select>

                    <flux:input wire:model.live.debounce.500ms="filterDateFrom" type="date" label="Date From" size="sm" />
                    <flux:input wire:model.live.debounce.500ms="filterDateTo" type="date" label="Date To" size="sm" />

                    <flux:input wire:model.live.debounce.500ms="filterSearch" type="text" label="Search" placeholder="Search notes, reason..." size="sm" class="sm:col-span-2" />
                </div>
            </div>

            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Daily Records') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                        @if($records->total() > 0)
                            Showing {{ $records->firstItem() }} to {{ $records->lastItem() }} of {{ $records->total() }} records
                        @else
                            {{ __('Record daily bird activities and stock movements') }}
                        @endif
                    </p>
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
                        {{ $editingId ? __('Edit Daily Record') : __('New Daily Record') }}
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

                            <flux:select wire:model.live="selectedCoop" :label="__('Coop')" required :disabled="!$selectedFarm">
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
                                <flux:input :label="__('Age (weeks)')" type="number" value="{{ $age_in_weeks }}" disabled />
                                <p class="text-xs text-zinc-500 mt-1">{{ __('Auto-calculated from placement date') }}</p>
                            </div>

                            <div class="sm:col-span-2 lg:col-span-1">
                                <flux:input wire:model.live="opening_stock" :label="__('Opening Stock')" type="number" min="0" required />
                                <p class="text-xs text-zinc-500 mt-1">{{ __('Auto-filled from previous day') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-5">
                            <flux:input wire:model.live="mortality" :label="__('Mortality')" type="number" min="0" value="0" />
                            <flux:input wire:model.live="culled" :label="__('Culled')" type="number" min="0" value="0" />
                            <flux:input wire:model.live="sold" :label="__('Sold')" type="number" min="0" value="0" />
                        </div>

                        <div>
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                    <span class="text-sm font-medium text-blue-900 dark:text-blue-100">{{ __('Closing Stock') }}</span>
                                    <span class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ number_format((float)$closing_stock) }}</span>
                                </div>
                                <p class="text-xs text-blue-700 dark:text-blue-300 mt-2">
                                    {{ __('Opening') }} ({{ number_format((float)$opening_stock) }}) - {{ __('Mortality') }} ({{ number_format((float)$mortality) }}) - {{ __('Culled') }} ({{ number_format((float)$culled) }}) - {{ __('Sold') }} ({{ number_format((float)$sold) }})
                                </p>
                            </div>
                        </div>

                        @if ($mortality > 0)
                            <flux:input wire:model="mortality_reason" :label="__('Mortality Reason')" type="text" placeholder="e.g., Disease, Heat stress, Unknown" />
                        @endif

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
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Week') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Location') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Opening') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Mortality') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Culled') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Sold') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Closing') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($records as $record)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ $record->date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ $record->age_in_weeks }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                            <div class="max-w-xs">
                                                <div class="font-medium">{{ $record->flock->batch_number }}</div>
                                                <div class="text-xs text-zinc-500">{{ $record->flock->coop->name }} • {{ $record->flock->coop->farm->name }}</div>
                                                @if ($record->mortality > 0 && $record->mortality_reason)
                                                    <div class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $record->mortality_reason }}</div>
                                                @endif
                                                @if ($record->notes)
                                                    <div class="text-xs text-zinc-500 mt-1 italic">{{ $record->notes }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ number_format($record->opening_stock) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            <span class="{{ $record->mortality > 0 ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-zinc-900 dark:text-zinc-100' }}">
                                                {{ number_format($record->mortality) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            <span class="{{ $record->culled > 0 ? 'text-orange-600 dark:text-orange-400 font-semibold' : 'text-zinc-900 dark:text-zinc-100' }}">
                                                {{ number_format($record->culled) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            <span class="{{ $record->sold > 0 ? 'text-green-600 dark:text-green-400 font-semibold' : 'text-zinc-900 dark:text-zinc-100' }}">
                                                {{ number_format($record->sold) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-blue-600 dark:text-blue-400 font-semibold whitespace-nowrap">
                                            {{ number_format($record->closing_stock) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <flux:button wire:click="edit({{ $record->id }})" size="sm" variant="ghost" icon="pencil" />
                                                <flux:button
                                                    wire:click="delete({{ $record->id }})"
                                                    wire:confirm="Are you sure you want to delete this daily record?"
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
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                        <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $record->date->format('M d, Y') }}
                                        </h4>
                                        <flux:badge size="sm" variant="outline">Week {{ $record->age_in_weeks }}</flux:badge>
                                        @if ($record->mortality > 0)
                                            <flux:badge size="sm" variant="danger">{{ $record->mortality }} mortality</flux:badge>
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
                                            <span class="text-zinc-500 block">Mortality</span>
                                            <p class="font-semibold text-red-600 dark:text-red-400 mt-1">{{ number_format($record->mortality) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 block">Culled</span>
                                            <p class="font-semibold text-orange-600 dark:text-orange-400 mt-1">{{ number_format($record->culled) }}</p>
                                        </div>
                                        <div>
                                            <span class="text-zinc-500 block">Sold</span>
                                            <p class="font-semibold text-green-600 dark:text-green-400 mt-1">{{ number_format($record->sold) }}</p>
                                        </div>
                                        <div class="col-span-2 sm:col-span-3 lg:col-span-1">
                                            <span class="text-zinc-500 block">Closing</span>
                                            <p class="font-semibold text-blue-600 dark:text-blue-400 mt-1">{{ number_format($record->closing_stock) }}</p>
                                        </div>
                                    </div>

                                    @if ($record->mortality > 0 && $record->mortality_reason)
                                        <p class="text-xs text-red-600 dark:text-red-400 mt-3 flex items-center gap-1">
                                            <span>⚠️</span>
                                            <span>{{ __('Reason') }}: {{ $record->mortality_reason }}</span>
                                        </p>
                                    @endif

                                    @if ($record->notes)
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3 italic">{{ $record->notes }}</p>
                                    @endif
                                </div>
                                <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                                    <flux:button wire:click="edit({{ $record->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                        <span class="sm:inline">{{ __('Edit') }}</span>
                                    </flux:button>
                                    <flux:button
                                        wire:click="delete({{ $record->id }})"
                                        wire:confirm="Are you sure you want to delete this daily record?"
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        class="flex-1 sm:flex-none sm:w-auto">
                                        <span class="sm:inline">{{ __('Delete') }}</span>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No records yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Start tracking daily bird activities for your flocks.') }}</p>
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
