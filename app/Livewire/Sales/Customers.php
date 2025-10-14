<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

class Customers extends Component
{
    use WithPagination;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $contact_person = '';
    public $tax_id = '';
    public $is_active = true;
    public $editingId = null;
    public $showForm = false;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        if ($this->editingId) {
            Customer::find($this->editingId)->update($validated);
            session()->flash('status', 'Customer updated successfully.');
        } else {
            Customer::create($validated);
            session()->flash('status', 'Customer created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $customer = Customer::findOrFail($id);
        $this->editingId = $id;
        $this->name = $customer->name;
        $this->email = $customer->email ?? '';
        $this->phone = $customer->phone ?? '';
        $this->address = $customer->address ?? '';
        $this->contact_person = $customer->contact_person ?? '';
        $this->tax_id = $customer->tax_id ?? '';
        $this->is_active = $customer->is_active;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        Customer::findOrFail($id)->delete();
        session()->flash('status', 'Customer deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'email', 'phone', 'address', 'contact_person', 'tax_id', 'is_active', 'editingId', 'showForm']);
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.sales.customers', [
            'customers' => Customer::latest()->paginate(10),
        ]);
    }
}
