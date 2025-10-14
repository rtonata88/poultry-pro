<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Permissions')" :subheading="__('Manage system permissions')">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <flux:text class="text-green-700 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </flux:text>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search permissions...') }}" class="flex-1" icon="magnifying-glass" />

                @if (!$showForm)
                    <flux:button wire:click="create" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Permission') }}
                    </flux:button>
                @endif
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Permission') : __('New Permission') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4">
                        <flux:input wire:model="name" :label="__('Permission Name')" type="text" required placeholder="e.g., create-users, edit-products" />

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Permission') : __('Create Permission') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Permissions List -->
            <div class="space-y-3">
                @forelse ($permissions as $permission)
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex-1">
                                <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $permission->name }}</h4>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                                    {{ __('Created') }}: {{ $permission->created_at->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 sm:ml-4">
                                <flux:button wire:click="edit({{ $permission->id }})" size="sm" variant="ghost" icon="pencil">
                                    {{ __('Edit') }}
                                </flux:button>
                                <flux:button
                                    wire:click="delete({{ $permission->id }})"
                                    wire:confirm="Are you sure you want to delete this permission? This action cannot be undone."
                                    size="sm"
                                    variant="danger"
                                    icon="trash">
                                    {{ __('Delete') }}
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                        <div class="mx-auto w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No permissions found') }}</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                            @if($search)
                                {{ __('No permissions match your search.') }}
                            @else
                                {{ __('Get started by creating your first permission.') }}
                            @endif
                        </p>
                        @if(!$showForm && !$search)
                            <flux:button wire:click="create" variant="primary" icon="plus">
                                {{ __('Add Permission') }}
                            </flux:button>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($permissions->hasPages())
                <div class="mt-6">
                    {{ $permissions->links() }}
                </div>
            @endif
        </div>
    </x-settings.layout>
</section>
