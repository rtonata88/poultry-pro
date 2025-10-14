<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Roles extends Component
{
    use WithPagination;

    public $name = '';
    public $selectedPermissions = [];
    public $editingId = null;
    public $showForm = false;
    public $search = '';

    public function mount()
    {
        //
    }

    public function create()
    {
        $this->reset(['name', 'selectedPermissions', 'editingId']);
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $this->editingId,
            'selectedPermissions' => 'array',
        ]);

        // Convert permission IDs to permission names
        $permissionNames = Permission::whereIn('id', $this->selectedPermissions)->pluck('name')->toArray();

        if ($this->editingId) {
            $role = Role::findOrFail($this->editingId);
            $role->update(['name' => $this->name]);
            $role->syncPermissions($permissionNames);
            session()->flash('status', 'Role updated successfully.');
        } else {
            $role = Role::create(['name' => $this->name]);
            $role->syncPermissions($permissionNames);
            session()->flash('status', 'Role created successfully.');
        }

        $this->cancel();
    }

    public function edit($id): void
    {
        $role = Role::with('permissions')->findOrFail($id);
        $this->editingId = $id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->showForm = true;
    }

    public function delete($id): void
    {
        Role::findOrFail($id)->delete();
        session()->flash('status', 'Role deleted successfully.');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'selectedPermissions', 'editingId', 'showForm']);
    }

    public function toggleGroupPermissions($groupName, $groupPermissions)
    {
        $permissionIds = collect($groupPermissions)->pluck('id')->toArray();

        // Check if all permissions in this group are selected
        $allSelected = !array_diff($permissionIds, $this->selectedPermissions);

        if ($allSelected) {
            // Deselect all in this group
            $this->selectedPermissions = array_diff($this->selectedPermissions, $permissionIds);
        } else {
            // Select all in this group
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $permissionIds));
        }
    }

    public function isGroupSelected($groupPermissions)
    {
        $permissionIds = collect($groupPermissions)->pluck('id')->toArray();
        return !array_diff($permissionIds, $this->selectedPermissions);
    }

    public function render()
    {
        $query = Role::withCount('permissions');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        // Group permissions by module
        $allPermissions = Permission::orderBy('name')->get();
        $groupedPermissions = $this->groupPermissions($allPermissions);

        return view('livewire.settings.roles', [
            'roles' => $query->orderBy('name')->paginate(10),
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    private function groupPermissions($permissions)
    {
        $groups = [
            'Dashboards' => [],
            'Operations - Bird Daily Records' => [],
            'Operations - Egg Production' => [],
            'Operations - Egg Dispatches' => [],
            'Operations - Flocks' => [],
            'Operations - Coops' => [],
            'Operations - Farms' => [],
            'Purchases - Suppliers' => [],
            'Purchases - Invoices' => [],
            'Purchases - Payments' => [],
            'Sales - Customers' => [],
            'Sales - Quotations' => [],
            'Sales - Invoices' => [],
            'Sales - Payments' => [],
            'Finance - Bank Accounts' => [],
            'Finance - Account Transfers' => [],
            'Finance - Bank Statements' => [],
            'Finance - Expenses' => [],
            'Finance - VAT Report' => [],
            'Finance - Income Statement' => [],
            'Feed Management - Inventory' => [],
            'Feed Management - Usage' => [],
            'Settings' => [],
        ];

        foreach ($permissions as $permission) {
            $name = $permission->name;

            // Dashboard permissions
            if (str_contains($name, 'dashboard')) {
                $groups['Dashboards'][] = $permission;
            }
            // Bird Daily Records
            elseif (str_contains($name, 'bird-daily-records')) {
                $groups['Operations - Bird Daily Records'][] = $permission;
            }
            // Egg Production
            elseif (str_contains($name, 'egg-production')) {
                $groups['Operations - Egg Production'][] = $permission;
            }
            // Egg Dispatches
            elseif (str_contains($name, 'egg-dispatches')) {
                $groups['Operations - Egg Dispatches'][] = $permission;
            }
            // Flocks
            elseif (str_contains($name, 'flocks')) {
                $groups['Operations - Flocks'][] = $permission;
            }
            // Coops
            elseif (str_contains($name, 'coops')) {
                $groups['Operations - Coops'][] = $permission;
            }
            // Farms
            elseif (str_contains($name, 'farms')) {
                $groups['Operations - Farms'][] = $permission;
            }
            // Suppliers
            elseif (str_contains($name, 'suppliers')) {
                $groups['Purchases - Suppliers'][] = $permission;
            }
            // Purchase Invoices
            elseif (str_contains($name, 'purchase-invoices')) {
                $groups['Purchases - Invoices'][] = $permission;
            }
            // Purchase Payments
            elseif (str_contains($name, 'purchase-payments')) {
                $groups['Purchases - Payments'][] = $permission;
            }
            // Customers
            elseif (str_contains($name, 'customers')) {
                $groups['Sales - Customers'][] = $permission;
            }
            // Quotations
            elseif (str_contains($name, 'quotations')) {
                $groups['Sales - Quotations'][] = $permission;
            }
            // Sales Invoices
            elseif (str_contains($name, 'sales-invoices')) {
                $groups['Sales - Invoices'][] = $permission;
            }
            // Sales Payments
            elseif (str_contains($name, 'sales-payments')) {
                $groups['Sales - Payments'][] = $permission;
            }
            // Bank Accounts
            elseif (str_contains($name, 'bank-accounts')) {
                $groups['Finance - Bank Accounts'][] = $permission;
            }
            // Account Transfers
            elseif (str_contains($name, 'account-transfers')) {
                $groups['Finance - Account Transfers'][] = $permission;
            }
            // Bank Statements
            elseif (str_contains($name, 'bank-statements')) {
                $groups['Finance - Bank Statements'][] = $permission;
            }
            // Expenses
            elseif (str_contains($name, 'expenses')) {
                $groups['Finance - Expenses'][] = $permission;
            }
            // VAT Report
            elseif (str_contains($name, 'vat-report')) {
                $groups['Finance - VAT Report'][] = $permission;
            }
            // Income Statement
            elseif (str_contains($name, 'income-statement')) {
                $groups['Finance - Income Statement'][] = $permission;
            }
            // Feed Inventory
            elseif (str_contains($name, 'feed-inventory')) {
                $groups['Feed Management - Inventory'][] = $permission;
            }
            // Feed Usage
            elseif (str_contains($name, 'feed-usage')) {
                $groups['Feed Management - Usage'][] = $permission;
            }
            // Settings
            elseif (str_contains($name, 'settings') || str_contains($name, 'users') || str_contains($name, 'roles') || str_contains($name, 'company-info')) {
                $groups['Settings'][] = $permission;
            }
        }

        // Remove empty groups
        return array_filter($groups, function($group) {
            return count($group) > 0;
        });
    }
}
