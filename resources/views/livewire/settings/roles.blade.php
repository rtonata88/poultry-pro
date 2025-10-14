<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Roles')" :subheading="__('Manage user roles and permissions')">
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
                <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search roles...') }}" class="flex-1" icon="magnifying-glass" />

                @if (!$showForm)
                    <flux:button wire:click="create" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add Role') }}
                    </flux:button>
                @endif
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Role') : __('New Role') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4">
                        <flux:input wire:model="name" :label="__('Role Name')" type="text" required placeholder="e.g., Administrator, Manager" />

                        <!-- Permissions Selection -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-3">
                                {{ __('Permissions') }}
                            </label>
                            @if(count($groupedPermissions) > 0)
                                <div class="space-y-4 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700 max-h-96 overflow-y-auto">
                                    @foreach($groupedPermissions as $groupName => $permissions)
                                        <div class="border-b border-zinc-200 dark:border-zinc-700 last:border-0 pb-4 last:pb-0">
                                            <div class="flex items-center justify-between mb-3">
                                                <h5 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 flex items-center gap-2">
                                                    <span class="w-1 h-4 bg-blue-500 rounded"></span>
                                                    {{ $groupName }}
                                                </h5>
                                                <label class="flex items-center space-x-2 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 px-3 py-1.5 rounded transition-colors">
                                                    <input
                                                        type="checkbox"
                                                        wire:click="toggleGroupPermissions('{{ $groupName }}', {{ json_encode($permissions) }})"
                                                        @if($this->isGroupSelected($permissions)) checked @endif
                                                        class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-400"
                                                    >
                                                    <span class="text-xs font-medium text-blue-600 dark:text-blue-400">{{ __('Select All') }}</span>
                                                </label>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 ml-3">
                                                @foreach($permissions as $permission)
                                                    <label class="flex items-center space-x-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 p-2 rounded">
                                                        <input
                                                            type="checkbox"
                                                            wire:model.live="selectedPermissions"
                                                            value="{{ $permission->id }}"
                                                            class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-400"
                                                        >
                                                        <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ ucwords(str_replace('-', ' ', str_replace(['view-', 'create-', 'edit-', 'delete-', 'export-', 'manage-'], ['View ', 'Create ', 'Edit ', 'Delete ', 'Export ', 'Manage '], $permission->name))) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                        {{ __('No permissions available. Please create permissions first.') }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Role') : __('Create Role') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Roles List -->
            <div class="space-y-3">
                @forelse ($roles as $role)
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $role->name }}</h4>
                                    <flux:badge variant="info" size="sm">{{ $role->permissions_count }} {{ __('permissions') }}</flux:badge>
                                </div>

                                @if($role->permissions->count() > 0)
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        @foreach($role->permissions->take(5) as $permission)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/20 rounded">
                                                {{ $permission->name }}
                                            </span>
                                        @endforeach
                                        @if($role->permissions->count() > 5)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-zinc-600 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-800 rounded">
                                                +{{ $role->permissions->count() - 5 }} {{ __('more') }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">{{ __('No permissions assigned') }}</p>
                                @endif

                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-3">
                                    {{ __('Created') }}: {{ $role->created_at->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 sm:ml-4">
                                <flux:button wire:click="edit({{ $role->id }})" size="sm" variant="ghost" icon="pencil">
                                    {{ __('Edit') }}
                                </flux:button>
                                <flux:button
                                    wire:click="delete({{ $role->id }})"
                                    wire:confirm="Are you sure you want to delete this role? This action cannot be undone."
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No roles found') }}</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                            @if($search)
                                {{ __('No roles match your search.') }}
                            @else
                                {{ __('Get started by creating your first role.') }}
                            @endif
                        </p>
                        @if(!$showForm && !$search)
                            <flux:button wire:click="create" variant="primary" icon="plus">
                                {{ __('Add Role') }}
                            </flux:button>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($roles->hasPages())
                <div class="mt-6">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </x-settings.layout>
</section>
