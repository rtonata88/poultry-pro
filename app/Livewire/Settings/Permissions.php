<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class Permissions extends Component
{
    use WithPagination;

    public $name = '';
    public $editingId = null;
    public $showForm = false;
    public $search = '';

    public function mount()
    {
        //
    }

    public function create()
    {
        $this->reset(['name', 'editingId']);
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $this->editingId,
        ]);

        if ($this->editingId) {
            $permission = Permission::findOrFail($this->editingId);
            $permission->update(['name' => $this->name]);
            session()->flash('status', 'Permission updated successfully.');
        } else {
            Permission::create(['name' => $this->name]);
            session()->flash('status', 'Permission created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $permission = Permission::findOrFail($id);
        $this->editingId = $id;
        $this->name = $permission->name;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        Permission::findOrFail($id)->delete();
        session()->flash('status', 'Permission deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'editingId', 'showForm']);
    }

    public function render()
    {
        $query = Permission::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.settings.permissions', [
            'permissions' => $query->orderBy('name')->paginate(10),
        ]);
    }
}
