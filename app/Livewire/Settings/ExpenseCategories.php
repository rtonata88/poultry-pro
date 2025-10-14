<?php

namespace App\Livewire\Settings;

use App\Models\ExpenseCategory;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseCategories extends Component
{
    use WithPagination;

    public $name = '';
    public $description = '';
    public $is_active = true;
    public $editingId = null;
    public $showForm = false;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($this->editingId) {
            ExpenseCategory::find($this->editingId)->update($validated);
            session()->flash('status', 'Expense category updated successfully.');
        } else {
            ExpenseCategory::create($validated);
            session()->flash('status', 'Expense category created successfully.');
        }

        $this->reset(['name', 'description', 'is_active', 'editingId', 'showForm']);
        $this->is_active = true;
    }

    public function edit($id): void
    {
        $category = ExpenseCategory::findOrFail($id);
        $this->editingId = $id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->is_active = $category->is_active;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        ExpenseCategory::findOrFail($id)->delete();
        session()->flash('status', 'Expense category deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'description', 'is_active', 'editingId', 'showForm']);
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.settings.expense-categories', [
            'categories' => ExpenseCategory::latest()->paginate(10),
        ]);
    }
}
