<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('settings.profile')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('settings.password')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <flux:navlist.item :href="route('two-factor.show')" wire:navigate>{{ __('Two-Factor Auth') }}</flux:navlist.item>
            @endif
            <flux:navlist.item :href="route('settings.appearance')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>

            <flux:navlist.group heading="{{ __('Business Settings') }}">
                <flux:navlist.item :href="route('settings.company')" wire:navigate>{{ __('Company Information') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.products')" wire:navigate>{{ __('Products') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.expense-categories')" wire:navigate>{{ __('Expense Categories') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.vendor-categories')" wire:navigate>{{ __('Vendor Categories') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.payment-methods')" wire:navigate>{{ __('Payment Methods') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group heading="{{ __('Access Control') }}">
                <flux:navlist.item :href="route('settings.users')" wire:navigate>{{ __('Users') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.roles')" wire:navigate>{{ __('Roles') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.permissions')" wire:navigate>{{ __('Permissions') }}</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
