<?php

namespace App\Livewire\Settings;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class Products extends Component
{
    use WithPagination;

    public $name = '';
    public $description = '';
    public $type = 'chicken';
    public $unit = 'piece';
    public $unit_size = null;
    public $price = 0;
    public $is_active = true;
    public $editingId = null;
    public $showForm = false;
    public $filterType = '';

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:chicken,eggs',
            'unit' => 'required|string|max:50',
            'unit_size' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        if ($this->editingId) {
            Product::find($this->editingId)->update($validated);
            session()->flash('status', 'Product updated successfully.');
        } else {
            Product::create($validated);
            session()->flash('status', 'Product created successfully.');
        }

        $this->reset(['name', 'description', 'type', 'unit', 'unit_size', 'price', 'is_active', 'editingId', 'showForm']);
        $this->is_active = true;
        $this->type = 'chicken';
        $this->unit = 'piece';
        $this->price = 0;
    }

    public function edit($id): void
    {
        $product = Product::findOrFail($id);
        $this->editingId = $id;
        $this->name = $product->name;
        $this->description = $product->description ?? '';
        $this->type = $product->type;
        $this->unit = $product->unit;
        $this->unit_size = $product->unit_size;
        $this->price = $product->price;
        $this->is_active = $product->is_active;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        Product::findOrFail($id)->delete();
        session()->flash('status', 'Product deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'description', 'type', 'unit', 'unit_size', 'price', 'is_active', 'editingId', 'showForm']);
        $this->is_active = true;
        $this->type = 'chicken';
        $this->unit = 'piece';
        $this->price = 0;
    }

    public function render()
    {
        $query = Product::query();

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        return view('livewire.settings.products', [
            'products' => $query->latest()->paginate(10),
        ]);
    }
}
