@php
    $socketTooltips = [
        'socket.connected' => __('socket.connected'),
        'socket.connecting' => __('socket.connecting'),
        'socket.disconnected' => __('socket.disconnected'),
    ];
@endphp
<script>
    window.socketTooltips = @json($socketTooltips);
</script>
<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if(Auth::check() && Auth::user()->role === 'admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                            {{ __('Admin Dashboard') }}
                        </x-nav-link>
                    @endif

                    @if(Auth::check() && in_array(Auth::user()->role, ['chief', 'manager', 'purchasing']))
                        <x-nav-link :href="route('requests.approvals')" :active="request()->routeIs('requests.approvals')">
                            {{ __('Pending Approvals') }}
                        </x-nav-link>
                        <x-nav-link :href="route('requests.my-actions')" :active="request()->routeIs('requests.my-actions')">
                            {{ __('My Approvals / Rejections') }}
                        </x-nav-link>
                    @endif

                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-2">
                <!-- Bildirimler: WebSocket ile anında düşer, tıklanınca talebe yönlendir -->
                @auth
                @php
                    $notificationListConfig = [
                        'userId' => Auth::id(),
                        'readRedirectBase' => url('notifications'),
                        'unreadUrl' => url('notifications/unread'),
                        'list' => collect($userUnreadNotifications ?? [])->map(fn ($n) => [
                            'id' => $n->id,
                            'data' => $n->data,
                            'created_at' => $n->created_at?->toIso8601String(),
                        ])->values()->toArray(),
                        'notificationsLabel' => __('Notifications'),
                        'unreadLabel' => __('unread'),
                        'noNewLabel' => __('No new notifications.'),
                    ];
                @endphp
                <script>
                    window._notificationListConfig = @json($notificationListConfig);
                </script>
                <div class="relative" x-data="notificationList()">
                    <button type="button" @click="open = !open" class="relative p-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 focus:ring-indigo-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m-6 0H9" /></svg>
                        <span x-show="list.length > 0" class="absolute top-0 right-0 flex h-5 w-5" x-cloak>
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-5 w-5 bg-red-500 text-xs font-medium text-white items-center justify-center" x-text="list.length > 9 ? '9+' : list.length"></span>
                        </span>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" x-transition class="absolute right-0 z-50 mt-2 w-80 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                        <div class="py-1 border-b border-gray-200 dark:border-gray-700 px-4 py-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="notificationsLabel"></span>
                            <span x-show="list.length > 0" class="text-xs text-gray-500 dark:text-gray-400" x-text="' (' + list.length + ' ' + unreadLabel + ')'" x-cloak></span>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <template x-for="n in list" :key="n.id">
                                <a :href="readRedirectBase + '/' + n.id + '/read-and-redirect'" class="block px-4 py-3 text-sm text-left text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                                    <span class="font-medium text-gray-900 dark:text-gray-100" x-text="n.data?.request_no || ''"></span>
                                    <span class="block text-gray-500 dark:text-gray-400 truncate" x-text="n.data?.message || n.data?.title || ''"></span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500" x-text="timeAgo(n.created_at)"></span>
                                </a>
                            </template>
                            <p x-show="list.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400" x-text="noNewLabel"></p>
                        </div>
                    </div>
                </div>
                @endauth
                <!-- WebSocket durum göstergesi: Bağlı / Bağlanıyor / Bağlantı yok -->
                <div
                    x-data="{ tooltips: {} }"
                    x-init="tooltips = window.socketTooltips || {}"
                    class="flex items-center"
                    @click="$store.socket.status === 'disconnected' && $store.socket.reconnect()"
                    :class="{ 'cursor-pointer': $store.socket?.status === 'disconnected', 'cursor-default': $store.socket?.status !== 'disconnected' }"
                    :title="$store.socket && tooltips[$store.socket.tooltipKey] ? tooltips[$store.socket.tooltipKey] : ($store.socket ? $store.socket.tooltipKey : '')"
                >
                    <span
                        x-show="$store.socket && $store.socket.enabled !== false"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full text-lg focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                        :class="{
                            'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400': $store.socket?.status === 'connected',
                            'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 animate-pulse': $store.socket?.status === 'connecting',
                            'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400': $store.socket?.status === 'disconnected'
                        }"
                        :aria-label="$store.socket && tooltips[$store.socket.tooltipKey] ? tooltips[$store.socket.tooltipKey] : ($store.socket ? $store.socket.tooltipKey : '')"
                    >
                        <span x-show="$store.socket?.status === 'connected'" aria-hidden="true">🟢</span>
                        <span x-show="$store.socket?.status === 'connecting'" aria-hidden="true">🟡</span>
                        <span x-show="$store.socket?.status === 'disconnected'" aria-hidden="true">🔴</span>
                    </span>
                </div>
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">


                        <!-- Theme Toggle Item -->
                        <div
                            class="block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out">
                            <div class="flex items-center justify-between">
                                <span>{{ __('Theme') }}</span>
                                <x-theme-toggle />
                            </div>
                        </div>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if(Auth::check() && in_array(Auth::user()->role, ['chief', 'manager', 'purchasing']))
                <x-responsive-nav-link :href="route('requests.approvals')" :active="request()->routeIs('requests.approvals')">
                    {{ __('Pending Approvals') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('requests.my-actions')" :active="request()->routeIs('requests.my-actions')">
                    {{ __('My Approvals / Rejections') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">


                <!-- Theme Toggle Item -->
                <div
                    class="px-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out">
                    <div class="flex items-center justify-between">
                        <span>{{ __('Theme') }}</span>
                        <x-theme-toggle />
                    </div>
                </div>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>