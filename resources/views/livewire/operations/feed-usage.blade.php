<section class="w-full">
    <x-operations.layout :heading="__('Feed Usage')" :subheading="__('Record daily feed consumption by flocks')">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <flux:text class="text-green-700 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </flux:text>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Usage Records') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Track daily feed consumption') }}</p>
                </div>
                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Record') }}
                    </flux:button>
                @endif
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Usage Record') : __('New Usage Record') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4 sm:space-y-5">
                        <!-- Cascading Farm, Coop, and Flock Selection -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                            <flux:select wire:model.live="selectedFarm" :label="__('Farm')" required>
                                <option value="">{{ __('Select a farm') }}</option>
                                @foreach ($farms as $farm)
                                    <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model.live="selectedCoop" :label="__('Coop')" required :disabled="!$selectedFarm">
                                <option value="">{{ $selectedFarm ? __('Select a coop') : __('Select farm first') }}</option>
                                @foreach ($coopsForSelectedFarm as $coop)
                                    <option value="{{ $coop->id }}">{{ $coop->name }}</option>
                                @endforeach
                            </flux:select>

                            <flux:select wire:model.live="flock_id" :label="__('Flock')" required :disabled="!$selectedCoop" class="sm:col-span-2 lg:col-span-1">
                                <option value="">{{ $selectedCoop ? __('Select a flock') : __('Select coop first') }}</option>
                                @foreach ($flocksForSelectedCoop as $flock)
                                    <option value="{{ $flock->id }}">{{ $flock->batch_number }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                            <flux:select wire:model="feed_type_id" :label="__('Feed Type')" required class="sm:col-span-2 lg:col-span-1">
                                <option value="">{{ __('Select feed type') }}</option>
                                @foreach ($feedTypes as $feedType)
                                    <option value="{{ $feedType->id }}">{{ $feedType->name }} ({{ strtoupper($feedType->unit) }})</option>
                                @endforeach
                            </flux:select>

                            <flux:input wire:model="date" :label="__('Date')" type="date" required max="{{ now()->format('Y-m-d') }}" />

                            <flux:input wire:model="quantity_used" :label="__('Quantity Used')" type="number" step="0.01" min="0" required placeholder="Amount consumed" class="sm:col-span-2 lg:col-span-1" />
                        </div>

                        <flux:textarea wire:model="notes" :label="__('Notes')" rows="3" placeholder="Any observations or additional information..." />

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Record') : __('Create Record') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Records Table (Desktop) -->
            @if ($records->count() > 0)
                <div class="hidden lg:block bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Location') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Feed Type') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Quantity') }}</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($records as $record)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100 whitespace-nowrap">
                                            {{ $record->date->format('M d, Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                            <div class="max-w-xs">
                                                <div class="font-medium">{{ $record->flock->batch_number }}</div>
                                                <div class="text-xs text-zinc-500">{{ $record->flock->coop->name }} • {{ $record->flock->coop->farm->name }}</div>
                                                @if ($record->notes)
                                                    <div class="text-xs text-zinc-500 mt-1 italic">{{ $record->notes }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">
                                            <flux:badge size="sm" variant="success">{{ $record->feedType->name }}</flux:badge>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-zinc-900 dark:text-zinc-100 font-semibold whitespace-nowrap">
                                            {{ number_format($record->quantity_used, 2) }} {{ strtoupper($record->feedType->unit) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <flux:button wire:click="edit({{ $record->id }})" size="sm" variant="ghost" icon="pencil" />
                                                <flux:button
                                                    wire:click="delete({{ $record->id }})"
                                                    wire:confirm="Are you sure you want to delete this usage record?"
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

                <!-- Records Cards (Mobile) -->
                <div class="lg:hidden space-y-3">
                    @foreach ($records as $record)
                        <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                        <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $record->date->format('M d, Y') }}
                                        </h4>
                                        <flux:badge size="sm" variant="success">{{ $record->feedType->name }}</flux:badge>
                                        <flux:badge size="sm">{{ number_format($record->quantity_used, 2) }} {{ strtoupper($record->feedType->unit) }}</flux:badge>
                                    </div>

                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2 flex items-center gap-1">
                                        <span>🏠</span>
                                        <span>{{ $record->flock->coop->farm->name }} → {{ $record->flock->coop->name }} → {{ $record->flock->batch_number }}</span>
                                    </p>

                                    @if ($record->notes)
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3 italic">{{ $record->notes }}</p>
                                    @endif
                                </div>
                                <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                                    <flux:button wire:click="edit({{ $record->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                        <span class="sm:inline">{{ __('Edit') }}</span>
                                    </flux:button>
                                    <flux:button
                                        wire:click="delete({{ $record->id }})"
                                        wire:confirm="Are you sure you want to delete this usage record?"
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No usage records yet') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Start recording daily feed consumption for your flocks.') }}</p>
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                        {{ __('Create First Record') }}
                    </flux:button>
                </div>
            @endif

            <!-- Pagination -->
            @if ($records->hasPages())
                <div class="mt-6">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </x-operations.layout>
</section>
