<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard.operations') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Dashboards')" class="grid">
                    @can('view-operations-dashboard')
                        <flux:navlist.item icon="chart-bar" :href="route('dashboard.operations')" :current="request()->routeIs('dashboard.operations')" wire:navigate>{{ __('Operations') }}</flux:navlist.item>
                    @endcan
                    @can('view-finance-dashboard')
                        <flux:navlist.item icon="banknotes" :href="route('dashboard.finance')" :current="request()->routeIs('dashboard.finance')" wire:navigate>{{ __('Finance') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>

                @if(auth()->user()->can('view-bird-daily-records') || auth()->user()->can('view-egg-production') || auth()->user()->can('view-egg-dispatches') || auth()->user()->can('view-flocks') || auth()->user()->can('view-coops') || auth()->user()->can('view-farms'))
                <flux:navlist.group :heading="__('Operations')" class="grid">
                    @can('view-bird-daily-records')
                        <flux:navlist.item icon="clipboard-document-list" :href="route('operations.bird-records')" :current="request()->routeIs('operations.bird-records')" wire:navigate>{{ __('Daily Records') }}</flux:navlist.item>
                    @endcan
                    @can('view-egg-production')
                        <flux:navlist.item icon="circle-stack" :href="route('operations.egg-production')" :current="request()->routeIs('operations.egg-production')" wire:navigate>{{ __('Egg Production') }}</flux:navlist.item>
                    @endcan
                    @can('view-egg-dispatches')
                        <flux:navlist.item icon="truck" :href="route('operations.egg-dispatches')" :current="request()->routeIs('operations.egg-dispatches')" wire:navigate>{{ __('Egg Dispatches') }}</flux:navlist.item>
                    @endcan
                    @can('view-flocks')
                        <flux:navlist.item icon="user-group" :href="route('operations.flocks')" :current="request()->routeIs('operations.flocks')" wire:navigate>{{ __('Flocks') }}</flux:navlist.item>
                    @endcan
                    @can('view-coops')
                        <flux:navlist.item icon="building-office" :href="route('operations.coops')" :current="request()->routeIs('operations.coops')" wire:navigate>{{ __('Coops') }}</flux:navlist.item>
                    @endcan
                    @can('view-farms')
                        <flux:navlist.item icon="map-pin" :href="route('operations.farms')" :current="request()->routeIs('operations.farms')" wire:navigate>{{ __('Farms') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
                @endif

                @if(auth()->user()->can('view-suppliers') || auth()->user()->can('view-purchase-invoices') || auth()->user()->can('view-purchase-payments'))
                <flux:navlist.group :heading="__('Purchases')" class="grid">
                    @can('view-suppliers')
                        <flux:navlist.item icon="user-group" :href="route('purchases.suppliers')" :current="request()->routeIs('purchases.suppliers')" wire:navigate>{{ __('Suppliers') }}</flux:navlist.item>
                    @endcan
                    @can('view-purchase-invoices')
                        <flux:navlist.item icon="document-text" :href="route('purchases.invoices')" :current="request()->routeIs('purchases.invoices')" wire:navigate>{{ __('Invoices') }}</flux:navlist.item>
                    @endcan
                    @can('view-purchase-payments')
                        <flux:navlist.item icon="banknotes" :href="route('purchases.payments')" :current="request()->routeIs('purchases.payments')" wire:navigate>{{ __('Payments') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
                @endif

                @if(auth()->user()->can('view-customers') || auth()->user()->can('view-quotations') || auth()->user()->can('view-sales-invoices') || auth()->user()->can('view-sales-payments'))
                <flux:navlist.group :heading="__('Sales')" class="grid">
                    @can('view-customers')
                        <flux:navlist.item icon="users" :href="route('sales.customers')" :current="request()->routeIs('sales.customers')" wire:navigate>{{ __('Customers') }}</flux:navlist.item>
                    @endcan
                    @can('view-quotations')
                        <flux:navlist.item icon="document-duplicate" :href="route('sales.quotations')" :current="request()->routeIs('sales.quotations')" wire:navigate>{{ __('Quotations') }}</flux:navlist.item>
                    @endcan
                    @can('view-sales-invoices')
                        <flux:navlist.item icon="document-text" :href="route('sales.invoices')" :current="request()->routeIs('sales.invoices')" wire:navigate>{{ __('Invoices') }}</flux:navlist.item>
                    @endcan
                    @can('view-sales-payments')
                        <flux:navlist.item icon="credit-card" :href="route('sales.payments')" :current="request()->routeIs('sales.payments')" wire:navigate>{{ __('Payments') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
                @endif

                @if(auth()->user()->can('view-bank-accounts') || auth()->user()->can('view-account-transfers') || auth()->user()->can('view-bank-statements') || auth()->user()->can('view-expenses') || auth()->user()->can('view-vat-report') || auth()->user()->can('view-income-statement'))
                <flux:navlist.group :heading="__('Finance')" class="grid">
                    @can('view-bank-accounts')
                        <flux:navlist.item icon="building-library" :href="route('finance.bank-accounts')" :current="request()->routeIs('finance.bank-accounts')" wire:navigate>{{ __('Bank Accounts') }}</flux:navlist.item>
                    @endcan
                    @can('view-account-transfers')
                        <flux:navlist.item icon="arrow-path" :href="route('finance.account-transfers')" :current="request()->routeIs('finance.account-transfers')" wire:navigate>{{ __('Account Transfers') }}</flux:navlist.item>
                    @endcan
                    @can('view-bank-statements')
                        <flux:navlist.item icon="document-text" :href="route('finance.bank-statements')" :current="request()->routeIs('finance.bank-statements')" wire:navigate>{{ __('Bank Statements') }}</flux:navlist.item>
                    @endcan
                    @can('view-expenses')
                        <flux:navlist.item icon="receipt-percent" :href="route('finance.expenses')" :current="request()->routeIs('finance.expenses')" wire:navigate>{{ __('Expense Tracking') }}</flux:navlist.item>
                    @endcan
                    @can('view-vat-report')
                        <flux:navlist.item icon="document-chart-bar" :href="route('finance.vat-report')" :current="request()->routeIs('finance.vat-report')" wire:navigate>{{ __('VAT Report') }}</flux:navlist.item>
                    @endcan
                    @can('view-income-statement')
                        <flux:navlist.item icon="chart-bar" :href="route('finance.income-statement')" :current="request()->routeIs('finance.income-statement')" wire:navigate>{{ __('Income Statement') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
                @endif

                @if(auth()->user()->can('view-feed-inventory') || auth()->user()->can('view-feed-usage'))
                <flux:navlist.group :heading="__('Feed Management')" class="grid">
                    @can('view-feed-inventory')
                        <flux:navlist.item icon="archive-box" :href="route('feed.types')" :current="request()->routeIs('feed.*')" wire:navigate>{{ __('Inventory') }}</flux:navlist.item>
                    @endcan
                    @can('view-feed-usage')
                        <flux:navlist.item icon="document-text" :href="route('operations.feed-usage')" :current="request()->routeIs('operations.feed-usage')" wire:navigate>{{ __('Usage') }}</flux:navlist.item>
                    @endcan
                </flux:navlist.group>
                @endif

                @if(auth()->user()->can('edit-own-profile') || auth()->user()->can('view-settings'))
                <flux:navlist.group :heading="__('Settings')" class="grid">
                    <flux:navlist.item icon="cog" :href="route('settings.profile')" :current="request()->routeIs('settings.*')" wire:navigate>{{ __('Settings') }}</flux:navlist.item>
                </flux:navlist.group>
                @endif
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    @if(auth()->user()->can('edit-own-profile') || auth()->user()->can('view-settings'))
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    @if(auth()->user()->can('edit-own-profile') || auth()->user()->can('view-settings'))
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        <flux:footer class="border-t border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
                A product of AST Software Investment CC
            </div>
        </flux:footer>

        @fluxScripts
    </body>
</html>
