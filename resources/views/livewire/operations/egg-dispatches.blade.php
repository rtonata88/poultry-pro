<section class="w-full">
    <x-operations.layout :heading="__('Egg Dispatches')" :subheading="__('Track egg dispatches and sales')">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <flux:text class="text-green-700 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </flux:text>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Dispatch Records') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Manage egg dispatches for owner consumption and sales') }}</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <flux:select wire:model.live="filterFarm" placeholder="{{ __('Filter by farm') }}" class="flex-1">
                        <option value="">{{ __('All Farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}">
                                {{ $farm->name }} ({{ number_format($farm->available_stock) }} eggs available)
                            </option>
                        @endforeach
                    </flux:select>

                    @if (!$showForm)
                        <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                            {{ __('Add Dispatch') }}
                        </flux:button>
                    @endif
                </div>
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Dispatch Record') : __('New Dispatch Record') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4 sm:space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:select wire:model.live="farm_id" :label="__('Farm')" required class="sm:col-span-2">
                                <option value="">{{ __('Select farm') }}</option>
                                @foreach ($farms as $farm)
                                    <option value="{{ $farm->id }}">
                                        {{ $farm->name }}
                                        (Available: {{ number_format($farm->available_stock) }} eggs / {{ $farm->available_trays }} trays)
                                    </option>
                                @endforeach
                            </flux:select>

                            <flux:input wire:model.live="date" :label="__('Dispatch Date')" type="date" required max="{{ now()->format('Y-m-d') }}" />

                            <flux:input wire:model.live="quantity" :label="__('Quantity')" type="number" min="1" required placeholder="Number of eggs" />

                            <flux:select wire:model.live="dispatch_type" :label="__('Dispatch Type')" required>
                                <option value="owner_consumption">{{ __('Owner Consumption') }}</option>
                                <option value="sale">{{ __('Sale') }}</option>
                            </flux:select>
                        </div>

                        <flux:input wire:model="dispatch_reason" :label="__('Dispatch Reason')" type="text" placeholder="e.g., Home use, Staff consumption, Gift" />

                        <flux:input wire:model="recipient_name" :label="__('Recipient Name')" type="text" required placeholder="Name" />

                        <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Any additional information..." />

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Dispatch') : __('Create Dispatch') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Dispatch Records Table (Desktop) -->
            @if ($dispatches->count() > 0)
                <div class="hidden lg:block bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Farm') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Quantity') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Recipient') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Reason') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($dispatches as $dispatch)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ $dispatch->date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                            <div class="font-medium">{{ $dispatch->farm->name }}</div>
                                            @if ($dispatch->notes)
                                                <div class="text-xs text-zinc-500 mt-1 italic">{{ $dispatch->notes }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                                            <flux:badge size="sm" :variant="$dispatch->dispatch_type === 'sale' ? 'success' : 'warning'">
                                                {{ $dispatch->dispatch_type === 'sale' ? __('Sale') : __('Owner') }}
                                            </flux:badge>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-zinc-900 dark:text-zinc-100 font-semibold whitespace-nowrap">
                                            {{ number_format($dispatch->quantity) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $dispatch->recipient_name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $dispatch->dispatch_reason ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            @if ($dispatch->dispatch_type === 'sale' && $dispatch->total_amount)
                                                <span class="text-green-600 dark:text-green-400 font-semibold">
                                                    {{ number_format($dispatch->total_amount, 2) }}
                                                </span>
                                            @else
                                                <span class="text-zinc-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <flux:button wire:click="edit({{ $dispatch->id }})" size="sm" variant="ghost" icon="pencil" />
                                                <flux:button
                                                    wire:click="delete({{ $dispatch->id }})"
                                                    wire:confirm="Are you sure you want to delete this dispatch record?"
                                                    size="sm"
                                                    variant="danger"
                                                    icon="trash"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Dispatch Records Cards (Mobile) -->
                <div class="lg:hidden space-y-3">
                    @foreach ($dispatches as $dispatch)
                        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                        <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $dispatch->date->format('M d, Y') }}
                                        </h4>
                                        <flux:badge size="sm" :variant="$dispatch->dispatch_type === 'sale' ? 'success' : 'warning'">
                                            {{ $dispatch->dispatch_type === 'sale' ? __('Sale') : __('Owner Consumption') }}
                                        </flux:badge>
                                        <flux:badge size="sm">{{ number_format($dispatch->quantity) }} eggs</flux:badge>
                                    </div>

                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2 flex items-center gap-1">
                                        <span>🏠</span>
                                        <span>{{ $dispatch->farm->name }}</span>
                                    </p>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mt-3 text-xs">
                                        <div>
                                            <span class="text-zinc-500 block">Recipient</span>
                                            <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $dispatch->recipient_name }}</p>
                                        </div>
                                        @if ($dispatch->dispatch_reason)
                                            <div>
                                                <span class="text-zinc-500 block">Reason</span>
                                                <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">{{ $dispatch->dispatch_reason }}</p>
                                            </div>
                                        @endif
                                        @if ($dispatch->dispatch_type === 'sale' && $dispatch->total_amount)
                                            <div>
                                                <span class="text-zinc-500 block">Amount</span>
                                                <p class="font-semibold text-green-600 dark:text-green-400 mt-1">{{ number_format($dispatch->total_amount, 2) }}</p>
                                            </div>
                                        @endif
                                    </div>

                                    @if ($dispatch->notes)
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3 italic">{{ $dispatch->notes }}</p>
                                    @endif
                                </div>
                                <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                                    <flux:button wire:click="edit({{ $dispatch->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                        <span class="sm:inline">{{ __('Edit') }}</span>
                                    </flux:button>
                                    <flux:button
                                        wire:click="delete({{ $dispatch->id }})"
                                        wire:confirm="Are you sure you want to delete this dispatch record?"
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        class="flex-1 sm:flex-none sm:w-auto">
                                        <span class="sm:inline">{{ __('Delete') }}</span>
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                    <div class="mx-auto w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No dispatch records yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Start recording egg dispatches for owner consumption.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                        {{ __('Create First Dispatch') }}
                    </flux:button>
                </div>
            @endif

            <!-- Pagination -->
            @if ($dispatches->hasPages())
                <div class="mt-6">
                    {{ $dispatches->links() }}
                </div>
            @endif
        </div>
    </x-operations.layout>
</section>
