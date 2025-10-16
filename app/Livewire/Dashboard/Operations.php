<?php

namespace App\Livewire\Dashboard;

use App\Models\BirdDailyRecord;
use App\Models\EggDailyProduction;
use App\Models\Farm;
use App\Models\FeedInventory;
use App\Models\FeedDailyUsage;
use App\Models\Flock;
use Livewire\Component;

class Operations extends Component
{
    public $filterFlock = '';

    public function mount()
    {
        // Default to the first active flock
        $firstActiveFlock = Flock::where('status', 'active')->orderBy('batch_number')->first();
        if ($firstActiveFlock) {
            $this->filterFlock = $firstActiveFlock->id;
        }
    }

    public function render()
    {
        // Active Birds - sum of latest closing stock
        $activeFlocks = Flock::where('status', 'active')->get();
        $activeBirds = 0;
        foreach ($activeFlocks as $flock) {
            $latestRecord = BirdDailyRecord::where('flock_id', $flock->id)
                ->latest('date')
                ->first();
            if ($latestRecord) {
                $activeBirds += $latestRecord->closing_stock;
            }
        }

        // Egg Production today
        $eggsToday = EggDailyProduction::whereDate('date', today())
            ->sum('eggs_produced');

        // Production Rate today (eggs produced / active birds * 100)
        $productionRateToday = $activeBirds > 0 ? ($eggsToday / $activeBirds) * 100 : 0;

        // Egg Production this week
        $eggsThisWeek = EggDailyProduction::whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('eggs_produced');

        // Average daily production this week
        $daysInWeek = now()->startOfWeek()->diffInDays(now()) + 1;
        $avgDailyProduction = $daysInWeek > 0 ? $eggsThisWeek / $daysInWeek : 0;

        // Average production rate this week
        $avgProductionRateWeek = $activeBirds > 0 && $daysInWeek > 0
            ? ($avgDailyProduction / $activeBirds) * 100
            : 0;

        // Mortality this month
        $mortalityThisMonth = BirdDailyRecord::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('mortality');

        // Calculate mortality rate
        $openingStockThisMonth = BirdDailyRecord::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->first();
        $mortalityRate = $openingStockThisMonth && $openingStockThisMonth->opening_stock > 0
            ? ($mortalityThisMonth / $openingStockThisMonth->opening_stock) * 100
            : 0;

        // Current Egg Stock (calculate from all farms)
        $currentEggStock = Farm::where('is_active', true)
            ->get()
            ->sum(function($farm) {
                return $farm->availableEggStock();
            });

        // Egg Stock per Farm
        $eggStockPerFarm = Farm::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($farm) {
                $stock = $farm->availableEggStock();
                return [
                    'name' => $farm->name,
                    'stock' => $stock,
                    'trays' => $stock > 0 ? floor($stock / 30) : 0,
                ];
            });

        // Feed Stock
        $totalFeedStock = FeedInventory::sum('current_stock');
        $lowStockItems = FeedInventory::whereRaw('current_stock <= reorder_level')->get();

        // Egg production trend per farm (last 14 days)
        $startDate = now()->subDays(13);
        $endDate = now();

        // Get all active farms
        $activeFarms = Farm::where('is_active', true)->orderBy('name')->get();

        // Prepare chart data
        $chartLabels = [];
        $chartDatasets = [];

        // Define colors for each farm
        $colors = [
            ['rgb(59, 130, 246)', 'rgba(59, 130, 246, 0.1)'], // blue
            ['rgb(16, 185, 129)', 'rgba(16, 185, 129, 0.1)'], // green
            ['rgb(251, 146, 60)', 'rgba(251, 146, 60, 0.1)'], // orange
            ['rgb(168, 85, 247)', 'rgba(168, 85, 247, 0.1)'], // purple
            ['rgb(236, 72, 153)', 'rgba(236, 72, 153, 0.1)'], // pink
        ];

