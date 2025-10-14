<?php

namespace App\Livewire\Operations;

use App\Models\Coop;
use App\Models\Farm;
use App\Models\Flock;
use Livewire\Component;
use Livewire\WithPagination;

class Flocks extends Component
{
    use WithPagination;

    public $selectedFarm = '';
    public $filterFarm = ''; // For filtering metrics and list
    public $coop_id = '';
    public $batch_number = '';
    public $breed = '';
    public $placement_date = '';
    public $initial_quantity = '';
    public $source = '';
    public $status = 'active';
    public $expected_end_date = '';
    public $notes = '';
    public $editingId = null;
    public $showForm = false;

    public function updatedSelectedFarm()
    {
        $this->coop_id = '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'coop_id' => 'required|exists:coops,id',
            'batch_number' => 'required|string|max:255|unique:flocks,batch_number,' . $this->editingId,
            'breed' => 'nullable|string|max:255',
            'placement_date' => 'required|date',
            'initial_quantity' => 'required|integer|min:1',
            'source' => 'nullable|string|max:255',
            'status' => 'required|in:active,completed,transferred',
            'expected_end_date' => 'nullable|date|after:placement_date',
            'notes' => 'nullable|string',
        ]);

        if ($this->editingId) {
            Flock::find($this->editingId)->update($validated);
            session()->flash('status', 'Flock updated successfully.');
        } else {
            Flock::create($validated);
            session()->flash('status', 'Flock created successfully.');
        }

        $this->reset(['selectedFarm', 'coop_id', 'batch_number', 'breed', 'placement_date', 'initial_quantity', 'source', 'status', 'expected_end_date', 'notes', 'editingId', 'showForm']);
        $this->status = 'active';
    }

    public function edit($id): void
    {
        $flock = Flock::with('coop.farm')->findOrFail($id);
        $this->editingId = $id;
        $this->selectedFarm = $flock->coop->farm_id;
        $this->coop_id = $flock->coop_id;
        $this->batch_number = $flock->batch_number;
        $this->breed = $flock->breed ?? '';
        $this->placement_date = $flock->placement_date->format('Y-m-d');
        $this->initial_quantity = $flock->initial_quantity;
        $this->source = $flock->source ?? '';
        $this->status = $flock->status;
        $this->expected_end_date = $flock->expected_end_date?->format('Y-m-d') ?? '';
        $this->notes = $flock->notes ?? '';
        $this->showForm = true;
    }

    public function delete($id): void
    {
        Flock::findOrFail($id)->delete();
        session()->flash('status', 'Flock deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['selectedFarm', 'coop_id', 'batch_number', 'breed', 'placement_date', 'initial_quantity', 'source', 'status', 'expected_end_date', 'notes', 'editingId', 'showForm']);
        $this->status = 'active';
    }

    public function render()
    {
        $coopsForSelectedFarm = $this->selectedFarm
            ? Coop::where('farm_id', $this->selectedFarm)->where('is_active', true)->orderBy('name')->get()
            : collect();

        // Build query for active flocks with optional farm filter
        $activeFlockQuery = Flock::where('status', 'active')
            ->with(['birdDailyRecords', 'coop.farm']);

        if ($this->filterFarm) {
            $activeFlockQuery->whereHas('coop', function($query) {
                $query->where('farm_id', $this->filterFarm);
            });
        }

        $activeFlocks = $activeFlockQuery->get();

        $totalInitialBirds = $activeFlocks->sum('initial_quantity');
        $totalMortality = $activeFlocks->sum(function($flock) {
            return $flock->birdDailyRecords->sum('mortality');
        });
        $totalCulled = $activeFlocks->sum(function($flock) {
            return $flock->birdDailyRecords->sum('culled');
        });
        $totalSold = $activeFlocks->sum(function($flock) {
            return $flock->birdDailyRecords->sum('sold');
        });

        $currentBirds = $totalInitialBirds - $totalMortality - $totalCulled - $totalSold;
        $mortalityRate = $totalInitialBirds > 0 ? ($totalMortality / $totalInitialBirds) * 100 : 0;

        // Build flocks list query with filter
        $flocksQuery = Flock::with('coop.farm')->latest();

        if ($this->filterFarm) {
            $flocksQuery->whereHas('coop', function($query) {
                $query->where('farm_id', $this->filterFarm);
            });
        }

        return view('livewire.operations.flocks', [
            'flocks' => $flocksQuery->paginate(10),
            'farms' => Farm::where('is_active', true)->orderBy('name')->get(),
            'coopsForSelectedFarm' => $coopsForSelectedFarm,
            'metrics' => [
                'totalInitialBirds' => $totalInitialBirds,
                'totalMortality' => $totalMortality,
                'currentBirds' => $currentBirds,
                'mortalityRate' => $mortalityRate,
            ],
        ]);
    }
}
