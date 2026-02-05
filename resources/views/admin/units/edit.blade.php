<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Edit Unit') }}</h2>
            <a href="{{ route('admin.units') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded text-sm">{{ __('Back to list') }}</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.units.update', $unit) }}">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="name" :value="__('Unit name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $unit->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="symbol" :value="__('Symbol (optional)')" />
                            <x-text-input id="symbol" name="symbol" type="text" class="mt-1 block w-full" :value="old('symbol', $unit->symbol)" />
                            <x-input-error :messages="$errors->get('symbol')" class="mt-2" />
                        </div>
                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button>{{ __('Update Unit') }}</x-primary-button>
                            <a href="{{ route('admin.units') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
