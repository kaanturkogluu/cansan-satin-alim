<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Create Role') }}</h2>
            <a href="{{ route('admin.roles') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded text-sm">{{ __('Back to list') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.roles.store') }}">
                        @csrf
                        <div>
                            <x-input-label for="name" :value="__('Role name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required placeholder="{{ __('e.g. Engineer, Manager') }}" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="slug" :value="__('Slug')" />
                            <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full font-mono" :value="old('slug')" required placeholder="engineer, chief, admin" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Lowercase letters, numbers and underscores only. Used in code.') }}</p>
                            <x-input-error :messages="$errors->get('slug')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="sort_order" :value="__('Sort order')" />
                            <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="mt-1 block w-full" :value="old('sort_order', '0')" />
                            <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                        </div>
                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button>{{ __('Create Role') }}</x-primary-button>
                            <a href="{{ route('admin.roles') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
