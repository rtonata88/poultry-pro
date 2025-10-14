<?php

namespace App\Livewire\Operations;

use App\Models\Coop;
use App\Models\Farm;
use App\Models\FeedDailyUsage;
use App\Models\FeedInventory;
use App\Models\FeedType;
use App\Models\Flock;
use Livewire\Component;
use Livewire\WithPagination;

class FeedUsage extends Component
{
    use WithPagination;

    public $selectedFarm = '';
    public $selectedCoop = '';
    public $flock_id = '';
    public $feed_type_id = '';
    public $date = '';
    public $quantity_used = 0;
    public $notes = '';
    public $editingId = null;
    public $showForm = false;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function updatedSelectedFarm()
    {
        $this->selectedCoop = '';
        $this->flock_id = '';
    }

    public function updatedSelectedCoop()
    {
        $this->flock_id = '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'flock_id' => 'required|exists:flocks,id',
            'feed_type_id' => 'required|exists:feed_types,id',
            'date' => 'required|date|before_or_equal:today',
            'quantity_used' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        if ($this->editingId) {
            $usage = FeedDailyUsage::find($this->editingId);
            $oldQuantity = $usage->quantity_used;
            $oldFeedType = $usage->feed_type_id;
            $flock = $usage->flock;

            $usage->update($validated);

            // Update inventory
            $this->updateInventoryOnUsage($flock, $oldFeedType, $oldQuantity, $validated['feed_type_id'], $validated['quantity_used']);

            session()->flash('status', 'Feed usage record updated successfully.');
        } else {
            $usage = FeedDailyUsage::create($validated);
            $flock = Flock::find($validated['flock_id']);

            // Update inventory
            $this->updateInventoryOnUsage($flock, null, 0, $validated['feed_type_id'], $validated['quantity_used']);

            session()->flash('status', 'Feed usage record created successfully.');
        }

        $this->cancel();
    }

    private function updateInventoryOnUsage($flock, $oldFeedType, $oldQuantity, $newFeedType, $newQuantity)
    {
        $farmId = $flock->coop->farm_id;

        // If editing and feed type changed, restore old inventory
        if ($oldFeedType && $oldFeedType != $newFeedType) {
            $oldInventory = FeedInventory::where('feed_type_id', $oldFeedType)
                ->where('farm_id', $farmId)
                ->first();
            if ($oldInventory) {
                $oldInventory->current_stock += $oldQuantity;
                $oldInventory->save();
            }
        }

        // Update new inventory
        $inventory = FeedInventory::where('feed_type_id', $newFeedType)
            ->where('farm_id', $farmId)
            ->first();

        if ($inventory) {
            // If editing same feed type, adjust difference
            if ($oldFeedType == $newFeedType) {
                $inventory->current_stock = $inventory->current_stock + $oldQuantity - $newQuantity;
            } else {
                $inventory->current_stock -= $newQuantity;
            }
            $inventory->save();
        }
    }

    public function edit($id): void
    {
        $usage = FeedDailyUsage::with('flock.coop.farm')->findOrFail($id);
        $this->editingId = $id;
        $this->selectedFarm = $usage->flock->coop->farm_id;
        $this->selectedCoop = $usage->flock->coop_id;
        $this->flock_id = $usage->flock_id;
        $this->feed_type_id = $usage->feed_type_id;
        $this->date = $usage->date->format('Y-m-d');
        $this->quantity_used = $usage->quantity_used;
        $this->notes = $usage->notes ?? '';
        $this->showForm = true;
    }

    public function delete($id): void
    {
        $usage = FeedDailyUsage::with('flock.coop')->findOrFail($id);

        // Restore inventory
        $farmId = $usage->flock->coop->farm_id;
        $inventory = FeedInventory::where('feed_type_id', $usage->feed_type_id)
            ->where('farm_id', $farmId)
            ->first();

        if ($inventory) {
            $inventory->current_stock += $usage->quantity_used;
            $inventory->save();
        }

        $usage->delete();
        session()->flash('status', 'Feed usage record deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['selectedFarm', 'selectedCoop', 'flock_id', 'feed_type_id', 'quantity_used', 'notes', 'editingId', 'showForm']);
        $this->date = now()->format('Y-m-d');
    }

    public function render()
    {
        $coopsForSelectedFarm = $this->selectedFarm
            ? Coop::where('farm_id', $this->selectedFarm)->where('is_active', true)->orderBy('name')->get()
            : collect();

        $flocksForSelectedCoop = $this->selectedCoop
            ? Flock::where('coop_id', $this->selectedCoop)
                ->where('status', 'active')
                ->orderBy('batch_number')
                ->get()
            : collect();

        return view('livewire.operations.feed-usage', [
            'records' => FeedDailyUsage::with(['flock.coop.farm', 'feedType'])->latest('date')->paginate(15),
            'farms' => Farm::where('is_active', true)->orderBy('name')->get(),
            'coopsForSelectedFarm' => $coopsForSelectedFarm,
            'flocksForSelectedCoop' => $flocksForSelectedCoop,
            'feedTypes' => FeedType::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
