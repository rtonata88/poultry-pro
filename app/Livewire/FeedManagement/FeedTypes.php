<?php

namespace App\Livewire\FeedManagement;

use App\Models\FeedType;
use Livewire\Component;
use Livewire\WithPagination;

class FeedTypes extends Component
{
    use WithPagination;

    public $showForm = false;
    public $editingId = null;
    public $name = '';
    public $description = '';
    public $unit = 'kg';

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|in:kg,bags',
        ]);

        if ($this->editingId) {
            FeedType::find($this->editingId)->update($validated);
            session()->flash('status', 'Feed type updated successfully.');
        } else {
            FeedType::create($validated);
            session()->flash('status', 'Feed type created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $feedType = FeedType::findOrFail($id);
        $this->editingId = $id;
        $this->name = $feedType->name;
        $this->description = $feedType->description ?? '';
        $this->unit = $feedType->unit;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        FeedType::findOrFail($id)->delete();
        session()->flash('status', 'Feed type deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'description', 'unit', 'editingId', 'showForm']);
        $this->unit = 'kg';
    }

    public function render()
    {
        return view('livewire.feed-management.feed-types', [
            'feedTypes' => FeedType::where('is_active', true)->orderBy('name')->paginate(12),
        ]);
    }
}
