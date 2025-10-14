<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboards
            'view-operations-dashboard',
            'view-finance-dashboard',

            // Operations - Bird Daily Records
            'view-bird-daily-records',
            'create-bird-daily-records',
            'edit-bird-daily-records',
            'delete-bird-daily-records',

            // Operations - Egg Production
            'view-egg-production',
            'create-egg-production',
            'edit-egg-production',
            'delete-egg-production',

            // Operations - Egg Dispatches
            'view-egg-dispatches',
            'create-egg-dispatches',
            'edit-egg-dispatches',
            'delete-egg-dispatches',

            // Operations - Flocks
            'view-flocks',
            'create-flocks',
            'edit-flocks',
            'delete-flocks',

            // Operations - Coops
            'view-coops',
            'create-coops',
            'edit-coops',
            'delete-coops',

            // Operations - Farms
            'view-farms',
            'create-farms',
            'edit-farms',
            'delete-farms',

            // Purchases - Suppliers
            'view-suppliers',
            'create-suppliers',
            'edit-suppliers',
            'delete-suppliers',

            // Purchases - Invoices
            'view-purchase-invoices',
            'create-purchase-invoices',
            'edit-purchase-invoices',
            'delete-purchase-invoices',

            // Purchases - Payments
            'view-purchase-payments',
            'create-purchase-payments',
            'edit-purchase-payments',
            'delete-purchase-payments',

            // Sales - Customers
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',

            // Sales - Quotations
            'view-quotations',
            'create-quotations',
            'edit-quotations',
            'delete-quotations',

            // Sales - Invoices
            'view-sales-invoices',
            'create-sales-invoices',
            'edit-sales-invoices',
            'delete-sales-invoices',

            // Sales - Payments
            'view-sales-payments',
            'create-sales-payments',
            'edit-sales-payments',
            'delete-sales-payments',

            // Finance - Bank Accounts
            'view-bank-accounts',
            'create-bank-accounts',
            'edit-bank-accounts',
            'delete-bank-accounts',

            // Finance - Account Transfers
            'view-account-transfers',
            'create-account-transfers',
            'edit-account-transfers',
            'delete-account-transfers',

            // Finance - Bank Statements
            'view-bank-statements',

            // Finance - Expenses
            'view-expenses',
            'create-expenses',
            'edit-expenses',
            'delete-expenses',

            // Finance - VAT Report
            'view-vat-report',
            'export-vat-report',

            // Finance - Income Statement
            'view-income-statement',
            'export-income-statement',

            // Feed Management - Inventory
            'view-feed-inventory',
            'create-feed-inventory',
            'edit-feed-inventory',
            'delete-feed-inventory',

            // Feed Management - Usage
            'view-feed-usage',
            'create-feed-usage',
            'edit-feed-usage',
            'delete-feed-usage',

            // Settings - Profile & Account
            'edit-own-profile',

            // Settings - Administrative
            'view-settings',
            'edit-settings',
            'manage-users',
            'manage-roles',
            'view-company-info',
            'edit-company-info',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info('Permissions created successfully!');
        $this->command->info('Total permissions: ' . count($permissions));
    }
}
