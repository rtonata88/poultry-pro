<?php

namespace App\Livewire\Settings;

use App\Models\PaymentMethod;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentMethods extends Component
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
            PaymentMethod::find($this->editingId)->update($validated);
            session()->flash('status', 'Payment method updated successfully.');
        } else {
            PaymentMethod::create($validated);
            session()->flash('status', 'Payment method created successfully.');
        }

        $this->reset(['name', 'description', 'is_active', 'editingId', 'showForm']);
        $this->is_active = true;
    }

    public function edit($id): void
    {
        $method = PaymentMethod::findOrFail($id);
        $this->editingId = $id;
        $this->name = $method->name;
        $this->description = $method->description ?? '';
        $this->is_active = $method->is_active;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        PaymentMethod::findOrFail($id)->delete();
        session()->flash('status', 'Payment method deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'description', 'is_active', 'editingId', 'showForm']);
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.settings.payment-methods', [
            'methods' => PaymentMethod::latest()->paginate(10),
        ]);
    }
}
