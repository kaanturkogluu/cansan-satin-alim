<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pending Approvals') }}
        </h2>
    </x-slot>

    @php
        $approvalsListConfig = [
            'initialRequests' => $requestsForJs,
            'channel' => $approvalsChannel,
            'showBaseUrl' => $requestShowBaseUrl,
            'noPendingText' => __('No pending requests found.'),
            'reviewText' => __('Review'),
            'doubleClickTitle' => __('Double-click to view details'),
        ];
    @endphp
    <script>
        window._approvalsListConfig = @json($approvalsListConfig);
    </script>
    <div class="py-12" x-data="approvalsList()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900/30 dark:border-green-700 dark:text-green-200"
                            role="alert">
                            <strong class="font-bold">{{ __('Success!') }}</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <p class="text-center text-gray-500" x-show="list.length === 0" x-text="noPendingText"></p>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" x-show="list.length > 0">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Req No') }}</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('User') }}</th>
                                <th class="px-6 py-3 bg-gray-50 dark:bg-gray-700 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('Title') }}</th>
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
                                    <td class="px-6 py-4 whitespace-nowrap" x-text="req.user ? req.user.name : '-'"></td>
                                    <td class="px-6 py-4 whitespace-nowrap" x-text="req.title"></td>
                                    <td class="px-6 py-4 whitespace-nowrap" x-text="formatDate(req.created_at)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a :href="showBaseUrl + '/' + req.id" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400" x-text="reviewText"></a>
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
