<?php

namespace App\Livewire\Purchases;

use App\Models\SupplierInvoice as SupplierInvoiceModel;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class SupplierInvoice extends Component
{
    public $invoiceId;
    public $invoice;

    public function mount($invoiceId)
    {
        $this->invoiceId = $invoiceId;
        $this->invoice = SupplierInvoiceModel::with(['supplier', 'items', 'payments'])->findOrFail($invoiceId);
    }

    public function downloadPdf()
    {
        $invoice = SupplierInvoiceModel::with(['supplier', 'items', 'payments'])->findOrFail($this->invoiceId);

        $pdf = Pdf::loadView('pdf.supplier-invoice', [
            'invoice' => $invoice,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'supplier-invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function render()
    {
        return view('livewire.purchases.supplier-invoice');
    }
}
