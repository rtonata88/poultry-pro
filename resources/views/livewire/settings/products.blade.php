<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Products')" :subheading="__('Manage your product catalog for sales')">
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
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Product Catalog') }}</h3>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ __('Manage products available for sale') }}</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <flux:select wire:model.live="filterType" placeholder="{{ __('Filter by type') }}" class="flex-1">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="chicken">{{ __('Chicken') }}</option>
                        <option value="eggs">{{ __('Eggs') }}</option>
                    </flux:select>

                    @if (!$showForm)
                        <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus" class="w-full sm:w-auto">
                            {{ __('Add Product') }}
                        </flux:button>
                    @endif
                </div>
            </div>

            <!-- Add/Edit Form -->
            @if ($showForm)
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-6 shadow-sm">
                    <h4 class="text-md font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                        {{ $editingId ? __('Edit Product') : __('New Product') }}
                    </h4>

                    <form wire:submit="save" class="space-y-4 sm:space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:input wire:model="name" :label="__('Product Name')" type="text" required placeholder="e.g., Point of Lay, Chicks, Egg Tray" class="sm:col-span-2" />

                            <flux:select wire:model.live="type" :label="__('Product Type')" required>
                                <option value="chicken">{{ __('Chicken') }}</option>
                                <option value="eggs">{{ __('Eggs') }}</option>
                            </flux:select>

                            <flux:input wire:model="unit" :label="__('Unit')" type="text" required placeholder="e.g., piece, tray, dozen" />
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            <flux:input wire:model="unit_size" :label="__('Unit Size')" type="number" min="1" placeholder="e.g., 30 for egg trays" />

                            <flux:input wire:model="price" :label="__('Price')" type="number" step="0.01" min="0" required placeholder="0.00" />
                        </div>

                        <flux:textarea wire:model="description" :label="__('Description')" rows="3" placeholder="Brief description of this product..." />

                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <flux:checkbox wire:model="is_active" :label="__('Active')" />
                            <flux:text class="text-xs text-zinc-500">{{ __('Inactive products will not appear in sales forms') }}</flux:text>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                            <flux:button type="submit" variant="primary" class="w-full sm:w-auto">
                                {{ $editingId ? __('Update Product') : __('Create Product') }}
                            </flux:button>
                            <flux:button wire:click="cancel" variant="ghost" type="button" class="w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Products List -->
            <div class="space-y-3">
                @forelse ($products as $product)
                    <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 p-4 sm:p-5 hover:shadow-md transition-shadow duration-200">
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                    <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $product->name }}</h4>
                                    <flux:badge :variant="$product->type === 'chicken' ? 'warning' : 'info'" size="sm">
                                        {{ ucfirst($product->type) }}
                                    </flux:badge>
                                    <flux:badge :variant="$product->is_active ? 'success' : 'danger'" size="sm">
                                        {{ $product->is_active ? __('Active') : __('Inactive') }}
                                    </flux:badge>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mt-3 text-xs">
                                    <div>
                                        <span class="text-zinc-500 block">{{ __('Unit') }}</span>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">
                                            {{ $product->unit }}{{ $product->unit_size ? " ({$product->unit_size})" : '' }}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-zinc-500 block">{{ __('Price') }}</span>
                                        <p class="font-semibold text-green-600 dark:text-green-400 mt-1">
                                            {{ number_format($product->price, 2) }}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-zinc-500 block">{{ __('Created') }}</span>
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100 mt-1">
                                            {{ $product->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>

                                @if ($product->description)
                                    <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-3 italic">{{ $product->description }}</p>
                                @endif
                            </div>
                            <div class="flex sm:flex-col items-stretch sm:items-end gap-2 sm:ml-4">
                                <flux:button wire:click="edit({{ $product->id }})" size="sm" variant="ghost" icon="pencil" class="flex-1 sm:flex-none sm:w-auto">
                                    <span class="sm:inline">{{ __('Edit') }}</span>
                                </flux:button>
                                <flux:button
                                    wire:click="delete({{ $product->id }})"
                                    wire:confirm="Are you sure you want to delete this product? This action cannot be undone."
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-zinc-900 dark:text-zinc-100 mb-2">{{ __('No products yet') }}</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">{{ __('Add your first product to start building your sales catalog.') }}</p>
                        <flux:button wire:click="$set('showForm', true)" variant="primary" icon="plus">
                            {{ __('Create First Product') }}
                        </flux:button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($products->hasPages())
                <div class="mt-6">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </x-settings.layout>
</section>
