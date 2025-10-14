<?php

namespace App\Livewire\Settings;

use App\Models\CompanyInformation as CompanyInformationModel;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompanyInformation extends Component
{
    use WithFileUploads;

    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $city = '';
    public $state = '';
    public $zip_code = '';
    public $country = '';
    public $tax_number = '';
    public $vat_rate = '';
    public $logo;
    public $bank_name = '';
    public $bank_account_name = '';
    public $bank_account_number = '';
    public $bank_routing_number = '';
    public $bank_swift_code = '';
    public $bank_iban = '';
    public $companyId = null;

    public function mount(): void
    {
        $company = CompanyInformationModel::first();

        if ($company) {
            $this->companyId = $company->id;
            $this->name = $company->name;
            $this->email = $company->email ?? '';
            $this->phone = $company->phone ?? '';
            $this->address = $company->address ?? '';
            $this->city = $company->city ?? '';
            $this->state = $company->state ?? '';
            $this->zip_code = $company->zip_code ?? '';
            $this->country = $company->country ?? '';
            $this->tax_number = $company->tax_number ?? '';
            $this->vat_rate = $company->vat_rate ?? '';
            $this->bank_name = $company->bank_name ?? '';
            $this->bank_account_name = $company->bank_account_name ?? '';
            $this->bank_account_number = $company->bank_account_number ?? '';
            $this->bank_routing_number = $company->bank_routing_number ?? '';
            $this->bank_swift_code = $company->bank_swift_code ?? '';
            $this->bank_iban = $company->bank_iban ?? '';
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'bank_routing_number' => 'nullable|string|max:255',
            'bank_swift_code' => 'nullable|string|max:255',
            'bank_iban' => 'nullable|string|max:255',
        ]);

        if ($this->companyId) {
            $company = CompanyInformationModel::find($this->companyId);
            $company->update($validated);
        } else {
            CompanyInformationModel::create($validated);
        }

        $this->dispatch('company-updated');
        session()->flash('status', 'Company information saved successfully.');
    }

    public function render()
    {
        return view('livewire.settings.company-information');
    }
}
