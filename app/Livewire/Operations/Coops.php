<?php

namespace App\Livewire\Operations;

use App\Models\Coop;
use App\Models\Farm;
use Livewire\Component;
use Livewire\WithPagination;

class Coops extends Component
{
    use WithPagination;

    public $farm_id = '';
    public $name = '';
    public $code = '';
    public $capacity = '';
    public $type = 'layers';
    public $is_active = true;
    public $notes = '';
    public $editingId = null;
    public $showForm = false;

    public function save(): void
    {
        $validated = $this->validate([
            'farm_id' => 'required|exists:farms,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'type' => 'required|in:layers,broilers,breeders',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($this->editingId) {
            Coop::find($this->editingId)->update($validated);
            session()->flash('status', 'Coop updated successfully.');
        } else {
            Coop::create($validated);
            session()->flash('status', 'Coop created successfully.');
        }

        $this->reset(['farm_id', 'name', 'code', 'capacity', 'type', 'is_active', 'notes', 'editingId', 'showForm']);
        $this->is_active = true;
        $this->type = 'layers';
    }

    public function edit($id): void
    {
        $coop = Coop::findOrFail($id);
        $this->editingId = $id;
        $this->farm_id = $coop->farm_id;
        $this->name = $coop->name;
        $this->code = $coop->code;
        $this->capacity = $coop->capacity ?? '';
        $this->type = $coop->type;
        $this->is_active = $coop->is_active;
        $this->notes = $coop->notes ?? '';
        $this->showForm = true;
    }

    public function delete($id): void
    {
        Coop::findOrFail($id)->delete();
        session()->flash('status', 'Coop deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['farm_id', 'name', 'code', 'capacity', 'type', 'is_active', 'notes', 'editingId', 'showForm']);
        $this->is_active = true;
        $this->type = 'layers';
    }

    public function render()
    {
        return view('livewire.operations.coops', [
            'coops' => Coop::with('farm')->latest()->paginate(10),
            'farms' => Farm::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
