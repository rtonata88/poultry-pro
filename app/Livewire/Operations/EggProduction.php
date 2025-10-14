<?php

namespace App\Livewire\Operations;

use App\Models\Coop;
use App\Models\EggDailyProduction;
use App\Models\EggDispatch;
use App\Models\Farm;
use App\Models\Flock;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class EggProduction extends Component
{
    use WithPagination;

    public $selectedFarm = '';
    public $selectedCoop = '';
    public $flock_id = '';
    public $date = '';
    public $opening_stock = 0;
    public $eggs_produced = 0;
    public $damaged = 0;
    public $closing_stock = 0;
    public $notes = '';
    public $editingId = null;
    public $showForm = false;

    // For filtering metrics and records
    public $filterFarm = '';

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedSelectedFarm()
    {
        $this->selectedCoop = '';
        $this->flock_id = '';
        $this->resetProductionFields();
    }

    public function updatedSelectedCoop()
    {
        $this->flock_id = '';
        $this->resetProductionFields();
    }

    public function updatedFlockId()
    {
        if ($this->flock_id && $this->date) {
            $this->autoFillOpeningStock();
        }
    }

    public function updatedDate()
    {
        if ($this->flock_id && $this->date) {
            $this->autoFillOpeningStock();
        }
    }

    public function updatedEggsProduced()
    {
        $this->calculateClosingStock();
    }

    public function updatedDamaged()
    {
        $this->calculateClosingStock();
    }

    public function updatedOpeningStock()
    {
        $this->calculateClosingStock();
    }

    private function resetProductionFields()
    {
        $this->opening_stock = 0;
        $this->eggs_produced = 0;
        $this->damaged = 0;
        $this->closing_stock = 0;
    }

    private function autoFillOpeningStock()
    {
        if (!$this->flock_id || !$this->date) {
            return;
        }

        // Find the previous day's record
        $previousRecord = EggDailyProduction::where('flock_id', $this->flock_id)
            ->where('date', '<', $this->date)
            ->orderBy('date', 'desc')
            ->first();

        if ($previousRecord) {
            $this->opening_stock = $previousRecord->closing_stock;
        } else {
            // First record, opening stock is 0
            $this->opening_stock = 0;
        }

        $this->calculateClosingStock();
    }

    private function calculateClosingStock()
    {
        // Closing stock for production = opening + produced - damaged
        // Note: Dispatches are now tracked separately at farm level
        $this->closing_stock = (int)$this->opening_stock + (int)$this->eggs_produced - (int)$this->damaged;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'flock_id' => 'required|exists:flocks,id',
            'date' => 'required|date|before_or_equal:today',
            'opening_stock' => 'required|integer|min:0',
            'eggs_produced' => 'required|integer|min:0',
            'damaged' => 'required|integer|min:0',
            'closing_stock' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($this->editingId) {
            EggDailyProduction::find($this->editingId)->update($validated);
            session()->flash('status', 'Egg production record updated successfully.');
        } else {
            EggDailyProduction::create($validated);
            session()->flash('status', 'Egg production record created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $record = EggDailyProduction::with('flock.coop.farm')->findOrFail($id);
        $this->editingId = $id;
        $this->selectedFarm = $record->flock->coop->farm_id;
        $this->selectedCoop = $record->flock->coop_id;
        $this->flock_id = $record->flock_id;
        $this->date = $record->date->format('Y-m-d');
        $this->opening_stock = $record->opening_stock;
        $this->eggs_produced = $record->eggs_produced;
        $this->damaged = $record->damaged;
        $this->closing_stock = $record->closing_stock;
        $this->notes = $record->notes ?? '';
        $this->showForm = true;
    }

    public function delete($id): void
    {
        EggDailyProduction::findOrFail($id)->delete();
        session()->flash('status', 'Egg production record deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['selectedFarm', 'selectedCoop', 'flock_id', 'opening_stock', 'eggs_produced', 'damaged', 'closing_stock', 'notes', 'editingId', 'showForm']);
        $this->date = now()->format('Y-m-d');
    }

    public function render()
    {
        $coopsForSelectedFarm = $this->selectedFarm
            ? Coop::where('farm_id', $this->selectedFarm)->where('is_active', true)->orderBy('name')->get()
            : collect();

        // Only show layer flocks for egg production
        $flocksForSelectedCoop = $this->selectedCoop
            ? Flock::where('coop_id', $this->selectedCoop)
                ->where('status', 'active')
                ->whereHas('coop', function($query) {
                    $query->where('type', 'layers');
                })
                ->orderBy('batch_number')
                ->get()
            : collect();

        // Build records query with optional farm filter
        $recordsQuery = EggDailyProduction::with(['flock.coop.farm'])
            ->latest('date');

        if ($this->filterFarm) {
            $recordsQuery->whereHas('flock.coop', function($query) {
                $query->where('farm_id', $this->filterFarm);
            });
        }

        // Get all records for metrics calculation (not paginated)
        $allRecords = EggDailyProduction::with(['flock.coop.farm']);

        if ($this->filterFarm) {
            $allRecords->whereHas('flock.coop', function($query) {
                $query->where('farm_id', $this->filterFarm);
            });
        }

        $recordsForMetrics = $allRecords->get();

        // Calculate metrics
        $totalProduced = $recordsForMetrics->sum('eggs_produced');
        $totalDamaged = $recordsForMetrics->sum('damaged');

        // Get total dispatched from the farm(s) being filtered
        $farmIds = $recordsForMetrics->map(function($record) {
            return $record->flock->coop->farm_id;
        })->unique();

        $totalDispatched = EggDispatch::whereIn('farm_id', $farmIds)->sum('quantity');

        // Current Stock: Get farm-level available stock
        $farms = Farm::whereIn('id', $farmIds)->get();
        $currentStock = $farms->sum(function($farm) {
            return $farm->availableEggStock();
        });
        $currentTrays = $currentStock > 0 ? floor($currentStock / 30) : 0;

        // Production Rate: Average daily production rate (Total Eggs / Total Active Birds) × 100
        // Calculate total active birds from all production records
        $totalActiveBirds = 0;

        foreach ($recordsForMetrics as $record) {
            // Get the most recent bird record up to this production date
            $birdRecord = \App\Models\BirdDailyRecord::where('flock_id', $record->flock_id)
                ->where('date', '<=', $record->date)
                ->orderBy('date', 'desc')
                ->first();

            if ($birdRecord) {
                // Use closing stock as active birds (current active birds)
                $totalActiveBirds += $birdRecord->closing_stock;
            }
        }

        $productionRate = $totalActiveBirds > 0 ? ($totalProduced / $totalActiveBirds) * 100 : 0;

        // Damage Rate: Percentage
        $damageRate = $totalProduced > 0 ? ($totalDamaged / $totalProduced) * 100 : 0;

        // Dispatch Rate: Percentage
        $dispatchRate = $totalProduced > 0 ? ($totalDispatched / $totalProduced) * 100 : 0;

        $metrics = [
            'currentStock' => $currentStock,
            'currentTrays' => $currentTrays,
            'productionRate' => $productionRate,
            'damageRate' => $damageRate,
            'dispatchRate' => $dispatchRate,
        ];

        return view('livewire.operations.egg-production', [
            'records' => $recordsQuery->paginate(15),
            'farms' => Farm::where('is_active', true)->orderBy('name')->get(),
            'coopsForSelectedFarm' => $coopsForSelectedFarm,
            'flocksForSelectedCoop' => $flocksForSelectedCoop,
            'metrics' => $metrics,
        ]);
    }
}
