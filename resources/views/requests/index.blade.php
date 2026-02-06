<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Requests') }}
        </h2>
    </x-slot>

    @php
        $myRequestsListConfig = [
            'initialRequests' => $requestsForJs,
            'channel' => $userRequestsChannel,
            'showBaseUrl' => $requestShowBaseUrl,
            'statusLabels' => $statusLabels,
            'viewText' => __('View'),
            'doubleClickTitle' => __('Double-click to view details'),
        ];
    @endphp
    <script>
        window._myRequestsListConfig = @json($myRequestsListConfig);
    </script>
    <div class="py-12" x-data="myRequestsList()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('requests.create') }}"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            {{ __('Create New Request') }}
                        </a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Req No') }}</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Title') }}</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Date') }}</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="req in list" :key="req.id">
                                <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition"
                                    @dblclick="window.location.href = showBaseUrl + '/' + req.id"
                                    :title="doubleClickTitle">
                                    <td class="px-6 py-4 whitespace-nowrap" x-text="req.request_no"></td>
                                    <td class="px-6 py-4 whitespace-nowrap" x-text="req.title"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="statusClass(req.status)"
                                            x-text="(statusLabels && statusLabels[req.status]) || req.status"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap" x-text="formatDate(req.created_at)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a :href="showBaseUrl + '/' + req.id" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400" x-text="viewText"></a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
