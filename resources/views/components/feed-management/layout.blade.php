<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.group heading="{{ __('Inventory') }}">
                <flux:navlist.item :href="route('feed.receipts')" wire:navigate>{{ __('Receipts') }}</flux:navlist.item>
                <flux:navlist.item :href="route('feed.stock-levels')" wire:navigate>{{ __('Stock Levels') }}</flux:navlist.item>
                <flux:navlist.item :href="route('feed.types')" wire:navigate>{{ __('Feed Types') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group heading="{{ __('Usage') }}">
                <flux:navlist.item :href="route('operations.feed-usage')" wire:navigate>{{ __('Daily Usage') }}</flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full">
            {{ $slot }}
        </div>
    </div>
</div>
