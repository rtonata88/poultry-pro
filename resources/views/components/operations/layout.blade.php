<div class="w-full">
    <flux:heading>{{ $heading ?? '' }}</flux:heading>
    <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

    <div class="mt-5 w-full">
        {{ $slot }}
    </div>
</div>
