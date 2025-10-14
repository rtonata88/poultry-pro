<?php

namespace App\Livewire\Sales;

use App\Mail\InvoiceMail;
use App\Models\CompanyInformation;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceItem;
use App\Models\CustomerQuotation;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerInvoices extends Component
{
    use WithPagination;

    public $invoice_number = '';
    public $customer_id = '';
    public $customer_quotation_id = '';
    public $date = '';
    public $due_date = '';
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
    public $acceptedQuotations = [];

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
        $lastInvoice = CustomerInvoice::latest('id')->first();
        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice->invoice_number, 4)) + 1 : 1;
        $this->invoice_number = 'INV-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
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

    public function updatedCustomerId()
    {
        $this->customer_quotation_id = '';
        $this->loadAcceptedQuotations();
    }

    public function loadAcceptedQuotations()
    {
        if ($this->customer_id) {
            $this->acceptedQuotations = CustomerQuotation::where('customer_id', $this->customer_id)
                ->where('status', 'accepted')
                ->orderBy('date', 'desc')
                ->get();
        } else {
            $this->acceptedQuotations = [];
        }
    }

    public function updatedCustomerQuotationId()
    {
        if ($this->customer_quotation_id) {
            $quotation = CustomerQuotation::with('items')->find($this->customer_quotation_id);
            if ($quotation) {
                // Pre-fill invoice with quotation details
                $this->reference = $quotation->reference ?? '';
                $this->notes = $quotation->notes ?? '';
                $this->discount = $quotation->discount;
                $this->vatOverridden = true; // Mark as overridden when loading from quotation
                $this->vat = $quotation->vat;

                $this->items = $quotation->items->map(function ($item) {
                    return [
                        'product_id' => '',
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total,
                    ];
                })->toArray();

                $this->calculateTotals();
            }
        }
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
            'invoice_number' => 'required|string|max:255|unique:customer_invoices,invoice_number,' . $this->editingId,
            'customer_id' => 'required|exists:customers,id',
            'customer_quotation_id' => 'nullable|exists:customer_quotations,id',
            'date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date',
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

        $invoiceData = [
            'invoice_number' => $this->invoice_number,
            'customer_id' => $this->customer_id,
            'customer_quotation_id' => $this->customer_quotation_id ?: null,
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

        if ($this->editingId) {
            $invoice = CustomerInvoice::find($this->editingId);

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

            session()->flash('status', 'Customer invoice updated successfully.');
        } else {
            $invoice = CustomerInvoice::create($invoiceData);
            session()->flash('status', 'Customer invoice created successfully.');
        }

        // Save line items
        foreach ($this->items as $item) {
            CustomerInvoiceItem::create([
                'customer_invoice_id' => $invoice->id,
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
        $invoice = CustomerInvoice::with('items')->findOrFail($id);
        $this->editingId = $id;
        $this->invoice_number = $invoice->invoice_number;
        $this->customer_id = $invoice->customer_id;
        $this->loadAcceptedQuotations();
        $this->customer_quotation_id = $invoice->customer_quotation_id ?? '';
        $this->date = $invoice->date->format('Y-m-d');
        $this->due_date = $invoice->due_date->format('Y-m-d');
        $this->subtotal = $invoice->subtotal;
        $this->vat = $invoice->vat;
        $this->vatOverridden = true; // Mark as overridden when loading existing invoice
        $this->discount = $invoice->discount;
        $this->total = $invoice->total;
        $this->reference = $invoice->reference ?? '';
        $this->notes = $invoice->notes ?? '';

        $this->items = $invoice->items->map(function ($item) {
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
        CustomerInvoice::findOrFail($id)->delete();
        session()->flash('status', 'Customer invoice deleted successfully.');
    }

    public function sendEmail($id): void
    {
        $invoice = CustomerInvoice::with(['customer', 'items', 'quotation'])->findOrFail($id);

        if (!$invoice->customer->email) {
            session()->flash('error', 'Customer does not have an email address.');
            return;
        }

        try {
            // Send email
            Mail::to($invoice->customer->email)->send(new InvoiceMail($invoice));

            session()->flash('status', 'Invoice sent to ' . $invoice->customer->email . ' successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function downloadPdf($id)
    {
        $invoice = CustomerInvoice::with(['customer', 'items', 'quotation'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function recordPayment($id)
    {
        // Redirect to payments page with invoice pre-selected
        session()->flash('invoice_id', $id);
        return redirect()->route('sales.payments');
    }

    public function cancel(): void
    {
        $this->reset(['invoice_number', 'customer_id', 'customer_quotation_id', 'date', 'due_date', 'subtotal', 'vat', 'discount', 'total', 'reference', 'notes', 'editingId', 'showForm', 'items', 'vatOverridden', 'acceptedQuotations']);
        $this->date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->generateInvoiceNumber();
        $this->addItem();
    }

    public function render()
    {
        $query = CustomerInvoice::with(['customer', 'quotation', 'payments']);

        if ($this->filterCustomer) {
            $query->where('customer_id', $this->filterCustomer);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.sales.customer-invoices', [
            'invoices' => $query->latest('id')->paginate(10),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
