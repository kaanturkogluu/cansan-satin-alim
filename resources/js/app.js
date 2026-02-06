import './bootstrap';

import Alpine from 'alpinejs';
import { bootstrapEcho } from './echo';

window.Alpine = Alpine;

// WebSocket (Reverb) bağlantısı ve socket store - Alpine.start() öncesi
bootstrapEcho();

// Bekleyen onaylar listesi: socket ile anlık ekleme/çıkarma (config window'dan okunur)
Alpine.data('approvalsList', () => {
  const config = window._approvalsListConfig || {};
  delete window._approvalsListConfig;
  return {
    requests: Array.isArray(config.initialRequests) ? config.initialRequests : [],
    get list() {
      return this.requests || [];
    },
    showBaseUrl: config.showBaseUrl || '',
    noPendingText: config.noPendingText || '',
    reviewText: config.reviewText || 'Review',
    doubleClickTitle: config.doubleClickTitle || '',

    formatDate(iso) {
      if (!iso) return '-';
      try {
        const d = new Date(iso);
        return isNaN(d.getTime()) ? iso : d.toISOString().slice(0, 10);
      } catch {
        return iso;
      }
    },

    init() {
      const channelName = config.channel;
      if (!channelName || typeof window.Echo === 'undefined') return;
      window.Echo.private(channelName)
        .listen('.RequestListUpdate', (e) => {
          if (!e || !e.request) return;
          const { action, request } = e;
          if (action === 'added') {
            this.requests = [request, ...this.requests];
          } else if (action === 'removed') {
            this.requests = this.requests.filter((r) => r.id !== request.id);
          }
        });
    },
  };
});

// Mühendis "Taleplerim" listesi: socket ile anlık ekleme/güncelleme (config window'dan okunur)
Alpine.data('myRequestsList', () => {
  const config = window._myRequestsListConfig || {};
  delete window._myRequestsListConfig;
  return {
    requests: Array.isArray(config.initialRequests) ? config.initialRequests : [],
    get list() {
      return this.requests || [];
    },
    showBaseUrl: config.showBaseUrl || '',
    statusLabels: config.statusLabels || {},
    viewText: config.viewText || 'View',
    doubleClickTitle: config.doubleClickTitle || '',
    formatDate(iso) {
      if (!iso) return '-';
      try {
        const d = new Date(iso);
        return isNaN(d.getTime()) ? iso : d.toISOString().slice(0, 10);
      } catch {
        return iso;
      }
    },
    statusClass(status) {
      if (status === 'approved') return 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200';
      if (status === 'rejected') return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200';
      return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200';
    },
    init() {
      const channelName = config.channel;
      if (!channelName || typeof window.Echo === 'undefined') return;
      window.Echo.private(channelName)
        .listen('.RequestListUpdate', (e) => {
          if (!e || !e.request) return;
          const { action, request } = e;
          if (action === 'added') {
            this.requests = [{ ...request, status: request.status || 'pending_chief' }, ...this.requests];
          } else if (action === 'updated') {
            const i = this.requests.findIndex((r) => r.id === request.id);
            if (i >= 0) this.requests[i] = { ...this.requests[i], ...request };
            else this.requests = [request, ...this.requests];
          }
        });
    },
  };
});

// Navbar bildirim listesi: WebSocket ile anında yeni bildirim ekleme (sayfa yenilenmeden)
function notificationListData() {
  const config = window._notificationListConfig || {};
  const list = Array.isArray(config.list) ? config.list : [];
  const userId = config.userId || '';
  const readRedirectBase = config.readRedirectBase || '';

  return {
    open: false,
    list: list,
    readRedirectBase: readRedirectBase,
    notificationsLabel: config.notificationsLabel || 'Notifications',
    unreadLabel: config.unreadLabel || 'unread',
    noNewLabel: config.noNewLabel || 'No new notifications.',

    timeAgo(iso) {
      if (!iso) return '';
      try {
        const d = new Date(iso);
        const now = new Date();
        const s = Math.floor((now - d) / 1000);
        if (s < 60) return (s <= 1 ? '1' : s) + ' sn';
        if (s < 3600) return Math.floor(s / 60) + ' dk';
        if (s < 86400) return Math.floor(s / 3600) + ' sa';
        if (s < 604800) return Math.floor(s / 86400) + ' gün';
        return d.toLocaleDateString();
      } catch {
        return iso;
      }
    },

    init() {
      const self = this;
      const unreadUrl = config.unreadUrl || '/notifications/unread';

      function pushNotification(payload) {
        // Laravel sends flat payload: { id, type, request_no, message, created_at_iso, ... } (no nested .data)
        const data = payload?.data ?? payload;
        if (!data || typeof data !== 'object') return;
        const id = payload?.id ?? data?.id;
        const newItem = {
          id: id || 'broadcast-' + Date.now(),
          data: data,
          created_at: data.created_at_iso || payload?.created_at || new Date().toISOString(),
        };
        self.list = [newItem, ...self.list];
      }

      // WebSocket: anında bildirim (Reverb açıksa)
      if (userId && typeof window.Echo !== 'undefined') {
        const channelName = 'App.Models.User.' + userId;
        window.Echo.private(channelName).notification(pushNotification);
      }

      // Polling yedeği: sayfa yenilenmeden yeni bildirimler (WebSocket kapalı/hatalı olsa da çalışır)
      const pollInterval = 15 * 1000;
      const poll = () => {
        fetch(unreadUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
          .then((r) => r.ok ? r.json() : null)
          .then((data) => {
            if (!data?.notifications?.length) return;
            const existingIds = new Set(self.list.map((n) => n.id));
            const newOnes = data.notifications.filter((n) => !existingIds.has(n.id));
            if (newOnes.length) {
              self.list = [
                ...newOnes.map((n) => ({
                  id: n.id,
                  data: n.data || {},
                  created_at: n.created_at || new Date().toISOString(),
                })),
                ...self.list,
              ];
            }
          })
          .catch(() => {});
      };
      const pollTimer = setInterval(poll, pollInterval);
      poll();
    },
  };
}
Alpine.data('notificationList', notificationListData);
window.notificationList = notificationListData;

Alpine.start();
