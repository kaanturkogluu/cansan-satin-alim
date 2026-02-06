/**
 * Laravel Echo - Merkezi WebSocket (Reverb) bağlantı yönetimi
 * Bağlantı durumu Alpine.js store üzerinden global erişilebilir.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Pusher'ı window'a ekle (Laravel Echo bunu bekler)
window.Pusher = Pusher;

/** Reverb env değişkenleri (Vite ile build'de enjekte edilir) */
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST ?? 'localhost';
const reverbPort = import.meta.env.VITE_REVERB_PORT ?? '8080';
const reverbScheme = String(import.meta.env.VITE_REVERB_SCHEME ?? 'http').toLowerCase();
/** Yerel geliştirmede ws:// kullan (wss değil). localhost/127.0.0.1 için TLS kapalı. */
const isLocalHost = /^(localhost|127\.0\.0\.1)$/.test(reverbHost);
const forceTLS = !isLocalHost && reverbScheme === 'https';

/** Socket durumları: connected | connecting | disconnected */
const SOCKET_CONNECTED = 'connected';
const SOCKET_CONNECTING = 'connecting';
const SOCKET_DISCONNECTED = 'disconnected';

/**
 * Alpine.js store - tüm uygulama içinde reaktif socket durumu
 * Kullanım: $store.socket.status, $store.socket.tooltip, $store.socket.reconnect()
 */
function registerSocketStore() {
    if (typeof window.Alpine === 'undefined') return;
    window.Alpine.store('socket', {
        /** Reverb kullanılmıyorsa (VITE_REVERB_APP_KEY yok) göstergenin gizlenmesi için */
        enabled: true,
        status: SOCKET_DISCONNECTED,
        /** Tooltip metni için key (i18n veya doğrudan metin) */
        tooltipKey: 'socket.disconnected',

        setState(newStatus, tooltipKey) {
            this.status = newStatus;
            this.tooltipKey = tooltipKey ?? this.tooltipKey;
        },

        /** Manuel yeniden bağlanma (navbar tıklanınca) */
        reconnect() {
            if (window.__laravelEchoInstance?.connector?.pusher) {
                this.setState(SOCKET_CONNECTING, 'socket.connecting');
                window.__laravelEchoInstance.connector.pusher.connect();
            }
        },
    });
}

/**
 * Echo instance oluşturur ve bağlantı lifecycle event'lerini store ile senkronize eder
 */
function initEcho() {
    const store = window.Alpine?.store?.('socket');
    if (!reverbKey) {
        if (store) store.enabled = false;
        return null;
    }

    const echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS,
        disableStats: true,
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
                Accept: 'application/json',
            },
        },
    });

    const storeForEvents = window.Alpine?.store?.('socket');
    const setStore = (status, key) => {
        if (storeForEvents) {
            storeForEvents.setState(status, key);
        }
    };

    const pusher = echo.connector?.pusher;
    if (!pusher) return echo;

    const connection = pusher.connection;

    connection.bind('connecting', () => setStore(SOCKET_CONNECTING, 'socket.connecting'));
    connection.bind('connected', () => setStore(SOCKET_CONNECTED, 'socket.connected'));
    connection.bind('disconnected', () => setStore(SOCKET_DISCONNECTED, 'socket.disconnected'));
    connection.bind('failed', () => setStore(SOCKET_DISCONNECTED, 'socket.disconnected'));
    connection.bind('error', () => setStore(SOCKET_DISCONNECTED, 'socket.disconnected'));
    connection.bind('state_change', (states) => {
        if (states.current === 'connected') setStore(SOCKET_CONNECTED, 'socket.connected');
        else if (states.current === 'connecting' || states.current === 'unavailable') setStore(SOCKET_CONNECTING, 'socket.connecting');
        else setStore(SOCKET_DISCONNECTED, 'socket.disconnected');
    });

    // İlk durum: bağlanıyor (Echo connect tetiklenir)
    setStore(SOCKET_CONNECTING, 'socket.connecting');

    return echo;
}

/**
 * Echo'yu başlatır. Alpine yüklendikten sonra store kayıtlı olmalı.
 */
export function bootstrapEcho() {
    registerSocketStore();
    const echo = initEcho();
    if (echo) {
        window.Echo = echo;
        window.__laravelEchoInstance = echo;
    }
}

export default bootstrapEcho;
