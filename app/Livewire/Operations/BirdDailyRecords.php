<?php

namespace App\Livewire\Operations;

use App\Models\BirdDailyRecord;
use App\Models\Coop;
use App\Models\Farm;
use App\Models\Flock;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class BirdDailyRecords extends Component
{
    use WithPagination;

    // Form fields
    public $selectedFarm = '';
    public $selectedCoop = '';
    public $flock_id = '';
    public $date = '';
    public $age_in_weeks = 0;
    public $opening_stock = 0;
    public $mortality = 0;
    public $culled = 0;
    public $sold = 0;
    public $closing_stock = 0;
    public $mortality_reason = '';
    public $notes = '';
    public $editingId = null;
    public $showForm = false;

    // Filter properties
    public $filterFarm = '';
    public $filterCoop = '';
    public $filterFlock = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $filterMortality = 'all'; // all, with_mortality, no_mortality
    public $filterSearch = '';

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    // Filter update handlers
    public function updatedFilterFarm()
    {
        $this->filterCoop = '';
        $this->filterFlock = '';
        $this->resetPage();
    }

    public function updatedFilterCoop()
    {
        $this->filterFlock = '';
        $this->resetPage();
    }

    public function updatedFilterFlock()
    {
        $this->resetPage();
    }

    public function updatedFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatedFilterDateTo()
    {
        $this->resetPage();
    }

    public function updatedFilterMortality()
    {
        $this->resetPage();
    }

    public function updatedFilterSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->filterFarm = '';
        $this->filterCoop = '';
        $this->filterFlock = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->filterMortality = 'all';
        $this->filterSearch = '';
        $this->resetPage();
    }

    public function setQuickDate($period)
    {
        $today = now();

        switch ($period) {
            case 'today':
                $this->filterDateFrom = $today->format('Y-m-d');
                $this->filterDateTo = $today->format('Y-m-d');
                break;
            case 'yesterday':
                $yesterday = $today->copy()->subDay();
                $this->filterDateFrom = $yesterday->format('Y-m-d');
                $this->filterDateTo = $yesterday->format('Y-m-d');
                break;
            case 'this_week':
                $this->filterDateFrom = $today->copy()->startOfWeek()->format('Y-m-d');
                $this->filterDateTo = $today->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'this_month':
                $this->filterDateFrom = $today->copy()->startOfMonth()->format('Y-m-d');
                $this->filterDateTo = $today->copy()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_7_days':
                $this->filterDateFrom = $today->copy()->subDays(6)->format('Y-m-d');
                $this->filterDateTo = $today->format('Y-m-d');
                break;
            case 'last_30_days':
                $this->filterDateFrom = $today->copy()->subDays(29)->format('Y-m-d');
                $this->filterDateTo = $today->format('Y-m-d');
                break;
        }

        $this->resetPage();
    }

    public function updatedSelectedFarm()
    {
        $this->selectedCoop = '';
        $this->flock_id = '';
        $this->resetRecordFields();
    }

    public function updatedSelectedCoop()
    {
        $this->flock_id = '';
        $this->resetRecordFields();
    }

    public function updatedFlockId()
    {
        if ($this->flock_id && $this->date) {
            $this->calculateAgeInWeeks();
            $this->autoFillOpeningStock();
        }
    }

    public function updatedDate()
    {
        if ($this->flock_id && $this->date) {
            $this->calculateAgeInWeeks();
            $this->autoFillOpeningStock();
        }
    }

    public function updatedMortality()
    {
        $this->calculateClosingStock();
    }

    public function updatedCulled()
    {
        $this->calculateClosingStock();
    }

    public function updatedSold()
    {
        $this->calculateClosingStock();
    }

    public function updatedOpeningStock()
    {
        $this->calculateClosingStock();
    }

    private function resetRecordFields()
    {
        $this->age_in_weeks = 0;
        $this->opening_stock = 0;
        $this->mortality = 0;
        $this->culled = 0;
        $this->sold = 0;
        $this->closing_stock = 0;
    }

    private function calculateAgeInWeeks()
    {
        if (!$this->flock_id || !$this->date) {
            return;
        }

        $flock = Flock::find($this->flock_id);
        if ($flock) {
            $this->age_in_weeks = $flock->ageInWeeks(Carbon::parse($this->date));
        }
    }

    private function autoFillOpeningStock()
    {
        if (!$this->flock_id || !$this->date) {
            return;
        }

        // Find the previous day's record
        $previousRecord = BirdDailyRecord::where('flock_id', $this->flock_id)
            ->where('date', '<', $this->date)
            ->orderBy('date', 'desc')
            ->first();

        if ($previousRecord) {
            $this->opening_stock = $previousRecord->closing_stock;
        } else {
            // If no previous record, use flock's initial quantity
            $flock = Flock::find($this->flock_id);
            if ($flock) {
                $this->opening_stock = $flock->initial_quantity;
            }
        }

        $this->calculateClosingStock();
    }

    private function calculateClosingStock()
    {
        $this->closing_stock = (int)$this->opening_stock - (int)$this->mortality - (int)$this->culled - (int)$this->sold;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'flock_id' => 'required|exists:flocks,id',
            'date' => 'required|date|before_or_equal:today',
            'age_in_weeks' => 'required|integer|min:0',
            'opening_stock' => 'required|integer|min:0',
            'mortality' => 'required|integer|min:0|lte:opening_stock',
            'culled' => 'required|integer|min:0',
            'sold' => 'required|integer|min:0',
            'closing_stock' => 'required|integer|min:0',
            'mortality_reason' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Additional validation: total reductions shouldn't exceed opening stock
        if (($validated['mortality'] + $validated['culled'] + $validated['sold']) > $validated['opening_stock']) {
            $this->addError('mortality', 'Total mortality, culled, and sold cannot exceed opening stock.');
            return;
        }

        if ($this->editingId) {
            BirdDailyRecord::find($this->editingId)->update($validated);
            session()->flash('status', 'Daily record updated successfully.');
        } else {
            BirdDailyRecord::create($validated);
            session()->flash('status', 'Daily record created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $record = BirdDailyRecord::with('flock.coop.farm')->findOrFail($id);
        $this->editingId = $id;
        $this->selectedFarm = $record->flock->coop->farm_id;
        $this->selectedCoop = $record->flock->coop_id;
        $this->flock_id = $record->flock_id;
        $this->date = $record->date->format('Y-m-d');
        $this->age_in_weeks = $record->age_in_weeks;
        $this->opening_stock = $record->opening_stock;
        $this->mortality = $record->mortality;
        $this->culled = $record->culled;
        $this->sold = $record->sold;
        $this->closing_stock = $record->closing_stock;
        $this->mortality_reason = $record->mortality_reason ?? '';
        $this->notes = $record->notes ?? '';
        $this->showForm = true;
    }

    public function delete($id): void
    {
        BirdDailyRecord::findOrFail($id)->delete();
        session()->flash('status', 'Daily record deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['selectedFarm', 'selectedCoop', 'flock_id', 'age_in_weeks', 'opening_stock', 'mortality', 'culled', 'sold', 'closing_stock', 'mortality_reason', 'notes', 'editingId', 'showForm']);
        $this->date = now()->format('Y-m-d');
    }

    public function render()
    {
        // Coops and flocks for form cascading selects
        $coopsForSelectedFarm = $this->selectedFarm
            ? Coop::where('farm_id', $this->selectedFarm)->where('is_active', true)->orderBy('name')->get()
            : collect();

        $flocksForSelectedCoop = $this->selectedCoop
            ? Flock::where('coop_id', $this->selectedCoop)->where('status', 'active')->orderBy('batch_number')->get()
            : collect();

        // Coops and flocks for filter dropdowns
        $filterCoopsForFarm = $this->filterFarm
            ? Coop::where('farm_id', $this->filterFarm)->where('is_active', true)->orderBy('name')->get()
            : collect();

        $filterFlocksForCoop = $this->filterCoop
            ? Flock::where('coop_id', $this->filterCoop)->orderBy('batch_number')->get()
            : collect();

        // Build filtered query
        $query = BirdDailyRecord::with('flock.coop.farm');

        // Apply filters
        if ($this->filterFlock) {
            $query->where('flock_id', $this->filterFlock);
        } elseif ($this->filterCoop) {
            $query->whereHas('flock', function ($q) {
                $q->where('coop_id', $this->filterCoop);
            });
        } elseif ($this->filterFarm) {
            $query->whereHas('flock.coop', function ($q) {
                $q->where('farm_id', $this->filterFarm);
            });
        }

        if ($this->filterDateFrom) {
            $query->where('date', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->where('date', '<=', $this->filterDateTo);
        }

        if ($this->filterMortality === 'with_mortality') {
            $query->where('mortality', '>', 0);
        } elseif ($this->filterMortality === 'no_mortality') {
            $query->where('mortality', '=', 0);
        }

        if ($this->filterSearch) {
            $query->where(function ($q) {
                $q->where('notes', 'like', '%' . $this->filterSearch . '%')
                    ->orWhere('mortality_reason', 'like', '%' . $this->filterSearch . '%');
            });
        }

        // Get filtered results with pagination
        $records = $query->latest('date')->paginate(25);

        // Calculate summary statistics based on current filters
        $statsQuery = BirdDailyRecord::query();

        // Apply same filters to stats
        if ($this->filterFlock) {
            $statsQuery->where('flock_id', $this->filterFlock);
        } elseif ($this->filterCoop) {
            $statsQuery->whereHas('flock', function ($q) {
                $q->where('coop_id', $this->filterCoop);
            });
        } elseif ($this->filterFarm) {
            $statsQuery->whereHas('flock.coop', function ($q) {
                $q->where('farm_id', $this->filterFarm);
            });
        }

        if ($this->filterDateFrom) {
            $statsQuery->where('date', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $statsQuery->where('date', '<=', $this->filterDateTo);
        }

        // Calculate statistics
        $totalOpeningStock = $statsQuery->sum('opening_stock');
        $totalMortality = $statsQuery->sum('mortality');
        $totalMortalityPercentage = $totalOpeningStock > 0
            ? ($totalMortality / $totalOpeningStock) * 100
            : 0;
        $avgMortalityPercentage = $totalOpeningStock > 0
            ? ($totalMortality / $totalOpeningStock) * 100
            : 0;

        $stats = [
            'total_records' => $statsQuery->count(),
            'total_mortality' => $totalMortality,
            'total_mortality_percentage' => $totalMortalityPercentage,
            'total_culled' => $statsQuery->sum('culled'),
            'total_sold' => $statsQuery->sum('sold'),
            'avg_mortality' => $statsQuery->avg('mortality'),
            'avg_mortality_percentage' => $avgMortalityPercentage,
        ];

        return view('livewire.operations.bird-daily-records', [
            'records' => $records,
            'stats' => $stats,
            'farms' => Farm::where('is_active', true)->orderBy('name')->get(),
            'coopsForSelectedFarm' => $coopsForSelectedFarm,
            'flocksForSelectedCoop' => $flocksForSelectedCoop,
            'filterCoopsForFarm' => $filterCoopsForFarm,
            'filterFlocksForCoop' => $filterFlocksForCoop,
        ]);
    }
}
