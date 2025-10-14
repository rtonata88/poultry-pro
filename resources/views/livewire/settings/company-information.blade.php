<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Company Information')" :subheading="__('Manage your company details and tax information')">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <flux:text class="text-green-700 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </flux:text>
            </div>
        @endif

        <form wire:submit="save" class="space-y-12">
            <!-- Basic Information Section -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Basic Information') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Essential company details') }}</p>
                </div>

                <div class="space-y-5">
                    <flux:input wire:model="name" :label="__('Company Name')" type="text" required />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <flux:input wire:model="email" :label="__('Email Address')" type="email" />
                        <flux:input wire:model="phone" :label="__('Phone Number')" type="text" />
                    </div>
                </div>
            </div>

            <!-- Address Section -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Address Details') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Company location information') }}</p>
                </div>

                <div class="space-y-5">
                    <flux:input wire:model="address" :label="__('Street Address')" type="text" />

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <flux:input wire:model="city" :label="__('City')" type="text" />
                        <flux:input wire:model="state" :label="__('State / Province')" type="text" />
                        <flux:input wire:model="zip_code" :label="__('Postal Code')" type="text" />
                    </div>

                    <flux:input wire:model="country" :label="__('Country')" type="text" />
                </div>
            </div>

            <!-- Tax Information Section -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 mb-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Tax Information') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Tax identification and VAT settings') }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <flux:input wire:model="tax_number" :label="__('Tax Identification Number')" type="text" />
                    <flux:input wire:model="vat_rate" :label="__('VAT Rate (%)')" type="number" step="0.01" min="0" max="100" />
                </div>
            </div>

            <!-- Banking Details Section -->
            <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Banking Details') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Bank account information for payments') }}</p>
                </div>

                <div class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <flux:input wire:model="bank_name" :label="__('Bank Name')" type="text" />
                        <flux:input wire:model="bank_account_name" :label="__('Account Holder Name')" type="text" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <flux:input wire:model="bank_account_number" :label="__('Account Number')" type="text" />
                        <flux:input wire:model="bank_routing_number" :label="__('Routing Number / Sort Code')" type="text" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <flux:input wire:model="bank_swift_code" :label="__('SWIFT / BIC Code')" type="text" />
                        <flux:input wire:model="bank_iban" :label="__('IBAN')" type="text" />
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Changes will be saved to your company profile') }}
                </flux:text>
                <flux:button variant="primary" type="submit">
                    {{ __('Save Changes') }}
                </flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>
