<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-bold mb-4">{{ __('Welcome, Admin!') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('admin.users') }}"
                            class="block p-6 bg-indigo-50 dark:bg-gray-700 rounded-lg hover:bg-indigo-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-bold text-lg">{{ __('User Management') }}</h4>
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ __('Create, edit, and delete system users.') }}</p>
                        </a>
                        <a href="{{ route('admin.requests') }}"
                            class="block p-6 bg-indigo-50 dark:bg-gray-700 rounded-lg hover:bg-indigo-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-bold text-lg">{{ __('Request List') }}</h4>
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ __('View all requests and their statuses.') }}</p>
                        </a>
                        <a href="{{ route('admin.roles') }}"
                            class="block p-6 bg-indigo-50 dark:bg-gray-700 rounded-lg hover:bg-indigo-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-bold text-lg">{{ __('Roles') }}</h4>
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ __('Manage roles for user assignment.') }}</p>
                        </a>
                        <a href="{{ route('admin.departments') }}"
                            class="block p-6 bg-indigo-50 dark:bg-gray-700 rounded-lg hover:bg-indigo-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-bold text-lg">{{ __('Departments') }}</h4>
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ __('Manage departments for user and request assignment.') }}</p>
                        </a>
                        <a href="{{ route('admin.units') }}"
                            class="block p-6 bg-indigo-50 dark:bg-gray-700 rounded-lg hover:bg-indigo-100 dark:hover:bg-gray-600 transition">
                            <h4 class="font-bold text-lg">{{ __('Units') }}</h4>
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ __('Manage units for request items (e.g. Adet, Kg, Litre).') }}</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>