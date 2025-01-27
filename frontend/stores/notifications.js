import { defineStore } from 'pinia';
import { useAuthStore } from '~/stores/auth';

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
  }),

  actions: {
    async fetchNotifications() {
      const authStore = useAuthStore();
      const userId = authStore.user?.id;
      
      if (!userId) {
        console.error('Usuario no autenticado');
        return;
      }

      try {
        const response = await $fetch(
          `/api/notifications/student/${userId}`,
          {
            headers: {
              Authorization: `Bearer ${useCookie('auth_token').value}`,
            }
          }
        );

        const serverNotifications = Array.isArray(response) ? response : [];
        const localNotifications = this.getLocalNotifications();
        
        this.notifications = this.mergeNotifications(serverNotifications, localNotifications);
        this.saveLocalNotifications();

      } catch (error) {
        console.error('Error fetching notifications:', error);
        this.notifications = this.getLocalNotifications();
      }
    },

    markAsRead(notificationId) {
      this.notifications = this.notifications.map(notification => {
        if (notification.id === notificationId) {
          const updated = { ...notification, read: true };
          if (updated.isLocal) this.saveLocalNotifications();
          return updated;
        }
        return notification;
      });
      this.saveLocalNotifications();
    },

    addNotification(notification) {
      if (!notification?.message || !notification?.teacher_name) {
        console.error('Notificación inválida:', notification);
        return;
      }

      const withLocalFlag = { 
        title: notification.title || 'Nueva notificación',
        message: notification.message,
        teacher_name: notification.teacher_name,
        priority: notification.priority || 'normal',
        created_at: notification.created_at || new Date().toISOString(),
        id: `local-${Date.now()}-${Math.random().toString(36).slice(2, 11)}`,
        read: false,
        isLocal: true
      };
      
      this.notifications = [withLocalFlag, ...this.notifications];
      this.saveLocalNotifications();
    },

    getLocalNotifications() {
      if (import.meta.client) {
        try {
          const local = localStorage.getItem('notifications');
          return local ? JSON.parse(local) : [];
        } catch (error) {
          console.error('Error loading local notifications:', error);
          return [];
        }
      }
      return [];
    },

    saveLocalNotifications() {
      if (import.meta.client) {
        try {
          const localNotifications = this.notifications
            .filter(n => n.isLocal && !n.read)
            .map(({ isLocal, ...rest }) => rest); // Remove isLocal flag before saving
          localStorage.setItem('notifications', JSON.stringify(localNotifications));
        } catch (error) {
          console.error('Error saving local notifications:', error);
        }
      }
    },

    mergeNotifications(serverNotifications, localNotifications) {
      const validServer = Array.isArray(serverNotifications) ? serverNotifications : [];
      const validLocal = Array.isArray(localNotifications) ? localNotifications : [];
      
      const serverIds = new Set(validServer.map(n => n.id));
      return [
        ...validServer,
        ...validLocal.filter(n => 
          !serverIds.has(n.id) && 
          !n.read &&
          typeof n.id === 'string'
        )
      ];
    },

    clearLocalNotifications() {
      if (import.meta.client) {
        localStorage.removeItem('notifications');
      }
    }
  },

  getters: {
    unreadCount: (state) => state.notifications.filter(n => !n.read).length,
    
    sortedNotifications: (state) => 
      [...state.notifications].sort((a, b) => 
        new Date(b.created_at) - new Date(a.created_at))
  }
});