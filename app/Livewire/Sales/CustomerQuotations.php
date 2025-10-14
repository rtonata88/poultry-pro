<?php

namespace App\Livewire\Sales;

use App\Mail\QuotationMail;
use App\Models\CompanyInformation;
use App\Models\Customer;
use App\Models\CustomerQuotation;
use App\Models\CustomerQuotationItem;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceItem;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerQuotations extends Component
{
    use WithPagination;

    public $quotation_number = '';
    public $customer_id = '';
    public $date = '';
    public $valid_until = '';
    public $subtotal = 0;
    public $vat = 0;
    public $discount = 0;
    public $total = 0;
    public $reference = '';
    public $notes = '';
    public $editingId = null;
    public $showForm = false;
    public $filterCustomer = '';
    public $filterStatus = '';
    public $vatOverridden = false; // Track if user manually changed VAT

    // Line items
    public $items = [];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $this->valid_until = now()->addDays(30)->format('Y-m-d');
        $this->generateQuotationNumber();
        $this->addItem();
    }

    public function generateQuotationNumber()
    {
        $lastQuotation = CustomerQuotation::latest('id')->first();
        $nextNumber = $lastQuotation ? ((int) substr($lastQuotation->quotation_number, 4)) + 1 : 1;
        $this->quotation_number = 'QUO-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function addItem()
    {
        $this->items[] = [
            'product_id' => '',
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'total' => 0,
        ];
    }

    public function selectProduct($index, $productId)
    {
        if ($productId) {
            $product = Product::find($productId);
            if ($product) {
                $this->items[$index]['product_id'] = $product->id;
                $this->items[$index]['description'] = $product->name;
                $this->items[$index]['unit_price'] = $product->price;
                $this->updatedItems();
            }
        }
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
            'quotation_number' => 'required|string|max:255|unique:customer_quotations,quotation_number,' . $this->editingId,
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:date',
            'subtotal' => 'required|numeric|min:0',
            'vat' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total' => 'required|numeric|min:0',
        ]);

        $quotationData = [
            'quotation_number' => $this->quotation_number,
            'customer_id' => $this->customer_id,
            'date' => $this->date,
            'valid_until' => $this->valid_until,
            'subtotal' => $this->subtotal,
            'vat' => $this->vat ?? 0,
            'discount' => $this->discount ?? 0,
            'total' => $this->total,
            'reference' => $this->reference,
            'notes' => $this->notes,
            'status' => 'draft',
        ];

        if ($this->editingId) {
            $quotation = CustomerQuotation::find($this->editingId);

            // Preserve status if it's not draft
            if ($quotation->status !== 'draft') {
                $quotationData['status'] = $quotation->status;
            }

            $quotation->update($quotationData);
            $quotation->items()->delete();

            session()->flash('status', 'Customer quotation updated successfully.');
        } else {
            $quotation = CustomerQuotation::create($quotationData);
            session()->flash('status', 'Customer quotation created successfully.');
        }

        // Save line items
        foreach ($this->items as $item) {
            CustomerQuotationItem::create([
                'customer_quotation_id' => $quotation->id,
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
        $quotation = CustomerQuotation::with('items')->findOrFail($id);
        $this->editingId = $id;
        $this->quotation_number = $quotation->quotation_number;
        $this->customer_id = $quotation->customer_id;
        $this->date = $quotation->date->format('Y-m-d');
        $this->valid_until = $quotation->valid_until->format('Y-m-d');
        $this->subtotal = $quotation->subtotal;
        $this->vat = $quotation->vat;
        $this->vatOverridden = true; // Mark as overridden when loading existing quotation
        $this->discount = $quotation->discount;
        $this->total = $quotation->total;
        $this->reference = $quotation->reference ?? '';
        $this->notes = $quotation->notes ?? '';

        $this->items = $quotation->items->map(function ($item) {
            return [
                'product_id' => '',
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
        CustomerQuotation::findOrFail($id)->delete();
        session()->flash('status', 'Customer quotation deleted successfully.');
    }

    public function convertToInvoice($id)
    {
        $quotation = CustomerQuotation::with('items')->findOrFail($id);

        // Generate invoice number
        $lastInvoice = CustomerInvoice::latest('id')->first();
        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, 4)) + 1 : 1;
        $invoiceNumber = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Create invoice
        $invoice = CustomerInvoice::create([
            'invoice_number' => $invoiceNumber,
            'customer_id' => $quotation->customer_id,
            'customer_quotation_id' => $quotation->id,
            'date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'subtotal' => $quotation->subtotal,
            'vat' => $quotation->vat,
            'discount' => $quotation->discount,
            'total' => $quotation->total,
            'balance' => $quotation->total,
            'amount_paid' => 0,
            'status' => 'unpaid',
            'reference' => $quotation->reference,
            'notes' => $quotation->notes,
        ]);

        // Copy line items
        foreach ($quotation->items as $item) {
            CustomerInvoiceItem::create([
                'customer_invoice_id' => $invoice->id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ]);
        }

        // Update quotation status to accepted
        $quotation->update(['status' => 'accepted']);

        session()->flash('status', 'Quotation converted to invoice successfully.');

        // Redirect to invoices page
        return redirect()->route('sales.invoices');
    }

    public function sendEmail($id): void
    {
        $quotation = CustomerQuotation::with(['customer', 'items'])->findOrFail($id);

        if (!$quotation->customer->email) {
            session()->flash('error', 'Customer does not have an email address.');
            return;
        }

        try {
            // Send email
            Mail::to($quotation->customer->email)->send(new QuotationMail($quotation));

            // Update status to sent
            $quotation->update(['status' => 'sent']);

            session()->flash('status', 'Quotation sent to ' . $quotation->customer->email . ' successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function downloadPdf($id)
    {
        $quotation = CustomerQuotation::with(['customer', 'items'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.quotation', ['quotation' => $quotation]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'quotation-' . $quotation->quotation_number . '.pdf');
    }

    public function cancel(): void
    {
        $this->reset(['quotation_number', 'customer_id', 'date', 'valid_until', 'subtotal', 'vat', 'discount', 'total', 'reference', 'notes', 'editingId', 'showForm', 'items', 'vatOverridden']);
        $this->date = now()->format('Y-m-d');
        $this->valid_until = now()->addDays(30)->format('Y-m-d');
        $this->generateQuotationNumber();
        $this->addItem();
    }

    public function render()
    {
        $query = CustomerQuotation::with('customer');

        if ($this->filterCustomer) {
            $query->where('customer_id', $this->filterCustomer);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.sales.customer-quotations', [
            'quotations' => $query->latest('id')->paginate(10),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
