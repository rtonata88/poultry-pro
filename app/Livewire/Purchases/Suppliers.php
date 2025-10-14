<?php

namespace App\Livewire\Purchases;

use App\Models\Supplier;
use App\Models\VendorCategory;
use Livewire\Component;
use Livewire\WithPagination;

class Suppliers extends Component
{
    use WithPagination;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $vendor_category_id = '';
    public $contact_person = '';
    public $tax_id = '';
    public $is_active = true;
    public $editingId = null;
    public $showForm = false;
    public $filterCategory = '';

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'vendor_category_id' => 'nullable|exists:vendor_categories,id',
            'contact_person' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        if ($this->editingId) {
            Supplier::find($this->editingId)->update($validated);
            session()->flash('status', 'Supplier updated successfully.');
        } else {
            Supplier::create($validated);
            session()->flash('status', 'Supplier created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $supplier = Supplier::findOrFail($id);
        $this->editingId = $id;
        $this->name = $supplier->name;
        $this->email = $supplier->email ?? '';
        $this->phone = $supplier->phone ?? '';
        $this->address = $supplier->address ?? '';
        $this->vendor_category_id = $supplier->vendor_category_id ?? '';
        $this->contact_person = $supplier->contact_person ?? '';
        $this->tax_id = $supplier->tax_id ?? '';
        $this->is_active = $supplier->is_active;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        Supplier::findOrFail($id)->delete();
        session()->flash('status', 'Supplier deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'email', 'phone', 'address', 'vendor_category_id', 'contact_person', 'tax_id', 'is_active', 'editingId', 'showForm']);
        $this->is_active = true;
    }

    public function render()
    {
        $query = Supplier::with('vendorCategory');

        if ($this->filterCategory) {
            $query->where('vendor_category_id', $this->filterCategory);
        }

        return view('livewire.purchases.suppliers', [
            'suppliers' => $query->latest()->paginate(10),
            'categories' => VendorCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
