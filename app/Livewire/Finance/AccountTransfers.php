<?php

namespace App\Livewire\Finance;

use App\Models\AccountTransfer;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AccountTransfers extends Component
{
    use WithPagination;

    public $transfer_number = '';
    public $from_account_id = '';
    public $to_account_id = '';
    public $amount = 0;
    public $transfer_date = '';
    public $reference = '';
    public $notes = '';
    public $status = 'pending';
    public $editingId = null;
    public $showForm = false;
    public $filterStatus = '';
    public $filterFromAccount = '';
    public $filterToAccount = '';

    public function mount()
    {
        $this->transfer_date = now()->format('Y-m-d');
        $this->generateTransferNumber();
    }

    public function generateTransferNumber()
    {
        $lastTransfer = AccountTransfer::latest('id')->first();
        $nextNumber = $lastTransfer ? ((int) substr($lastTransfer->transfer_number, 4)) + 1 : 1;
        $this->transfer_number = 'TRF-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function save(): void
    {
        $this->validate([
            'transfer_number' => 'required|string|max:255|unique:account_transfers,transfer_number,' . $this->editingId,
            'from_account_id' => 'required|exists:bank_accounts,id|different:to_account_id',
            'to_account_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'transfer_date' => 'required|date',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,completed,cancelled',
        ], [
            'from_account_id.different' => 'Source and destination accounts must be different.',
        ]);

        DB::transaction(function () {
            $transferData = [
                'transfer_number' => $this->transfer_number,
                'from_account_id' => $this->from_account_id,
                'to_account_id' => $this->to_account_id,
                'amount' => $this->amount,
                'transfer_date' => $this->transfer_date,
                'reference' => $this->reference,
                'notes' => $this->notes,
                'status' => $this->status,
            ];

            if ($this->editingId) {
                $transfer = AccountTransfer::findOrFail($this->editingId);
                $oldStatus = $transfer->status;

                // Reverse previous balance changes and delete transactions if status was completed
                if ($oldStatus === 'completed') {
                    $fromAccount = BankAccount::find($transfer->from_account_id);
                    $toAccount = BankAccount::find($transfer->to_account_id);
                    $fromAccount->increment('current_balance', $transfer->amount);
                    $toAccount->decrement('current_balance', $transfer->amount);

                    // Delete old transaction records
                    $transfer->bankTransactions()->delete();
                }

                $transfer->update($transferData);

                // Apply new balance changes and create transactions if status is completed
                if ($this->status === 'completed') {
                    $fromAccount = BankAccount::findOrFail($this->from_account_id);
                    $toAccount = BankAccount::findOrFail($this->to_account_id);

                    $fromAccount->decrement('current_balance', $this->amount);
                    $toAccount->increment('current_balance', $this->amount);

                    // Create transfer-out transaction
                    BankTransaction::create([
                        'bank_account_id' => $this->from_account_id,
                        'transaction_type' => 'transfer_out',
                        'transactionable_type' => AccountTransfer::class,
                        'transactionable_id' => $transfer->id,
                        'amount' => -$this->amount,
                        'transaction_date' => $this->transfer_date,
                        'description' => 'Transfer Out: ' . $this->transfer_number . ' to ' . $toAccount->account_name,
                        'balance_after' => $fromAccount->current_balance,
                        'reference' => $this->reference,
                    ]);

                    // Create transfer-in transaction
                    BankTransaction::create([
                        'bank_account_id' => $this->to_account_id,
                        'transaction_type' => 'transfer_in',
                        'transactionable_type' => AccountTransfer::class,
                        'transactionable_id' => $transfer->id,
                        'amount' => $this->amount,
                        'transaction_date' => $this->transfer_date,
                        'description' => 'Transfer In: ' . $this->transfer_number . ' from ' . $fromAccount->account_name,
                        'balance_after' => $toAccount->current_balance,
                        'reference' => $this->reference,
                    ]);
                }

                session()->flash('status', 'Transfer updated successfully.');
            } else {
                $transfer = AccountTransfer::create($transferData);

                // Update account balances and create transactions if status is completed
                if ($this->status === 'completed') {
                    $fromAccount = BankAccount::findOrFail($this->from_account_id);
                    $toAccount = BankAccount::findOrFail($this->to_account_id);

                    $fromAccount->decrement('current_balance', $this->amount);
                    $toAccount->increment('current_balance', $this->amount);

                    // Create transfer-out transaction
                    BankTransaction::create([
                        'bank_account_id' => $this->from_account_id,
                        'transaction_type' => 'transfer_out',
                        'transactionable_type' => AccountTransfer::class,
                        'transactionable_id' => $transfer->id,
                        'amount' => -$this->amount,
                        'transaction_date' => $this->transfer_date,
                        'description' => 'Transfer Out: ' . $this->transfer_number . ' to ' . $toAccount->account_name,
                        'balance_after' => $fromAccount->current_balance,
                        'reference' => $this->reference,
                    ]);

                    // Create transfer-in transaction
                    BankTransaction::create([
                        'bank_account_id' => $this->to_account_id,
                        'transaction_type' => 'transfer_in',
                        'transactionable_type' => AccountTransfer::class,
                        'transactionable_id' => $transfer->id,
                        'amount' => $this->amount,
                        'transaction_date' => $this->transfer_date,
                        'description' => 'Transfer In: ' . $this->transfer_number . ' from ' . $fromAccount->account_name,
                        'balance_after' => $toAccount->current_balance,
                        'reference' => $this->reference,
                    ]);
                }

                session()->flash('status', 'Transfer created successfully.');
            }
        });

        $this->cancel();
    }

    public function edit($id): void
    {
        $transfer = AccountTransfer::findOrFail($id);
        $this->editingId = $id;
        $this->transfer_number = $transfer->transfer_number;
        $this->from_account_id = $transfer->from_account_id;
        $this->to_account_id = $transfer->to_account_id;
        $this->amount = $transfer->amount;
        $this->transfer_date = $transfer->transfer_date->format('Y-m-d');
        $this->reference = $transfer->reference ?? '';
        $this->notes = $transfer->notes ?? '';
        $this->status = $transfer->status;
        $this->showForm = true;
    }

    public function delete($id): void
    {
        DB::transaction(function () use ($id) {
            $transfer = AccountTransfer::findOrFail($id);

            // Reverse balance changes and delete transactions if transfer was completed
            if ($transfer->status === 'completed') {
                $fromAccount = BankAccount::find($transfer->from_account_id);
                $toAccount = BankAccount::find($transfer->to_account_id);
                $fromAccount->increment('current_balance', $transfer->amount);
                $toAccount->decrement('current_balance', $transfer->amount);

                // Delete transaction records
                $transfer->bankTransactions()->delete();
            }

            $transfer->delete();
        });

        session()->flash('status', 'Transfer deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['transfer_number', 'from_account_id', 'to_account_id', 'amount', 'transfer_date', 'reference', 'notes', 'status', 'editingId', 'showForm']);
        $this->transfer_date = now()->format('Y-m-d');
        $this->status = 'pending';
        $this->generateTransferNumber();
    }

    public function render()
    {
        $query = AccountTransfer::with(['fromAccount', 'toAccount']);

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterFromAccount) {
            $query->where('from_account_id', $this->filterFromAccount);
        }

        if ($this->filterToAccount) {
            $query->where('to_account_id', $this->filterToAccount);
        }

        return view('livewire.finance.account-transfers', [
            'transfers' => $query->latest('transfer_date')->latest('id')->paginate(10),
            'bankAccounts' => BankAccount::where('is_active', true)->orderBy('account_name')->get(),
        ]);
    }
}
