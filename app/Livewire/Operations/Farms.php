<?php

namespace App\Livewire\Operations;

use App\Models\Farm;
use Livewire\Component;
use Livewire\WithPagination;

class Farms extends Component
{
    use WithPagination;

    public $name = '';
    public $code = '';
    public $address = '';
    public $city = '';
    public $state = '';
    public $zip_code = '';
    public $country = '';
    public $manager_name = '';
    public $phone = '';
    public $is_active = true;
    public $notes = '';
    public $editingId = null;
    public $showForm = false;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:farms,code,' . $this->editingId,
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        if ($this->editingId) {
            Farm::find($this->editingId)->update($validated);
            session()->flash('status', 'Farm updated successfully.');
        } else {
            Farm::create($validated);
            session()->flash('status', 'Farm created successfully.');
        }

        $this->reset(['name', 'code', 'address', 'city', 'state', 'zip_code', 'country', 'manager_name', 'phone', 'is_active', 'notes', 'editingId', 'showForm']);
        $this->is_active = true;
    }

    public function edit($id): void
    {
        $farm = Farm::findOrFail($id);
        $this->editingId = $id;
        $this->name = $farm->name;
        $this->code = $farm->code;
        $this->address = $farm->address ?? '';
        $this->city = $farm->city ?? '';
        $this->state = $farm->state ?? '';
        $this->zip_code = $farm->zip_code ?? '';
        $this->country = $farm->country ?? '';
        $this->manager_name = $farm->manager_name ?? '';
        $this->phone = $farm->phone ?? '';
        $this->is_active = $farm->is_active;
        $this->notes = $farm->notes ?? '';
        $this->showForm = true;
    }

    public function delete($id): void
    {
        Farm::findOrFail($id)->delete();
        session()->flash('status', 'Farm deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'code', 'address', 'city', 'state', 'zip_code', 'country', 'manager_name', 'phone', 'is_active', 'notes', 'editingId', 'showForm']);
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.operations.farms', [
            'farms' => Farm::latest()->paginate(10),
        ]);
    }
}
