<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Key Metrics Cards -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-4">
            <!-- Active Birds Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Active Birds') }}</span>
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($activeBirds) }}
                </div>
                <div class="mt-2 text-sm text-zinc-500">
                    {{ __('Total stock across flocks') }}
                </div>
            </div>

            <!-- Egg Production Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Eggs Today') }}</span>
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($eggsToday) }}
                </div>
                <div class="mt-2 text-sm text-zinc-500">
                    {{ __('Weekly avg') }}: {{ number_format($avgDailyProduction) }}
                </div>
            </div>

            <!-- Mortality Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Mortality (This Month)') }}</span>
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($mortalityThisMonth) }}
                </div>
                <div class="mt-2 text-sm text-zinc-500">
                    {{ __('Rate') }}: {{ number_format($mortalityRate, 2) }}%
                </div>
            </div>

            <!-- Feed Stock Card -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Feed Stock') }}</span>
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">
                    {{ number_format($totalFeedStock, 2) }}
                </div>
                @if($lowStockItems->count() > 0)
                    <div class="mt-2 text-sm text-red-600 dark:text-red-400">
                        {{ $lowStockItems->count() }} {{ __('low stock alert(s)') }}
                    </div>
                @else
                    <div class="mt-2 text-sm text-zinc-500">
                        {{ __('All stock levels good') }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Bottom Section -->
        <div class="grid gap-4 md:grid-cols-2">
            <!-- Farm Performance -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ __('Farm Performance (This Month)') }}</h3>
                <div class="space-y-3">
                    @forelse($farmPerformance as $farm)
                        <div class="border-b border-zinc-100 dark:border-zinc-700 last:border-0 pb-3 last:pb-0">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $farm['name'] }}</span>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ number_format($farm['current_stock']) }} {{ __('birds') }}</span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div>
                                    <span class="text-zinc-500">{{ __('Mortality') }}:</span>
                                    <span class="font-medium text-red-600 dark:text-red-400">{{ $farm['mortality'] }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500">{{ __('Avg Eggs/Day') }}:</span>
                                    <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($farm['avg_daily_eggs']) }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500">{{ __('Prod. Rate') }}:</span>
                                    <span class="font-medium text-blue-600 dark:text-blue-400">{{ number_format($farm['production_rate'], 1) }}%</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500 text-center py-4">{{ __('No active farms') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Egg Stock per Farm -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ __('Egg Stock per Farm') }}</h3>
                <div class="space-y-3">
                    @forelse($eggStockPerFarm as $farm)
                        <div class="flex items-center justify-between py-2 border-b border-zinc-100 dark:border-zinc-700 last:border-0">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $farm['name'] }}
                                </p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    {{ number_format($farm['stock']) }} {{ __('eggs') }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ number_format($farm['trays']) }} {{ __('trays') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-zinc-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="text-sm text-zinc-500">{{ __('No active farms') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Egg Production Trend Chart -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">{{ __('Egg Production Trend (Last 14 Days)') }}</h3>
            <div class="relative" style="height: 350px;">
                <canvas id="eggProductionChart" wire:ignore></canvas>
            </div>
        </div>

        <!-- Daily Operations Report Table -->
        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden">
            <div class="p-6 border-b border-zinc-200 dark:border-zinc-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Daily Operations Report (Last 30 Days)') }}</h3>
                    <div class="flex items-center gap-3">
                        <flux:select wire:model.live="filterFlock" placeholder="{{ __('Filter by flock') }}" class="w-full sm:w-64">
                            <option value="">{{ __('All Flocks') }}</option>
                            @foreach ($allFlocks as $flock)
                                <option value="{{ $flock->id }}">
                                    {{ $flock->batch_number }} - {{ $flock->coop->farm->name }} ({{ $flock->coop->name }})
                                </option>
                            @endforeach
                        </flux:select>
                        @if($filterFlock)
                            <flux:button wire:click="$set('filterFlock', '')" size="sm" variant="ghost">
                                {{ __('Clear') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 dark:border-zinc-700">
                            <!-- Date Column -->
                            <th class="px-3 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 sticky left-0 z-10" rowspan="2">{{ __('DATE') }}</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700" rowspan="2">{{ __('AGE (weeks)') }}</th>

                            <!-- Birds Section -->
                            <th class="px-3 py-2 text-center text-xs font-bold text-zinc-900 dark:text-zinc-100 bg-yellow-200 dark:bg-yellow-900 border-r border-zinc-200 dark:border-zinc-700" colspan="3">{{ __('BIRDS') }}</th>

                            <!-- Egg Production Section -->
                            <th class="px-3 py-2 text-center text-xs font-bold text-zinc-900 dark:text-zinc-100 bg-green-200 dark:bg-green-900 border-r border-zinc-200 dark:border-zinc-700" colspan="6">{{ __('EGG PRODUCTION') }}</th>

                            <!-- Feed Section -->
                            <th class="px-3 py-2 text-center text-xs font-bold text-zinc-900 dark:text-zinc-100 bg-zinc-200 dark:bg-zinc-700" colspan="4">{{ __('FEED (Bags)') }}</th>
                        </tr>
                        <tr class="border-b-2 border-zinc-300 dark:border-zinc-600">
                            <!-- Birds Sub-headers -->
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-yellow-100 dark:bg-yellow-950 border-r border-zinc-200 dark:border-zinc-700">{{ __('OPENING STOCK') }}</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-yellow-100 dark:bg-yellow-950 border-r border-zinc-200 dark:border-zinc-700">{{ __('MORTALITY') }}</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-yellow-100 dark:bg-yellow-950 border-r border-zinc-200 dark:border-zinc-700">{{ __('CLOSING STOCK') }}</th>

                            <!-- Egg Production Sub-headers -->
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-green-100 dark:bg-green-950 border-r border-zinc-200 dark:border-zinc-700">{{ __('OPENING STOCK') }}</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-green-100 dark:bg-green-950 border-r border-zinc-200 dark:border-zinc-700">{{ __('EGGS PRODUCED') }}</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-green-100 dark:bg-green-950 border-r border-zinc-200 dark:border-zinc-700">{{ __('DAMAGED') }}</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-green-100 dark:bg-green-950 border-r border-zinc-200 dark:border-zinc-700">{{ __('DISPATCHED') }}</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-green-100 dark:bg-green-950 border-r border-zinc-200 dark:border-zinc-700">{{ __('CLOSING STOCK') }}</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-green-100 dark:bg-green-950 border-r border-zinc-200 dark:border-zinc-700">{{ __('%') }}</th>

                            <!-- Feed Sub-headers -->
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border-r border-zinc-200 dark:border-zinc-700">{{ __('OPENING STOCK') }}</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border-r border-zinc-200 dark:border-zinc-700">{{ __('RECEIVED') }}</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 border-r border-zinc-200 dark:border-zinc-700">{{ __('USED') }}</th>
                            <th class="px-3 py-2 text-center text-xs font-semibold text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800">{{ __('CLOSING STOCK') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach($reportRecords as $record)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                <!-- Date -->
                                <td class="px-3 py-2 text-xs font-medium text-zinc-900 dark:text-zinc-100 whitespace-nowrap bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700 sticky left-0">
                                    {{ $record['date'] }}
                                </td>
                                <!-- Week -->
                                <td class="px-3 py-2 text-xs text-center text-zinc-900 dark:text-zinc-100 border-r border-zinc-200 dark:border-zinc-700">
                                    {{ $record['week'] }}
                                </td>

                                <!-- Birds Data -->
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-yellow-50 dark:bg-yellow-950/20 border-r border-zinc-200 dark:border-zinc-700">
                                    {{ number_format($record['bird']['opening_stock']) }}
                                </td>
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-yellow-50 dark:bg-yellow-950/20 border-r border-zinc-200 dark:border-zinc-700 {{ $record['bird']['mortality'] > 0 ? 'font-semibold text-red-600 dark:text-red-400' : '' }}">
                                    {{ number_format($record['bird']['mortality']) }}
                                </td>
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-yellow-50 dark:bg-yellow-950/20 border-r border-zinc-200 dark:border-zinc-700">
                                    {{ number_format($record['bird']['closing_stock']) }}
                                </td>

                                <!-- Egg Production Data -->
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-green-50 dark:bg-green-950/20 border-r border-zinc-200 dark:border-zinc-700">
                                    {{ number_format($record['egg']['opening_stock']) }}
                                </td>
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-green-50 dark:bg-green-950/20 border-r border-zinc-200 dark:border-zinc-700">
                                    {{ number_format($record['egg']['eggs_produced']) }}
                                </td>
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-green-50 dark:bg-green-950/20 border-r border-zinc-200 dark:border-zinc-700 {{ $record['egg']['damaged'] > 0 ? 'font-semibold bg-red-100 dark:bg-red-950/40' : '' }}">
                                    {{ number_format($record['egg']['damaged']) }}
                                </td>
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-green-50 dark:bg-green-950/20 border-r border-zinc-200 dark:border-zinc-700 {{ $record['egg']['dispatched'] > 0 ? 'font-semibold bg-red-100 dark:bg-red-950/40' : '' }}">
                                    {{ number_format($record['egg']['dispatched']) }}
                                </td>
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-green-50 dark:bg-green-950/20 border-r border-zinc-200 dark:border-zinc-700">
                                    {{ number_format($record['egg']['closing_stock']) }}
                                </td>
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-green-50 dark:bg-green-950/20 border-r border-zinc-200 dark:border-zinc-700">
                                    {{ $record['egg']['production_rate'] }}%
                                </td>

                                <!-- Feed Data -->
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-zinc-50 dark:bg-zinc-800/50 border-r border-zinc-200 dark:border-zinc-700">
                                    {{ number_format($record['feed']['opening_stock']) }}
                                </td>
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-zinc-50 dark:bg-zinc-800/50 border-r border-zinc-200 dark:border-zinc-700">
                                    {{ number_format($record['feed']['received']) }}
                                </td>
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-zinc-50 dark:bg-zinc-800/50 border-r border-zinc-200 dark:border-zinc-700">
                                    {{ number_format($record['feed']['used']) }}
                                </td>
                                <td class="px-3 py-2 text-xs text-right text-zinc-900 dark:text-zinc-100 bg-zinc-50 dark:bg-zinc-800/50">
                                    {{ number_format($record['feed']['closing_stock']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
</div>

@vite('resources/js/chart-init.js')

<script>
    let chartInitialized = false;
    const chartData = @json($eggProductionTrend);

    function initChart() {
        if (chartInitialized) return;

        const canvas = document.getElementById('eggProductionChart');
        if (!canvas) {
            // Canvas not ready yet, try again
            setTimeout(initChart, 100);
            return;
        }

        if (typeof window.initEggProductionChart === 'function') {
            window.initEggProductionChart(chartData);
            chartInitialized = true;
        } else {
            // Function not loaded yet, try again
            setTimeout(initChart, 100);
        }
    }

    // Try multiple initialization points
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChart);
    } else {
        initChart();
    }

    // Also try when Livewire finishes loading
    document.addEventListener('livewire:navigated', function() {
        chartInitialized = false;
        setTimeout(initChart, 100);
    });
</script>
