<?php

namespace App\Livewire\Operations;

use App\Models\EggDailyProduction;
use App\Models\EggDispatch;
use App\Models\Farm;
use Livewire\Component;
use Livewire\WithPagination;

class EggDispatches extends Component
{
    use WithPagination;

    public $farm_id = '';
    public $date = '';
    public $quantity = 0;
    public $dispatch_type = 'owner_consumption';
    public $dispatch_reason = '';
    public $recipient_name = '';
    public $notes = '';
    public $editingId = null;
    public $showForm = false;

    // For filtering
    public $filterFarm = '';

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedFarmId()
    {
        $this->quantity = 0;
        $this->validateAvailableStock();
    }

    public function updatedQuantity()
    {
        $this->validateAvailableStock();
    }

    private function validateAvailableStock()
    {
        if (!$this->farm_id || !$this->quantity) {
            return;
        }

        $farm = Farm::find($this->farm_id);
        if (!$farm) {
            return;
        }

        // Calculate available stock for the farm
        $availableStock = $farm->availableEggStock();

        // If editing, add back the current dispatch quantity
        if ($this->editingId) {
            $currentDispatch = EggDispatch::find($this->editingId);
            if ($currentDispatch && $currentDispatch->farm_id == $this->farm_id) {
                $availableStock += $currentDispatch->quantity;
            }
        }

        if ($this->quantity > $availableStock) {
            $this->quantity = $availableStock;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'farm_id' => 'required|exists:farms,id',
            'date' => 'required|date|before_or_equal:today',
            'quantity' => 'required|integer|min:1',
            'dispatch_type' => 'required|in:owner_consumption,sale',
            'dispatch_reason' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Additional validation: check available stock
        $farm = Farm::find($this->farm_id);
        $availableStock = $farm->availableEggStock();

        if ($this->editingId) {
            $currentDispatch = EggDispatch::find($this->editingId);
            if ($currentDispatch && $currentDispatch->farm_id == $this->farm_id) {
                $availableStock += $currentDispatch->quantity;
            }
        }

        if ($validated['quantity'] > $availableStock) {
            $this->addError('quantity', 'Quantity exceeds available stock (' . number_format($availableStock) . ' eggs).');
            return;
        }

        if ($this->editingId) {
            EggDispatch::find($this->editingId)->update($validated);
            session()->flash('status', 'Egg dispatch record updated successfully.');
        } else {
            EggDispatch::create($validated);
            session()->flash('status', 'Egg dispatch record created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $dispatch = EggDispatch::findOrFail($id);
        $this->editingId = $id;
        $this->farm_id = $dispatch->farm_id;
        $this->date = $dispatch->date->format('Y-m-d');
        $this->quantity = $dispatch->quantity;
        $this->dispatch_type = $dispatch->dispatch_type;
        $this->dispatch_reason = $dispatch->dispatch_reason ?? '';
        $this->recipient_name = $dispatch->recipient_name;
        $this->notes = $dispatch->notes ?? '';
        $this->showForm = true;
    }

    public function delete($id): void
    {
        EggDispatch::findOrFail($id)->delete();
        session()->flash('status', 'Egg dispatch record deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['farm_id', 'quantity', 'dispatch_type', 'dispatch_reason', 'recipient_name', 'notes', 'editingId', 'showForm']);
        $this->date = now()->format('Y-m-d');
        $this->dispatch_type = 'owner_consumption';
    }

    public function render()
    {
        $dispatchesQuery = EggDispatch::with(['farm'])
            ->latest('date');

        if ($this->filterFarm) {
            $dispatchesQuery->where('farm_id', $this->filterFarm);
        }

        // Get farms with available egg stock
        $farms = Farm::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($farm) {
                $farm->available_stock = $farm->availableEggStock();
                $farm->available_trays = $farm->availableEggTrays();
                return $farm;
            });

        return view('livewire.operations.egg-dispatches', [
            'dispatches' => $dispatchesQuery->paginate(15),
            'farms' => $farms,
        ]);
    }
}
