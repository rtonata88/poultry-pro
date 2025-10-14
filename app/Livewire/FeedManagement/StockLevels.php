<?php

namespace App\Livewire\FeedManagement;

use App\Models\FeedInventory;
use App\Models\FeedType;
use App\Models\Farm;
use Livewire\Component;
use Livewire\WithPagination;

class StockLevels extends Component
{
    use WithPagination;

    public $showForm = false;
    public $editingId = null;
    public $feed_type_id = '';
    public $farm_id = '';
    public $current_stock = 0;
    public $reorder_level = 0;

    public function save(): void
    {
        $validated = $this->validate([
            'feed_type_id' => 'required|exists:feed_types,id',
            'farm_id' => 'nullable|exists:farms,id',
            'current_stock' => 'required|numeric|min:0',
            'reorder_level' => 'required|numeric|min:0',
        ]);

        if ($this->editingId) {
            FeedInventory::find($this->editingId)->update($validated);
            session()->flash('status', 'Stock level updated successfully.');
        } else {
            FeedInventory::create($validated);
            session()->flash('status', 'Stock level created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $inventory = FeedInventory::findOrFail($id);
        $this->editingId = $id;
        $this->feed_type_id = $inventory->feed_type_id;
        $this->farm_id = $inventory->farm_id ?? '';
        $this->current_stock = $inventory->current_stock;
        $this->reorder_level = $inventory->reorder_level;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        FeedInventory::findOrFail($id)->delete();
        session()->flash('status', 'Stock level deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['feed_type_id', 'farm_id', 'current_stock', 'reorder_level', 'editingId', 'showForm']);
    }

    public function render()
    {
        return view('livewire.feed-management.stock-levels', [
            'inventories' => FeedInventory::with(['feedType', 'farm'])->orderBy('created_at', 'desc')->paginate(12),
            'feedTypes' => FeedType::where('is_active', true)->orderBy('name')->get(),
            'farms' => Farm::orderBy('name')->get(),
        ]);
    }
}