        // Generate date labels
        $dates = [];
        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
            $chartLabels[] = $date->format('M d');
        }

        // Build dataset for each farm
        $colorIndex = 0;
        foreach ($activeFarms as $farm) {
            $farmFlocks = Flock::whereHas('coop', function($query) use ($farm) {
                $query->where('farm_id', $farm->id);
            })->pluck('id');

            if ($farmFlocks->isEmpty()) {
                continue;
            }

            $farmData = [];
            $farmProductionRates = [];
            foreach ($dates as $date) {
                $dailyTotal = EggDailyProduction::whereIn('flock_id', $farmFlocks)
                    ->whereDate('date', $date)
                    ->sum('eggs_produced');
                $farmData[] = $dailyTotal;

                // Calculate production rate for this date
                $totalBirds = 0;
                foreach ($farmFlocks as $flockId) {
                    $birdRecord = \App\Models\BirdDailyRecord::where('flock_id', $flockId)
                        ->where('date', '<=', $date)
                        ->orderBy('date', 'desc')
                        ->first();
                    if ($birdRecord) {
                        $totalBirds += $birdRecord->closing_stock;
                    }
                }
                $productionRate = $totalBirds > 0 ? ($dailyTotal / $totalBirds) * 100 : 0;
                $farmProductionRates[] = round($productionRate, 1);
            }

            $color = $colors[$colorIndex % count($colors)];
            $chartDatasets[] = [
                'label' => $farm->name,
                'data' => $farmData,
                'productionRates' => $farmProductionRates,
                'borderColor' => $color[0],
                'backgroundColor' => $color[1],
                'tension' => 0.4,
            ];
            $colorIndex++;
        }

        $eggProductionTrend = [
            'labels' => $chartLabels,
            'datasets' => $chartDatasets,
        ];

        // Farm performance
        $farmPerformance = [];
        $activeFarms = Farm::where('is_active', true)->orderBy('name')->get();

        foreach ($activeFarms as $farm) {
            // Get all active flocks for this farm
            $farmFlocks = Flock::where('status', 'active')
                ->whereHas('coop', function($query) use ($farm) {
                    $query->where('farm_id', $farm->id);
                })
                ->pluck('id');

            if ($farmFlocks->isEmpty()) {
                continue;
            }

            // Total current birds for this farm
            $totalBirds = 0;
            foreach ($farmFlocks as $flockId) {
                $latestRecord = BirdDailyRecord::where('flock_id', $flockId)
                    ->latest('date')
                    ->first();
                if ($latestRecord) {
                    $totalBirds += $latestRecord->closing_stock;
                }
            }

            // Monthly mortality for this farm
            $monthlyMortality = BirdDailyRecord::whereIn('flock_id', $farmFlocks)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('mortality');

            // Monthly eggs for this farm
            $monthlyEggs = EggDailyProduction::whereIn('flock_id', $farmFlocks)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('eggs_produced');

            // Count actual days with production records this month
            $daysWithRecords = EggDailyProduction::whereIn('flock_id', $farmFlocks)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->distinct('date')
                ->count('date');

            $avgDailyEggs = $daysWithRecords > 0 ? $monthlyEggs / $daysWithRecords : 0;

            $productionRate = $totalBirds > 0 && $daysWithRecords > 0
                ? ($avgDailyEggs / $totalBirds) * 100
                : 0;

            $farmPerformance[] = [
                'name' => $farm->name,
                'current_stock' => $totalBirds,
                'mortality' => $monthlyMortality,
                'avg_daily_eggs' => $avgDailyEggs,
                'production_rate' => $productionRate,
            ];
        }

        // Daily Operations Report Table
        // Get records for the last 30 days
        $reportStartDate = now()->subDays(29);
        $reportEndDate = now();
        $reportRecords = [];

        // Get all active flocks for the filter dropdown
        $allFlocks = Flock::where('status', 'active')
            ->with(['coop.farm'])
            ->orderBy('batch_number')
            ->get();

        // Generate dates for the report
        for ($date = $reportStartDate->copy(); $date <= $reportEndDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');

            // Bird records for the day (filtered by flock if selected)
            $birdQuery = BirdDailyRecord::whereDate('date', $dateStr);
            if ($this->filterFlock) {
                $birdQuery->where('flock_id', $this->filterFlock);
            }
            $birdRecords = $birdQuery->get();
            $birdData = [
                'opening_stock' => $birdRecords->sum('opening_stock'),
                'mortality' => $birdRecords->sum('mortality'),
                'closing_stock' => $birdRecords->sum('closing_stock'),
            ];

            // Egg production for the day (filtered by flock if selected)
            $eggQuery = EggDailyProduction::whereDate('date', $dateStr);
            if ($this->filterFlock) {
                $eggQuery->where('flock_id', $this->filterFlock);
            }
            $eggRecords = $eggQuery->get();
            $eggData = [
                'opening_stock' => $eggRecords->sum('opening_stock'),
                'eggs_produced' => $eggRecords->sum('eggs_produced'),
                'damaged' => $eggRecords->sum('damaged'),
                'closing_stock' => $eggRecords->sum('closing_stock'),
            ];

            // Calculate dispatched (from egg dispatches)
            // Note: Dispatches are by farm, not flock, so we filter by farm if flock is selected
            $dispatchQuery = \App\Models\EggDispatch::whereDate('date', $dateStr);
            if ($this->filterFlock) {
                $selectedFlock = Flock::find($this->filterFlock);
                if ($selectedFlock) {
                    $dispatchQuery->where('farm_id', $selectedFlock->coop->farm_id);
                }
            }
            $dispatched = $dispatchQuery->sum('quantity');
            $eggData['dispatched'] = $dispatched;

            // Calculate production rate
            $eggData['production_rate'] = $birdData['closing_stock'] > 0
                ? round(($eggData['eggs_produced'] / $birdData['closing_stock']) * 100, 0)
                : 0;

            // Feed usage for the day (filtered by flock if selected)
            $feedQuery = FeedDailyUsage::whereDate('date', $dateStr);
            if ($this->filterFlock) {
                $feedQuery->where('flock_id', $this->filterFlock);
            }
            $feedRecords = $feedQuery->get();
            $feedData = [
                'opening_stock' => $feedRecords->sum('opening_stock'),
                'received' => $feedRecords->sum('received'),
                'used' => $feedRecords->sum('quantity_used'),
                'closing_stock' => $feedRecords->sum('closing_stock'),
            ];

            // Calculate age in weeks based on flock placement date and starting age
            $ageInWeeks = 0;
            if ($this->filterFlock) {
                $selectedFlock = Flock::find($this->filterFlock);
                if ($selectedFlock && $selectedFlock->placement_date) {
                    $weeksSincePlacement = (int) floor($selectedFlock->placement_date->diffInDays($date) / 7);
                    $ageInWeeks = $selectedFlock->age_in_weeks + $weeksSincePlacement;
                }
            } else {
                // If showing all flocks, calculate average age or use the oldest flock
                $activeFlocks = Flock::where('status', 'active')
                    ->whereNotNull('placement_date')
                    ->orderBy('placement_date')
                    ->first();
                if ($activeFlocks && $activeFlocks->placement_date) {
                    $weeksSincePlacement = (int) floor($activeFlocks->placement_date->diffInDays($date) / 7);
                    $ageInWeeks = $activeFlocks->age_in_weeks + $weeksSincePlacement;
                }
            }

            $reportRecords[] = [
                'date' => $date->format('d-M-y'),
                'week' => $ageInWeeks,
                'bird' => $birdData,
                'egg' => $eggData,
                'feed' => $feedData,
            ];
        }

        // Reverse to show most recent first
        $reportRecords = array_reverse($reportRecords);

        return view('livewire.dashboard.operations', [
            'activeBirds' => $activeBirds,
            'eggsToday' => $eggsToday,
            'productionRateToday' => $productionRateToday,
            'currentEggStock' => $currentEggStock,
            'eggStockPerFarm' => $eggStockPerFarm,
            'eggsThisWeek' => $eggsThisWeek,
            'avgDailyProduction' => $avgDailyProduction,
            'avgProductionRateWeek' => $avgProductionRateWeek,
            'mortalityThisMonth' => $mortalityThisMonth,
            'mortalityRate' => $mortalityRate,
            'totalFeedStock' => $totalFeedStock,
            'lowStockItems' => $lowStockItems,
            'eggProductionTrend' => $eggProductionTrend,
            'farmPerformance' => $farmPerformance,
            'reportRecords' => $reportRecords,
            'allFlocks' => $allFlocks,
        ])->layout('components.layouts.app', ['title' => __('Operations Dashboard')]);
    }
}
