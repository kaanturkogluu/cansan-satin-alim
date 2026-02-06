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
      if (!userId || typeof window.Echo === 'undefined') return;
      const channelName = 'App.Models.User.' + userId;
      window.Echo.private(channelName).notification((payload) => {
        const n = payload?.notification || payload;
        const data = n?.data || n;
        const id = n?.id || data?.id;
        if (!id || !data) return;
        this.list = [
          {
            id: id,
            data: data,
            created_at: data.created_at_iso || n.created_at || new Date().toISOString(),
          },
          ...this.list,
        ];
      });
    },
  };
}
Alpine.data('notificationList', notificationListData);
window.notificationList = notificationListData;

Alpine.start();
