<?php

namespace App\Livewire\Finance;

use App\Models\BankAccount;
use Livewire\Component;
use Livewire\WithPagination;

class BankAccounts extends Component
{
    use WithPagination;

    public $account_name = '';
    public $account_number = '';
    public $bank_name = '';
    public $branch = '';
    public $swift_code = '';
    public $iban = '';
    public $account_type = 'checking';
    public $currency = 'NAD';
    public $opening_balance = 0;
    public $current_balance = 0;
    public $is_active = true;
    public $notes = '';
    public $editingId = null;
    public $showForm = false;
    public $filterStatus = '';

    public function save(): void
    {
        $this->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255|unique:bank_accounts,account_number,' . $this->editingId,
            'bank_name' => 'required|string|max:255',
            'branch' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:255',
            'account_type' => 'required|in:checking,savings,business,other',
            'currency' => 'required|string|size:3',
            'opening_balance' => 'required|numeric|min:0',
            'current_balance' => 'required|numeric',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $accountData = [
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'bank_name' => $this->bank_name,
            'branch' => $this->branch,
            'swift_code' => $this->swift_code,
            'iban' => $this->iban,
            'account_type' => $this->account_type,
            'currency' => $this->currency,
            'opening_balance' => $this->opening_balance,
            'current_balance' => $this->current_balance,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
        ];

        if ($this->editingId) {
            BankAccount::find($this->editingId)->update($accountData);
            session()->flash('status', 'Bank account updated successfully.');
        } else {
            BankAccount::create($accountData);
            session()->flash('status', 'Bank account created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $account = BankAccount::findOrFail($id);
        $this->editingId = $id;
        $this->account_name = $account->account_name;
        $this->account_number = $account->account_number;
        $this->bank_name = $account->bank_name;
        $this->branch = $account->branch ?? '';
        $this->swift_code = $account->swift_code ?? '';
        $this->iban = $account->iban ?? '';
        $this->account_type = $account->account_type;
        $this->currency = $account->currency;
        $this->opening_balance = $account->opening_balance;
        $this->current_balance = $account->current_balance;
        $this->is_active = $account->is_active;
        $this->notes = $account->notes ?? '';
        $this->showForm = true;
    }

    public function delete($id): void
    {
        BankAccount::findOrFail($id)->delete();
        session()->flash('status', 'Bank account deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['account_name', 'account_number', 'bank_name', 'branch', 'swift_code', 'iban', 'account_type', 'currency', 'opening_balance', 'current_balance', 'is_active', 'notes', 'editingId', 'showForm']);
        $this->account_type = 'checking';
        $this->currency = 'NAD';
        $this->is_active = true;
        $this->opening_balance = 0;
        $this->current_balance = 0;
    }

    public function render()
    {
        $query = BankAccount::query();

        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus);
        }

        return view('livewire.finance.bank-accounts', [
            'bankAccounts' => $query->latest()->paginate(10),
        ]);
    }
}
