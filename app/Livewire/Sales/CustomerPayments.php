<?php

namespace App\Livewire\Sales;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\Customer;
use App\Models\CustomerInvoice;
use App\Models\CustomerPayment;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerPayments extends Component
{
    use WithPagination;

    public $payment_number = '';
    public $customer_id = '';
    public $customer_invoice_id = '';
    public $date = '';
    public $amount = 0;
    public $payment_method_id = '';
    public $bank_account_id = '';
    public $reference = '';
    public $notes = '';
    public $editingId = null;
    public $showForm = false;
    public $filterCustomer = '';
    public $unpaidInvoices = [];

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $this->generatePaymentNumber();
    }

    public function generatePaymentNumber()
    {
        $lastPayment = CustomerPayment::latest('id')->first();
        $nextNumber = $lastPayment ? ((int) substr($lastPayment->payment_number, 4)) + 1 : 1;
        $this->payment_number = 'REC-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function updatedCustomerId()
    {
        $this->customer_invoice_id = '';
        $this->loadUnpaidInvoices();
    }

    public function loadUnpaidInvoices()
    {
        if ($this->customer_id) {
            $this->unpaidInvoices = CustomerInvoice::where('customer_id', $this->customer_id)
                ->whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->orderBy('date', 'desc')
                ->get();
        } else {
            $this->unpaidInvoices = [];
        }
    }

    public function updatedCustomerInvoiceId()
    {
        if ($this->customer_invoice_id) {
            $invoice = CustomerInvoice::find($this->customer_invoice_id);
            if ($invoice) {
                $this->amount = $invoice->balance;
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'payment_number' => 'required|string|max:255|unique:customer_payments,payment_number,' . $this->editingId,
            'customer_id' => 'required|exists:customers,id',
            'customer_invoice_id' => 'required|exists:customer_invoices,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $invoice = CustomerInvoice::find($this->customer_invoice_id);

        // Validate payment amount doesn't exceed balance
        if ($this->amount > $invoice->balance) {
            $this->addError('amount', 'Payment amount cannot exceed invoice balance of ' . number_format($invoice->balance, 2));
            return;
        }

        DB::transaction(function () use ($invoice) {
            $paymentData = [
                'payment_number' => $this->payment_number,
                'customer_id' => $this->customer_id,
                'customer_invoice_id' => $this->customer_invoice_id,
                'date' => $this->date,
                'amount' => $this->amount,
                'payment_method_id' => $this->payment_method_id,
                'bank_account_id' => $this->bank_account_id,
                'reference' => $this->reference,
                'notes' => $this->notes,
            ];

            if ($this->editingId) {
                $payment = CustomerPayment::findOrFail($this->editingId);
                $oldAmount = $payment->amount;
                $oldBankAccountId = $payment->bank_account_id;

                // Reverse old bank transaction
                if ($oldBankAccountId) {
                    $oldTransaction = $payment->bankTransaction;
                    if ($oldTransaction) {
                        $bankAccount = BankAccount::find($oldBankAccountId);
                        $bankAccount->decrement('current_balance', $oldAmount);
                        $oldTransaction->delete();
                    }
                }

                $payment->update($paymentData);

                // Update invoice amounts
                $invoice->amount_paid = $invoice->amount_paid - $oldAmount + $this->amount;
                $invoice->balance = $invoice->total - $invoice->amount_paid;

                // Create new bank transaction (incoming payment - positive amount)
                $bankAccount = BankAccount::findOrFail($this->bank_account_id);
                $bankAccount->increment('current_balance', $this->amount);

                BankTransaction::create([
                    'bank_account_id' => $this->bank_account_id,
                    'transaction_type' => 'customer_payment',
                    'transactionable_type' => CustomerPayment::class,
                    'transactionable_id' => $payment->id,
                    'amount' => $this->amount,
                    'transaction_date' => $this->date,
                    'description' => 'Customer Payment: ' . $this->payment_number . ' - Invoice: ' . $invoice->invoice_number,
                    'balance_after' => $bankAccount->current_balance,
                    'reference' => $this->reference,
                ]);

                session()->flash('status', 'Customer payment updated successfully.');
            } else {
                $payment = CustomerPayment::create($paymentData);

                // Update invoice amounts
                $invoice->amount_paid += $this->amount;
                $invoice->balance = $invoice->total - $invoice->amount_paid;

                // Create bank transaction (incoming payment - positive amount)
                $bankAccount = BankAccount::findOrFail($this->bank_account_id);
                $bankAccount->increment('current_balance', $this->amount);

                BankTransaction::create([
                    'bank_account_id' => $this->bank_account_id,
                    'transaction_type' => 'customer_payment',
                    'transactionable_type' => CustomerPayment::class,
                    'transactionable_id' => $payment->id,
                    'amount' => $this->amount,
                    'transaction_date' => $this->date,
                    'description' => 'Customer Payment: ' . $this->payment_number . ' - Invoice: ' . $invoice->invoice_number,
                    'balance_after' => $bankAccount->current_balance,
                    'reference' => $this->reference,
                ]);

                session()->flash('status', 'Customer payment created successfully.');
            }

            // Update invoice status
            if ($invoice->amount_paid >= $invoice->total) {
                $invoice->status = 'paid';
            } elseif ($invoice->amount_paid > 0) {
                $invoice->status = 'partial';
            }

            $invoice->save();
        });

        $this->cancel();
    }

    public function edit($id): void
    {
        $payment = CustomerPayment::findOrFail($id);
        $this->editingId = $id;
        $this->payment_number = $payment->payment_number;
        $this->customer_id = $payment->customer_id;
        $this->loadUnpaidInvoices();
        $this->customer_invoice_id = $payment->customer_invoice_id;
        $this->date = $payment->date->format('Y-m-d');
        $this->amount = $payment->amount;
        $this->payment_method_id = $payment->payment_method_id;
        $this->bank_account_id = $payment->bank_account_id ?? '';
        $this->reference = $payment->reference ?? '';
        $this->notes = $payment->notes ?? '';
        $this->showForm = true;
    }

    public function delete($id): void
    {
        DB::transaction(function () use ($id) {
            $payment = CustomerPayment::findOrFail($id);
            $invoice = $payment->customerInvoice;

            // Reverse bank transaction
            if ($payment->bank_account_id) {
                $transaction = $payment->bankTransaction;
                if ($transaction) {
                    $bankAccount = BankAccount::find($payment->bank_account_id);
                    $bankAccount->decrement('current_balance', $payment->amount);
                    $transaction->delete();
                }
            }

            // Reverse the payment effect on invoice
            $invoice->amount_paid -= $payment->amount;
            $invoice->balance = $invoice->total - $invoice->amount_paid;

            // Update invoice status
            if ($invoice->amount_paid <= 0) {
                $invoice->status = now()->greaterThan($invoice->due_date) ? 'overdue' : 'unpaid';
            } elseif ($invoice->amount_paid < $invoice->total) {
                $invoice->status = 'partial';
            }

            $invoice->save();
            $payment->delete();
        });

        session()->flash('status', 'Customer payment deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['payment_number', 'customer_id', 'customer_invoice_id', 'date', 'amount', 'payment_method_id', 'bank_account_id', 'reference', 'notes', 'editingId', 'showForm', 'unpaidInvoices']);
        $this->date = now()->format('Y-m-d');
        $this->generatePaymentNumber();
    }

    public function render()
    {
        $query = CustomerPayment::with(['customer', 'customerInvoice', 'paymentMethod']);

        if ($this->filterCustomer) {
            $query->where('customer_id', $this->filterCustomer);
        }

        return view('livewire.sales.customer-payments', [
            'payments' => $query->latest('date')->paginate(10),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('account_name')->get(),
        ]);
    }
}
