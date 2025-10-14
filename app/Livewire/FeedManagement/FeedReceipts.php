<?php

namespace App\Livewire\FeedManagement;

use App\Models\FeedReceipt;
use App\Models\FeedType;
use App\Models\Farm;
use App\Models\FeedInventory;
use Livewire\Component;
use Livewire\WithPagination;

class FeedReceipts extends Component
{
    use WithPagination;

    public $showForm = false;
    public $editingId = null;
    public $feed_type_id = '';
    public $farm_id = '';
    public $date = '';
    public $quantity = '';
    public $supplier = '';
    public $notes = '';

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'feed_type_id' => 'required|exists:feed_types,id',
            'farm_id' => 'nullable|exists:farms,id',
            'date' => 'required|date',
            'quantity' => 'required|numeric|min:0.01',
            'supplier' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($this->editingId) {
            $receipt = FeedReceipt::find($this->editingId);
            $oldQuantity = $receipt->quantity;
            $oldFeedType = $receipt->feed_type_id;
            $oldFarmId = $receipt->farm_id;

            $receipt->update($validated);

            // Update inventory
            $this->updateInventoryOnEdit($oldFeedType, $oldFarmId, $oldQuantity, $validated['feed_type_id'], $validated['farm_id'], $validated['quantity']);

            session()->flash('status', 'Receipt updated successfully.');
        } else {
            FeedReceipt::create($validated);

            // Update inventory - add to stock
            $this->updateInventoryOnCreate($validated['feed_type_id'], $validated['farm_id'], $validated['quantity']);

            session()->flash('status', 'Receipt created successfully.');
        }

        $this->cancel();
    }

    private function updateInventoryOnCreate($feedTypeId, $farmId, $quantity): void
    {
        $inventory = FeedInventory::firstOrCreate(
            [
                'feed_type_id' => $feedTypeId,
                'farm_id' => $farmId,
            ],
            [
                'current_stock' => 0,
                'reorder_level' => 0,
            ]
        );

        $inventory->current_stock += $quantity;
        $inventory->save();
    }

    private function updateInventoryOnEdit($oldFeedType, $oldFarmId, $oldQuantity, $newFeedType, $newFarmId, $newQuantity): void
    {
        // Subtract old quantity from old inventory
        $oldInventory = FeedInventory::where('feed_type_id', $oldFeedType)
            ->where('farm_id', $oldFarmId)
            ->first();

        if ($oldInventory) {
            $oldInventory->current_stock -= $oldQuantity;
            $oldInventory->save();
        }

        // Add new quantity to new inventory
        $newInventory = FeedInventory::firstOrCreate(
            [
                'feed_type_id' => $newFeedType,
                'farm_id' => $newFarmId,
            ],
            [
                'current_stock' => 0,
                'reorder_level' => 0,
            ]
        );

        $newInventory->current_stock += $newQuantity;
        $newInventory->save();
    }

    public function edit($id): void
    {
        $receipt = FeedReceipt::findOrFail($id);
        $this->editingId = $id;
        $this->feed_type_id = $receipt->feed_type_id;
        $this->farm_id = $receipt->farm_id ?? '';
        $this->date = $receipt->date->format('Y-m-d');
        $this->quantity = $receipt->quantity;
        $this->supplier = $receipt->supplier ?? '';
        $this->notes = $receipt->notes ?? '';
        $this->showForm = true;
    }

    public function delete($id): void
    {
        $receipt = FeedReceipt::findOrFail($id);

        // Update inventory - subtract the quantity
        $inventory = FeedInventory::where('feed_type_id', $receipt->feed_type_id)
            ->where('farm_id', $receipt->farm_id)
            ->first();

        if ($inventory) {
            $inventory->current_stock -= $receipt->quantity;
            $inventory->save();
        }

        $receipt->delete();
        session()->flash('status', 'Receipt deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['feed_type_id', 'farm_id', 'quantity', 'supplier', 'notes', 'editingId', 'showForm']);
        $this->date = now()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.feed-management.feed-receipts', [
            'receipts' => FeedReceipt::with(['feedType', 'farm'])->orderBy('date', 'desc')->paginate(12),
            'feedTypes' => FeedType::where('is_active', true)->orderBy('name')->get(),
            'farms' => Farm::orderBy('name')->get(),
        ]);
    }
}
