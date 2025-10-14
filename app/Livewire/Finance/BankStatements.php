<?php

namespace App\Livewire\Finance;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Url;

class BankStatements extends Component
{
    #[Url]
    public $bank_account_id = '';
    public $start_date = '';
    public $end_date = '';
    public $transaction_type = '';

    public function mount()
    {
        // Default to current month
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
    }

    public function downloadPdf()
    {
        if (!$this->bank_account_id) {
            return;
        }

        $selectedAccount = BankAccount::findOrFail($this->bank_account_id);

        // Get opening balance
        $lastTransactionBeforeStart = BankTransaction::where('bank_account_id', $this->bank_account_id)
            ->where('transaction_date', '<', $this->start_date)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $openingBalance = $lastTransactionBeforeStart
            ? $lastTransactionBeforeStart->balance_after
            : $selectedAccount->opening_balance;

        // Get transactions
        $query = BankTransaction::where('bank_account_id', $this->bank_account_id)
            ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
            ->with('transactionable');

        if ($this->transaction_type) {
            $query->where('transaction_type', $this->transaction_type);
        }

        $transactions = $query->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        // Calculate totals
        $totalIn = 0;
        $totalOut = 0;
        foreach ($transactions as $transaction) {
            if ($transaction->amount > 0) {
                $totalIn += $transaction->amount;
            } else {
                $totalOut += abs($transaction->amount);
            }
        }

        $closingBalance = $transactions->isNotEmpty()
            ? $transactions->last()->balance_after
            : $openingBalance;

        $pdf = Pdf::loadView('pdf.bank-statement', [
            'account' => $selectedAccount,
            'startDate' => $this->start_date,
            'endDate' => $this->end_date,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'bank-statement-' . $selectedAccount->account_name . '-' . now()->format('Y-m-d') . '.pdf');
    }

    public function render()
    {
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('account_name')->get();

        $transactions = collect();
        $openingBalance = 0;
        $closingBalance = 0;
        $totalIn = 0;
        $totalOut = 0;
        $selectedAccount = null;

        if ($this->bank_account_id) {
            $selectedAccount = BankAccount::find($this->bank_account_id);

            // Get opening balance (balance before start date)
            $lastTransactionBeforeStart = BankTransaction::where('bank_account_id', $this->bank_account_id)
                ->where('transaction_date', '<', $this->start_date)
                ->orderBy('transaction_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $openingBalance = $lastTransactionBeforeStart
                ? $lastTransactionBeforeStart->balance_after
                : $selectedAccount->opening_balance;

            // Get transactions for date range
            $query = BankTransaction::where('bank_account_id', $this->bank_account_id)
                ->whereBetween('transaction_date', [$this->start_date, $this->end_date])
                ->with('transactionable');

            if ($this->transaction_type) {
                $query->where('transaction_type', $this->transaction_type);
            }

            $transactions = $query->orderBy('transaction_date')
                ->orderBy('id')
                ->get();

            // Calculate totals
            foreach ($transactions as $transaction) {
                if ($transaction->amount > 0) {
                    $totalIn += $transaction->amount;
                } else {
                    $totalOut += abs($transaction->amount);
                }
            }

            // Get closing balance
            $closingBalance = $transactions->isNotEmpty()
                ? $transactions->last()->balance_after
                : $openingBalance;
        }

        return view('livewire.finance.bank-statements', [
            'bankAccounts' => $bankAccounts,
            'transactions' => $transactions,
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'selectedAccount' => $selectedAccount,
        ]);
    }
}
