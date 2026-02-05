<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Request Details') }} : {{ $requestForm->request_no }}
            </h2>
            <div class="flex items-center gap-2">
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <a href="{{ route('admin.requests.edit', $requestForm->id) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 dark:bg-amber-600 dark:hover:bg-amber-500 text-white text-sm font-medium rounded-lg transition">
                        {{ __('Edit') }}
                    </a>
                @endif
                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold">{{ __('Status') }}:
                            <span
                                class="{{ $requestForm->status === 'approved' ? 'text-green-600' : ($requestForm->status === 'rejected' ? 'text-red-600' : 'text-yellow-600') }}">
                                {{ __($requestForm->status) }}
                            </span>
                        </h3>
                        @if($requestForm->rejection_reason)
                            <p class="text-red-500 mt-2"><strong>{{ __('Rejection Reason') }}:</strong>
                                {{ $requestForm->rejection_reason }}</p>
                        @endif
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-gray-500">{{ __('Title') }}</h4>
                        <p class="text-lg">{{ $requestForm->title }}</p>
                    </div>

                    <div class="mb-6">
                        <h4 class="text-md font-semibold text-gray-500">{{ __('Description') }}</h4>
                        <p class="whitespace-pre-wrap">{{ $requestForm->description }}</p>
                    </div>

                    <div class="mb-6 border-t pt-4">
                        <h4 class="text-md font-semibold text-gray-500 mb-2">{{ __('Items') }}</h4>
                        <ul class="list-disc list-inside space-y-2">
                            @foreach($requestForm->items as $item)
                                <li class="mb-2">
                                    @if($item->image_path)
                                        <div class="mt-2">
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $item->content }}</span>
                                            @if($item->unit_id && $item->quantity !== null)
                                                <span class="text-gray-600 dark:text-gray-400"> — {{ $item->quantity }} {{ $item->unit?->name ?? $item->unit?->symbol ?? '' }}</span>
                                            @endif
                                            @if($item->image_name)
                                                <span class="text-gray-500 dark:text-gray-400 text-sm"> — {{ $item->image_name }}</span>
                                            @endif
                                            <div class="mt-1">
                                                <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->image_name ?? $item->content }}"
                                                    class="max-w-xs max-h-48 rounded-lg border border-gray-200 dark:border-gray-600 object-contain" />
                                            </div>
                                            @if($item->link)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 break-all">{{ __('Link') }}: {{ $item->link }}</p>
                                            @endif
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('All users can access this image.') }}</p>
                                        </div>
                                    @else
                                        {{ $item->content }}
                                        @if($item->unit_id && $item->quantity !== null)
                                            <span class="text-gray-600 dark:text-gray-400"> — {{ $item->quantity }} {{ $item->unit?->name ?? $item->unit?->symbol ?? '' }}</span>
                                        @endif
                                        @if($item->link)
                                            <span class="text-gray-600 dark:text-gray-400 text-sm block mt-0.5 break-all">{{ __('Link') }}: {{ $item->link }}</span>
                                        @endif
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Talep akış geçmişi (ağaç / timeline) --}}
                    @php
                        $roleLabels = [
                            'engineer' => __('Engineer'),
                            'chief' => __('Chief'),
                            'manager' => __('Manager'),
                            'purchasing' => __('Purchasing'),
                            'admin' => __('Admin'),
                        ];
                        $histories = $requestForm->histories->sortBy('created_at')->values();
                        $prevAt = null;
                        $flowSteps = [];
                        foreach ($histories as $h) {
                            $relative = null;
                            if ($prevAt) {
                                $totalMins = (int) $prevAt->diffInMinutes($h->created_at);
                                if ($totalMins >= 60 * 24 * 7) {
                                    $w = (int) floor($totalMins / (60 * 24 * 7));
                                    $relative = $w == 1 ? __('1 week later') : $w . ' ' . __('weeks later');
                                } elseif ($totalMins >= 60 * 24) {
                                    $d = (int) floor($totalMins / (60 * 24));
                                    $relative = $d == 1 ? __('1 day later') : $d . ' ' . __('days later');
                                } elseif ($totalMins >= 60) {
                                    $hr = (int) floor($totalMins / 60);
                                    $relative = $hr == 1 ? __('1 hour later') : $hr . ' ' . __('hours later');
                                } else {
                                    $relative = $totalMins <= 1 ? __('1 min later') : $totalMins . ' ' . __('mins later');
                                }
                            }
                            $prevAt = $h->created_at;
                            $roleTr = $roleLabels[$h->user->role] ?? $h->user->role;
                            $flowSteps[] = [
                                'type' => $h->action,
                                'at' => $h->created_at,
                                'relative' => $relative,
                                'user_name' => $h->user->name,
                                'role' => $roleTr,
                                'note' => $h->note,
                                'user' => $h->user,
                            ];
                        }
                        if (in_array($requestForm->status, ['pending_chief', 'pending_manager', 'pending_purchasing'])) {
                            $lastAt = $histories->isNotEmpty() ? $histories->last()->created_at : $requestForm->created_at;
                            $totalMins = (int) $lastAt->diffInMinutes(now());
                            if ($totalMins >= 60 * 24 * 7) {
                                $w = (int) floor($totalMins / (60 * 24 * 7));
                                $waitText = $w == 1 ? __('waiting 1 week') : $w . ' ' . __('weeks waiting');
                            } elseif ($totalMins >= 60 * 24) {
                                $d = (int) floor($totalMins / (60 * 24));
                                $waitText = $d == 1 ? __('waiting 1 day') : $d . ' ' . __('days waiting');
                            } elseif ($totalMins >= 60) {
                                $hr = (int) floor($totalMins / 60);
                                $waitText = $hr == 1 ? __('waiting 1 hour') : $hr . ' ' . __('hours waiting');
                            } else {
                                $waitText = $totalMins <= 1 ? __('waiting 1 min') : $totalMins . ' ' . __('mins waiting');
                            }
                            $flowSteps[] = [
                                'type' => 'waiting',
                                'at' => null,
                                'relative' => $waitText,
                                'status' => $requestForm->status,
                                'status_label' => __($requestForm->status),
                            ];
                        }
                    @endphp
                    <div class="mb-6 border-t pt-6">
                        <h4 class="text-md font-semibold text-gray-500 dark:text-gray-400 mb-4">{{ __('Request flow') }}</h4>
                        <div class="relative">
                            {{-- Dikey çizgi --}}
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-300 dark:bg-gray-600 rounded-full"
                                style="margin-left: 11px;"></div>
                            @foreach($flowSteps as $step)
                                <div class="relative flex gap-4 mb-6 pl-2 last:mb-0">
                                    <div class="relative z-10 flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold
                                        @if($step['type'] === 'created') bg-blue-500
                                        @elseif($step['type'] === 'approved') bg-green-500
                                        @elseif($step['type'] === 'rejected') bg-red-500
                                        @elseif($step['type'] === 'admin_updated') bg-slate-500 dark:bg-slate-600
                                        @else bg-amber-500 dark:bg-amber-600
                                        @endif">
                                        @if($step['type'] === 'created')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        @elseif($step['type'] === 'approved')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        @elseif($step['type'] === 'rejected')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @elseif($step['type'] === 'admin_updated')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0 pb-1">
                                        @if($step['type'] === 'waiting')
                                            <p class="text-sm text-amber-600 dark:text-amber-400 font-medium">
                                                {{ __('Waiting for approval') }}: {{ $step['status_label'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $step['relative'] }}</p>
                                        @else
                                            @if($step['relative'])
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">{{ $step['relative'] }}</p>
                                            @endif
                                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                                @if($step['type'] === 'created')
                                                    {{ $step['role'] }} (<x-user-label :user="$step['user'] ?? null" />) {{ __('created request') }}
                                                @elseif($step['type'] === 'approved')
                                                    {{ $step['role'] }} (<x-user-label :user="$step['user'] ?? null" />) {{ __('approved') }}
                                                    @if($step['user']->role === 'chief') — {{ __('Sent to manager') }}
                                                    @elseif($step['user']->role === 'manager') — {{ __('Sent to purchasing') }}
                                                    @else — {{ __('Completed') }}
                                                    @endif
                                                @elseif($step['type'] === 'admin_updated')
                                                    {{ $step['role'] }} (<x-user-label :user="$step['user'] ?? null" />) {{ __('updated request') }}
                                                    @if(!empty($step['note'])) <span class="text-gray-500 dark:text-gray-400">— {{ $step['note'] }}</span> @endif
                                                @else
                                                    {{ $step['role'] }} (<x-user-label :user="$step['user'] ?? null" />) {{ __('action_rejected') }}
                                                    @if(!empty($step['note'])) <span class="text-red-600 dark:text-red-400">— {{ $step['note'] }}</span> @endif
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $step['at']->format('d.m.Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @php
                        $user = Auth::user();
                        $canApprove = false;
                        if ($user->role === 'chief' && $requestForm->status === 'pending_chief')
                            $canApprove = true;
                        if ($user->role === 'manager' && $requestForm->status === 'pending_manager')
                            $canApprove = true;
                        if ($user->role === 'purchasing' && $requestForm->status === 'pending_purchasing')
                            $canApprove = true;
                    @endphp

                    @if($canApprove)
                        <div class="mt-8 border-t pt-6">
                            <h3 class="font-bold mb-4">{{ __('Actions') }}</h3>
                            <div class="flex gap-4">
                                <form action="{{ route('requests.approve', $requestForm->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        {{ __('Approve') }}
                                    </button>
                                </form>

                                <button onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                                    class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    {{ __('Reject') }}
                                </button>
                            </div>
                        </div>

                        <!-- Reject Modal -->
                        <div id="reject-modal"
                            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
                            <div
                                class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                                <div class="mt-3 text-center">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                        {{ __('Reject Request') }}
                                    </h3>
                                    <form action="{{ route('requests.reject', $requestForm->id) }}" method="POST"
                                        class="mt-2 text-left">
                                        @csrf
                                        <textarea name="reason"
                                            class="w-full border-gray-300 dark:border-gray-700 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-300"
                                            placeholder="{{ __('Reason for rejection') }}" required></textarea>
                                        <div class="mt-4 flex justify-between">
                                            <button type="button"
                                                onclick="document.getElementById('reject-modal').classList.add('hidden')"
                                                class="text-gray-500 hover:text-gray-700">{{ __('Cancel') }}</button>
                                            <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-1 px-3 rounded">{{ __('Confirm Reject') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>