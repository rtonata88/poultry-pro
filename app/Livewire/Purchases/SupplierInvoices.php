<?php

namespace App\Livewire\Purchases;

use App\Models\CompanyInformation;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceItem;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class SupplierInvoices extends Component
{
    use WithPagination, WithFileUploads;

    public $invoice_number = '';
    public $supplier_id = '';
    public $date = '';
    public $due_date = '';
    public $subtotal = 0;
    public $vat = 0;
    public $discount = 0;
    public $total = 0;
    public $reference = '';
    public $notes = '';
    public $document = null;
    public $existingDocumentPath = null;
    public $editingId = null;
    public $showForm = false;
    public $filterSupplier = '';
    public $filterStatus = '';
    public $vatOverridden = false; // Track if user manually changed VAT

    // Line items
    public $items = [];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->generateInvoiceNumber();
        $this->addItem();
    }

    public function generateInvoiceNumber()
    {
        $lastInvoice = SupplierInvoice::latest('id')->first();
        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, 4)) + 1 : 1;
        $this->invoice_number = 'PINV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function addItem()
    {
        $this->items[] = [
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'total' => 0,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function updatedItems()
    {
        foreach ($this->items as $index => $item) {
            $quantity = is_numeric($item['quantity'] ?? 0) ? (float)$item['quantity'] : 0;
            $unitPrice = is_numeric($item['unit_price'] ?? 0) ? (float)$item['unit_price'] : 0;
            $this->items[$index]['total'] = $quantity * $unitPrice;
        }
        $this->calculateTotals();
    }

    public function updatedVat()
    {
        // Mark that user manually changed VAT
        $this->vatOverridden = true;
        $this->calculateTotals();
    }

    public function updatedDiscount()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = collect($this->items)->sum('total');

        // Auto-calculate VAT based on company VAT rate (only if not manually overridden)
        if (!$this->vatOverridden) {
            $company = CompanyInformation::first();
            if ($company && $company->vat_rate) {
                $this->vat = round(($this->subtotal * $company->vat_rate) / 100, 2);
            }
        }

        $vat = is_numeric($this->vat) ? (float)$this->vat : 0;
        $discount = is_numeric($this->discount) ? (float)$this->discount : 0;
        $this->total = $this->subtotal + $vat - $discount;
    }

    public function save(): void
    {
        $this->validate([
            'invoice_number' => 'required|string|max:255|unique:supplier_invoices,invoice_number,' . $this->editingId,
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
            'subtotal' => 'required|numeric|min:0',
            'vat' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        $invoiceData = [
            'invoice_number' => $this->invoice_number,
            'supplier_id' => $this->supplier_id,
            'date' => $this->date,
            'due_date' => $this->due_date,
            'subtotal' => $this->subtotal,
            'vat' => $this->vat ?? 0,
            'discount' => $this->discount ?? 0,
            'total' => $this->total,
            'balance' => $this->total,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'status' => 'unpaid',
        ];

        // Handle file upload
        if ($this->document) {
            // Delete old document if updating
            if ($this->editingId && $this->existingDocumentPath) {
                \Storage::disk('public')->delete($this->existingDocumentPath);
            }

            $documentPath = $this->document->store('supplier-invoices', 'public');
            $invoiceData['document_path'] = $documentPath;
        }

        if ($this->editingId) {
            $invoice = SupplierInvoice::find($this->editingId);

            // Calculate balance based on existing payments
            $invoiceData['balance'] = $this->total - $invoice->amount_paid;

            // Update status based on payment
            if ($invoice->amount_paid >= $this->total) {
                $invoiceData['status'] = 'paid';
            } elseif ($invoice->amount_paid > 0) {
                $invoiceData['status'] = 'partial';
            } elseif (now()->greaterThan($this->due_date)) {
                $invoiceData['status'] = 'overdue';
            } else {
                $invoiceData['status'] = 'unpaid';
            }

            $invoice->update($invoiceData);
            $invoice->items()->delete();

            session()->flash('status', 'Supplier invoice updated successfully.');
        } else {
            $invoice = SupplierInvoice::create($invoiceData);
            session()->flash('status', 'Supplier invoice created successfully.');
        }

        // Save line items
        foreach ($this->items as $item) {
            SupplierInvoiceItem::create([
                'supplier_invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['total'],
            ]);
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $invoice = SupplierInvoice::with('items')->findOrFail($id);
        $this->editingId = $id;
        $this->invoice_number = $invoice->invoice_number;
        $this->supplier_id = $invoice->supplier_id;
        $this->date = $invoice->date->format('Y-m-d');
        $this->due_date = $invoice->due_date->format('Y-m-d');
        $this->subtotal = $invoice->subtotal;
        $this->vat = $invoice->vat;
        $this->vatOverridden = true; // Mark as overridden when loading existing invoice
        $this->discount = $invoice->discount;
        $this->total = $invoice->total;
        $this->reference = $invoice->reference ?? '';
        $this->notes = $invoice->notes ?? '';
        $this->existingDocumentPath = $invoice->document_path;

        $this->items = $invoice->items->map(function ($item) {
            return [
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ];
        })->toArray();

        $this->showForm = true;
    }

    public function delete($id): void
    {
        $invoice = SupplierInvoice::findOrFail($id);

        // Delete document if exists
        if ($invoice->document_path) {
            \Storage::disk('public')->delete($invoice->document_path);
        }

        $invoice->delete();
        session()->flash('status', 'Supplier invoice deleted successfully.');
    }

    public function removeDocument(): void
    {
        if ($this->editingId && $this->existingDocumentPath) {
            $invoice = SupplierInvoice::find($this->editingId);
            if ($invoice) {
                \Storage::disk('public')->delete($invoice->document_path);
                $invoice->update(['document_path' => null]);
                $this->existingDocumentPath = null;
                session()->flash('status', 'Document removed successfully.');
            }
        }
    }

    public function cancel(): void
    {
        $this->reset(['invoice_number', 'supplier_id', 'date', 'due_date', 'subtotal', 'vat', 'discount', 'total', 'reference', 'notes', 'document', 'existingDocumentPath', 'editingId', 'showForm', 'items', 'vatOverridden']);
        $this->date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->generateInvoiceNumber();
        $this->addItem();
    }

    public function render()
    {
        $query = SupplierInvoice::with(['supplier', 'payments']);

        if ($this->filterSupplier) {
            $query->where('supplier_id', $this->filterSupplier);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.purchases.supplier-invoices', [
            'invoices' => $query->latest('date')->paginate(10),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
