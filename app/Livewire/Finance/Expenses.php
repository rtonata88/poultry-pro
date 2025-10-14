<?php

namespace App\Livewire\Finance;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\CompanyInformation;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Expenses extends Component
{
    use WithPagination, WithFileUploads;

    public $expense_number = '';
    public $expense_category_id = '';
    public $supplier_id = '';
    public $payment_method_id = '';
    public $bank_account_id = '';
    public $date = '';
    public $amount = 0;
    public $vat = 0;
    public $total = 0;
    public $reference = '';
    public $notes = '';
    public $status = 'pending';
    public $document = null;
    public $existingDocumentPath = null;
    public $editingId = null;
    public $showForm = false;
    public $filterCategory = '';
    public $filterSupplier = '';
    public $filterStatus = '';
    public $vatOverridden = false;

    public function mount()
    {
        $this->date = now()->format('Y-m-d');
        $this->generateExpenseNumber();
    }

    public function generateExpenseNumber()
    {
        $lastExpense = Expense::latest('id')->first();
        $nextNumber = $lastExpense ? ((int) substr($lastExpense->expense_number, 4)) + 1 : 1;
        $this->expense_number = 'EXP-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function updatedAmount()
    {
        $this->calculateTotals();
    }

    public function updatedVat()
    {
        $this->vatOverridden = true;
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        // Auto-calculate VAT based on company VAT rate (only if not manually overridden)
        if (!$this->vatOverridden) {
            $company = CompanyInformation::first();
            if ($company && $company->vat_rate) {
                $this->vat = round(($this->amount * $company->vat_rate) / 100, 2);
            }
        }

        $amount = is_numeric($this->amount) ? (float)$this->amount : 0;
        $vat = is_numeric($this->vat) ? (float)$this->vat : 0;
        $this->total = $amount + $vat;
    }

    public function save(): void
    {
        $this->validate([
            'expense_number' => 'required|string|max:255|unique:expenses,expense_number,' . $this->editingId,
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'bank_account_id' => $this->status === 'paid' ? 'required|exists:bank_accounts,id' : 'nullable|exists:bank_accounts,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'vat' => 'nullable|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,paid',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        DB::transaction(function () {
            $expenseData = [
                'expense_number' => $this->expense_number,
                'expense_category_id' => $this->expense_category_id ?: null,
                'supplier_id' => $this->supplier_id ?: null,
                'payment_method_id' => $this->payment_method_id ?: null,
                'bank_account_id' => $this->bank_account_id ?: null,
                'date' => $this->date,
                'amount' => $this->amount,
                'vat' => $this->vat ?? 0,
                'total' => $this->total,
                'reference' => $this->reference,
                'notes' => $this->notes,
                'status' => $this->status,
            ];

            // Handle file upload
            if ($this->document) {
                // Delete old document if updating
                if ($this->editingId && $this->existingDocumentPath) {
                    \Storage::disk('public')->delete($this->existingDocumentPath);
                }

                $documentPath = $this->document->store('expenses', 'public');
                $expenseData['document_path'] = $documentPath;
            }

            if ($this->editingId) {
                $expense = Expense::findOrFail($this->editingId);
                $oldStatus = $expense->status;
                $oldBankAccountId = $expense->bank_account_id;
                $oldTotal = $expense->total;

                // Reverse old bank transaction if status was paid
                if ($oldStatus === 'paid' && $oldBankAccountId) {
                    $oldTransaction = $expense->bankTransaction;
                    if ($oldTransaction) {
                        $bankAccount = BankAccount::find($oldBankAccountId);
                        $bankAccount->increment('current_balance', $oldTotal);
                        $oldTransaction->delete();
                    }
                }

                $expense->update($expenseData);

                // Create new bank transaction if status is paid
                if ($this->status === 'paid' && $this->bank_account_id) {
                    $bankAccount = BankAccount::findOrFail($this->bank_account_id);
                    $bankAccount->decrement('current_balance', $this->total);

                    BankTransaction::create([
                        'bank_account_id' => $this->bank_account_id,
                        'transaction_type' => 'expense',
                        'transactionable_type' => Expense::class,
                        'transactionable_id' => $expense->id,
                        'amount' => -$this->total,
                        'transaction_date' => $this->date,
                        'description' => 'Expense: ' . $this->expense_number . ($this->reference ? ' - ' . $this->reference : ''),
                        'balance_after' => $bankAccount->current_balance,
                        'reference' => $this->reference,
                    ]);
                }

                session()->flash('status', 'Expense updated successfully.');
            } else {
                $expense = Expense::create($expenseData);

                // Create bank transaction if status is paid
                if ($this->status === 'paid' && $this->bank_account_id) {
                    $bankAccount = BankAccount::findOrFail($this->bank_account_id);
                    $bankAccount->decrement('current_balance', $this->total);

                    BankTransaction::create([
                        'bank_account_id' => $this->bank_account_id,
                        'transaction_type' => 'expense',
                        'transactionable_type' => Expense::class,
                        'transactionable_id' => $expense->id,
                        'amount' => -$this->total,
                        'transaction_date' => $this->date,
                        'description' => 'Expense: ' . $this->expense_number . ($this->reference ? ' - ' . $this->reference : ''),
                        'balance_after' => $bankAccount->current_balance,
                        'reference' => $this->reference,
                    ]);
                }

                session()->flash('status', 'Expense created successfully.');
            }
        });

        $this->cancel();
    }

    public function edit($id): void
    {
        $expense = Expense::findOrFail($id);
        $this->editingId = $id;
        $this->expense_number = $expense->expense_number;
        $this->expense_category_id = $expense->expense_category_id ?? '';
        $this->supplier_id = $expense->supplier_id ?? '';
        $this->payment_method_id = $expense->payment_method_id ?? '';
        $this->bank_account_id = $expense->bank_account_id ?? '';
        $this->date = $expense->date->format('Y-m-d');
        $this->amount = $expense->amount;
        $this->vat = $expense->vat;
        $this->vatOverridden = true;
        $this->total = $expense->total;
        $this->reference = $expense->reference ?? '';
        $this->notes = $expense->notes ?? '';
        $this->status = $expense->status;
        $this->existingDocumentPath = $expense->document_path;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        DB::transaction(function () use ($id) {
            $expense = Expense::findOrFail($id);

            // Reverse bank transaction if expense was paid
            if ($expense->status === 'paid' && $expense->bank_account_id) {
                $transaction = $expense->bankTransaction;
                if ($transaction) {
                    $bankAccount = BankAccount::find($expense->bank_account_id);
                    $bankAccount->increment('current_balance', $expense->total);
                    $transaction->delete();
                }
            }

            // Delete document if exists
            if ($expense->document_path) {
                \Storage::disk('public')->delete($expense->document_path);
            }

            $expense->delete();
        });

        session()->flash('status', 'Expense deleted successfully.');
    }

    public function removeDocument(): void
    {
        if ($this->editingId && $this->existingDocumentPath) {
            $expense = Expense::find($this->editingId);
            if ($expense) {
                \Storage::disk('public')->delete($expense->document_path);
                $expense->update(['document_path' => null]);
                $this->existingDocumentPath = null;
                session()->flash('status', 'Document removed successfully.');
            }
        }
    }

    public function cancel(): void
    {
        $this->reset(['expense_number', 'expense_category_id', 'supplier_id', 'payment_method_id', 'bank_account_id', 'date', 'amount', 'vat', 'total', 'reference', 'notes', 'status', 'document', 'existingDocumentPath', 'editingId', 'showForm', 'vatOverridden']);
        $this->date = now()->format('Y-m-d');
        $this->status = 'pending';
        $this->generateExpenseNumber();
    }

    public function render()
    {
        $query = Expense::with(['category', 'supplier', 'paymentMethod']);

        if ($this->filterCategory) {
            $query->where('expense_category_id', $this->filterCategory);
        }

        if ($this->filterSupplier) {
            $query->where('supplier_id', $this->filterSupplier);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.finance.expenses', [
            'expenses' => $query->latest('date')->latest('id')->paginate(10),
            'categories' => ExpenseCategory::where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('name')->get(),
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('account_name')->get(),
        ]);
    }
}
