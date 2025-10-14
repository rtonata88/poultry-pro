<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Users')" :subheading="__('Manage system users and their roles')">
        @if (session('status'))
            <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                <flux:text class="text-green-700 dark:text-green-400 font-medium">
                    {{ session('status') }}
                </flux:text>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4">
                <flux:text class="text-red-700 dark:text-red-400 font-medium">
                    {{ session('error') }}
                </flux:text>
            </div>
        @endif

        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search users...') }}" class="flex-1" icon="magnifying-glass" />

                @if (!$showForm)
                    <flux:button wire:click="create" variant="primary" icon="plus" class="w-full sm:w-auto">
                        {{ __('Add User') }}
                    </flux:button>
                @endif
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit User') : __('New User') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <flux:input wire:model="name" :label="__('Name')" type="text" required placeholder="John Doe" />
                            <flux:input wire:model="email" :label="__('Email')" type="email" required placeholder="john@example.com" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <flux:input
                                    wire:model="password"
                                    :label="$editingId ? __('New Password (leave blank to keep current)') : __('Password')"
                                    type="password"
                                    :required="!$editingId"
                                    placeholder="••••••••"
                                />
                            </div>
                            <div>
                                <flux:input
                                    wire:model="password_confirmation"
                                    :label="__('Confirm Password')"
                                    type="password"
                                    :required="!$editingId && $password"
                                    placeholder="••••••••"
                                />
                            </div>
                        </div>

                        <!-- Roles Selection -->
                        <div>
                            <label class="block text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-3">
                                {{ __('Roles') }}
                            </label>
                            @if($roles->count() > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                    @foreach($roles as $role)
                                        <label class="flex items-center space-x-2 cursor-pointer hover:bg-zinc-100 dark:hover:bg-zinc-800 p-2 rounded">
                                            <input
                                                type="checkbox"
                                                wire:model="selectedRoles"
                                                value="{{ $role->id }}"
                                                class="rounded border-zinc-300 dark:border-zinc-600 text-blue-600 focus:ring-blue-500 dark:focus:ring-blue-400"
                                            >
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ $role->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                        {{ __('No roles available. Please create roles first.') }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update User') : __('Create User') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Users List -->
            <div class="space-y-3">
                @forelse ($users as $user)
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                            {{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <flux:badge variant="success" size="sm">{{ __('You') }}</flux:badge>
                                            @endif
                                        </h4>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                                    </div>
                                </div>

                                @if($user->roles->count() > 0)
                                    <div class="flex flex-wrap gap-1.5 mt-3 ml-12">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 rounded">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2 ml-12">{{ __('No roles assigned') }}</p>
                                @endif

                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-3 ml-12">
                                    {{ __('Joined') }}: {{ $user->created_at->format('M d, Y') }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2 sm:ml-4">
                                <flux:button wire:click="edit({{ $user->id }})" size="sm" variant="ghost" icon="pencil">
                                    {{ __('Edit') }}
                                </flux:button>
                                @if($user->id !== auth()->id())
                                    <flux:button
                                        wire:click="delete({{ $user->id }})"
                                        wire:confirm="Are you sure you want to delete this user? This action cannot be undone."
                                        size="sm"
                                        variant="danger"
                                        icon="trash">
                                        {{ __('Delete') }}
                                    </flux:button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-12 text-center">
                        <div class="mx-auto w-16 h-16 bg-zinc-100 dark:bg-zinc-700 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No users found') }}</h3>
                        <p class="text-zinc-600 dark:text-zinc-400 mb-4">
                            @if($search)
                                {{ __('No users match your search.') }}
                            @else
                                {{ __('Get started by creating your first user.') }}
                            @endif
                        </p>
                        @if(!$showForm && !$search)
                            <flux:button wire:click="create" variant="primary" icon="plus">
                                {{ __('Add User') }}
                            </flux:button>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </x-settings.layout>
</section>
