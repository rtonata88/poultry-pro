<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Vendor Categories')" :subheading="__('Classify and organize your business vendors')">
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
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Categories') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Manage your vendor classification system') }}</p>
                </div>
                @if (!$showForm)
                    <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Category') }}
                    </flux:button>
                @endif
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Category') : __('New Category') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4 sm:space-y-5">
                        <flux:input wire:model="name" :label="__('Category Name')" type="text" required placeholder="e.g., Suppliers, Contractors, Service Providers" />
                        <flux:textarea wire:model="description" :label="__('Description')" rows="3" placeholder="Brief description of this vendor category..." />

                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <flux:checkbox wire:model="is_active" :label="__('Active')" />
                            <flux:text class="text-xs text-zinc-500">{{ __('Inactive categories will not appear in vendor forms') }}</flux:text>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Category') : __('Create Category') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Categories List -->
            <div class="space-y-3">
                @forelse ($categories as $category)
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                    <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $category->name }}</h4>
                                    <flux:badge :variant="$category->is_active ? 'success' : 'danger'" size="sm">
                                        {{ $category->is_active ? __('Active') : __('Inactive') }}
                                    </flux:badge>
                                </div>
                                @if ($category->description)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-2 leading-relaxed">{{ $category->description }}</p>
                                @endif
                                <div class="text-xs text-zinc-500 dark:text-zinc-500 mt-3">
                                    {{ __('Created') }} {{ $category->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                                <flux:button wire:click="edit({{ $category->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                    <span class="sm:inline">{{ __('Edit') }}</span>
                                </flux:button>
                                <flux:button
                                    wire:click="delete({{ $category->id }})"
                                    wire:confirm="Are you sure you want to delete this category? This action cannot be undone."
                                    size="sm"
                                    variant="danger"
                                    icon="trash"
                                    class="flex-1 sm:flex-none sm:w-auto">
                                    <span class="sm:inline">{{ __('Delete') }}</span>
                                </flux:button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                        <div class="mx-auto w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No categories yet') }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Create your first vendor category to start organizing your business relationships.') }}</p>
                        <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                            {{ __('Create First Category') }}
                        </flux:button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($categories->hasPages())
                <div class="mt-6">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </x-settings.layout>
</section>
