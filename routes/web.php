<?php

use App\Livewire\Dashboard\Finance;
use App\Livewire\Dashboard\Operations as OperationsDashboard;
use App\Livewire\Operations\BirdDailyRecords;
use App\Livewire\Operations\Coops;
use App\Livewire\Operations\EggDispatches;
use App\Livewire\Operations\EggProduction;
use App\Livewire\Operations\Farms;
use App\Livewire\Operations\FeedInventory;
use App\Livewire\Operations\FeedUsage;
use App\Livewire\Operations\Flocks;
use App\Livewire\FeedManagement\FeedTypes;
use App\Livewire\FeedManagement\StockLevels;
use App\Livewire\FeedManagement\FeedReceipts;
use App\Livewire\Purchases\Suppliers;
use App\Livewire\Purchases\SupplierInvoices;
use App\Livewire\Purchases\SupplierInvoice;
use App\Livewire\Purchases\SupplierPayments;
use App\Livewire\Purchases\SupplierStatement;
use App\Livewire\Sales\Customers;
use App\Livewire\Sales\CustomerQuotations;
use App\Livewire\Sales\CustomerInvoices;
use App\Livewire\Sales\CustomerPayments;
use App\Livewire\Sales\CustomerStatement;
use App\Livewire\Finance\AccountTransfers;
use App\Livewire\Finance\BankAccounts;
use App\Livewire\Finance\BankStatements;
use App\Livewire\Finance\Expenses;
use App\Livewire\Finance\VatReport;
use App\Livewire\Finance\IncomeStatement;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\CompanyInformation;
use App\Livewire\Settings\ExpenseCategories;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\PaymentMethods;
use App\Livewire\Settings\Permissions;
use App\Livewire\Settings\Products;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Roles;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Settings\Users;
use App\Livewire\Settings\VendorCategories;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', 'dashboard/operations')->name('dashboard');
    Route::get('dashboard/operations', OperationsDashboard::class)->middleware('permission:view-operations-dashboard')->name('dashboard.operations');
    Route::get('dashboard/finance', Finance::class)->middleware('permission:view-finance-dashboard')->name('dashboard.finance');
});

Route::middleware(['auth'])->group(function () {
    // Operations Routes
    Route::get('operations/farms', Farms::class)->middleware('permission:view-farms')->name('operations.farms');
    Route::get('operations/coops', Coops::class)->middleware('permission:view-coops')->name('operations.coops');
    Route::get('operations/flocks', Flocks::class)->middleware('permission:view-flocks')->name('operations.flocks');
    Route::get('operations/bird-records', BirdDailyRecords::class)->middleware('permission:view-bird-daily-records')->name('operations.bird-records');
    Route::get('operations/feed-inventory', FeedInventory::class)->middleware('permission:view-feed-inventory')->name('operations.feed-inventory');
    Route::get('operations/feed-usage', FeedUsage::class)->middleware('permission:view-feed-usage')->name('operations.feed-usage');
    Route::get('operations/egg-production', EggProduction::class)->middleware('permission:view-egg-production')->name('operations.egg-production');
    Route::get('operations/egg-dispatches', EggDispatches::class)->middleware('permission:view-egg-dispatches')->name('operations.egg-dispatches');

    // Feed Management Routes
    Route::redirect('feed', 'feed/receipts');
    Route::get('feed/types', FeedTypes::class)->middleware('permission:view-feed-inventory')->name('feed.types');
    Route::get('feed/stock-levels', StockLevels::class)->middleware('permission:view-feed-inventory')->name('feed.stock-levels');
    Route::get('feed/receipts', FeedReceipts::class)->middleware('permission:view-feed-inventory')->name('feed.receipts');

    // Purchases Routes
    Route::redirect('purchases', 'purchases/suppliers');
    Route::get('purchases/suppliers', Suppliers::class)->middleware('permission:view-suppliers')->name('purchases.suppliers');
    Route::get('purchases/suppliers/{supplierId}/statement', SupplierStatement::class)->middleware('permission:view-suppliers')->name('purchases.supplier-statement');
    Route::get('purchases/invoices', SupplierInvoices::class)->middleware('permission:view-purchase-invoices')->name('purchases.invoices');
    Route::get('purchases/invoices/{invoiceId}', SupplierInvoice::class)->middleware('permission:view-purchase-invoices')->name('purchases.supplier-invoice');
    Route::get('purchases/payments', SupplierPayments::class)->middleware('permission:view-purchase-payments')->name('purchases.payments');

    // Sales Routes
    Route::redirect('sales', 'sales/customers');
    Route::get('sales/customers', Customers::class)->middleware('permission:view-customers')->name('sales.customers');
    Route::get('sales/customers/{customerId}/statement', CustomerStatement::class)->middleware('permission:view-customers')->name('sales.customer-statement');
    Route::get('sales/quotations', CustomerQuotations::class)->middleware('permission:view-quotations')->name('sales.quotations');
    Route::get('sales/invoices', CustomerInvoices::class)->middleware('permission:view-sales-invoices')->name('sales.invoices');
    Route::get('sales/payments', CustomerPayments::class)->middleware('permission:view-sales-payments')->name('sales.payments');

    // Finance Routes
    Route::redirect('finance', 'finance/bank-accounts');
    Route::get('finance/bank-accounts', BankAccounts::class)->middleware('permission:view-bank-accounts')->name('finance.bank-accounts');
    Route::get('finance/account-transfers', AccountTransfers::class)->middleware('permission:view-account-transfers')->name('finance.account-transfers');
    Route::get('finance/bank-statements', BankStatements::class)->middleware('permission:view-bank-statements')->name('finance.bank-statements');
    Route::get('finance/expenses', Expenses::class)->middleware('permission:view-expenses')->name('finance.expenses');
    Route::get('finance/vat-report', VatReport::class)->middleware('permission:view-vat-report')->name('finance.vat-report');
    Route::get('finance/income-statement', IncomeStatement::class)->middleware('permission:view-income-statement')->name('finance.income-statement');

    // Settings Routes
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->middleware('permission:edit-own-profile')->name('settings.profile');
    Route::get('settings/password', Password::class)->middleware('permission:edit-own-profile')->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->middleware('permission:edit-own-profile')->name('settings.appearance');

    Route::get('settings/company', CompanyInformation::class)->middleware('permission:view-company-info')->name('settings.company');
    Route::get('settings/products', Products::class)->middleware('permission:edit-settings')->name('settings.products');
    Route::get('settings/expense-categories', ExpenseCategories::class)->middleware('permission:edit-settings')->name('settings.expense-categories');
    Route::get('settings/vendor-categories', VendorCategories::class)->middleware('permission:edit-settings')->name('settings.vendor-categories');
    Route::get('settings/payment-methods', PaymentMethods::class)->middleware('permission:edit-settings')->name('settings.payment-methods');

    // Access Control Routes
    Route::get('settings/users', Users::class)->middleware('permission:manage-users')->name('settings.users');
    Route::get('settings/roles', Roles::class)->middleware('permission:manage-roles')->name('settings.roles');
    Route::get('settings/permissions', Permissions::class)->middleware('permission:manage-roles')->name('settings.permissions');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

require __DIR__.'/auth.php';
