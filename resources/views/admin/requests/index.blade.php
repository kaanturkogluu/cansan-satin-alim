<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Request List') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}"
                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('Back to Dashboard') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 dark:bg-green-900/30 border border-green-400 text-green-700 dark:text-green-300 px-4 py-3 rounded relative">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Filtreler: Durum + Tarihe göre --}}
                    <form method="GET" action="{{ route('admin.requests') }}" class="mb-6 flex flex-wrap items-center gap-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <label for="status" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Filter by status') }}:</label>
                            <select name="status" id="status" onchange="this.form.submit()"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>{{ __('All statuses') }}</option>
                                <option value="pending_chief" {{ request('status') === 'pending_chief' ? 'selected' : '' }}>{{ __('pending_chief') }}</option>
                                <option value="pending_manager" {{ request('status') === 'pending_manager' ? 'selected' : '' }}>{{ __('pending_manager') }}</option>
                                <option value="pending_purchasing" {{ request('status') === 'pending_purchasing' ? 'selected' : '' }}>{{ __('pending_purchasing') }}</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('approved') }}</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('rejected') }}</option>
                            </select>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <label for="date_order" class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Filter by date') }}:</label>
                            <select name="date_order" id="date_order" onchange="this.form.submit()"
                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="en_yeni" {{ request('date_order', 'en_yeni') === 'en_yeni' ? 'selected' : '' }}>{{ __('Newest first') }}</option>
                                <option value="en_eski" {{ request('date_order') === 'en_eski' ? 'selected' : '' }}>{{ __('Oldest first') }}</option>
                            </select>
                        </div>
                        @if(request()->hasAny(['status', 'date_order']))
                            <a href="{{ route('admin.requests') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">{{ __('Clear filters') }}</a>
                        @endif
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Req No') }}
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Title') }}
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('User') }}
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Department') }}
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Date') }}
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        {{ __('Action') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($requests as $req)
                                    <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                        ondblclick="window.location.href='{{ route('requests.show', $req->id) }}'"
                                        title="{{ __('Double-click to view details') }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $req->request_no }}</td>
                                        <td class="px-6 py-4 text-sm max-w-xs truncate" title="{{ $req->title }}">{{ $req->title }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <x-user-label :user="$req->user" />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $req->department->name ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusClass = match($req->status) {
                                                    'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                                    'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                                    default => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                                };
                                            @endphp
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ __($req->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $req->created_at->format('d.m.Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                            <a href="{{ route('requests.show', $req->id) }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">
                                                {{ __('View') }}
                                            </a>
                                            <span class="text-gray-400">|</span>
                                            <a href="{{ route('admin.requests.edit', $req->id) }}"
                                                class="text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300 font-medium">
                                                {{ __('Edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            {{ __('No requests found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
