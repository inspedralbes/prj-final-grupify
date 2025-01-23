<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { useNotificationStore } from '~/stores/notifications';
import { useSocket } from '~/stores/useSockets';
import { BellIcon } from '@heroicons/vue/24/outline';

const notificationStore = useNotificationStore();
const { connect, socket } = useSocket();
const authStore = useAuthStore(); // Asume que tienes un store de autenticación

onMounted(() => {
  connect();
  
  if (socket) {
    // Registrar al alumno con su ID real
    socket.emit('register_role', { 
      role: 'alumno', 
      userId: authStore.user.id // Obtener ID del usuario autenticado
    });

    socket.on("nueva-notificacion", data => {
      notificationStore.addNotification({
        message: data.message,
        teacher_name: data.from,
        created_at: data.timestamp
      });
    });
  }
});

onBeforeUnmount(() => {
  if (socket) {
    socket.off("nueva-notificacion");
  }
});

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('ca-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-xl font-semibold mb-6 flex items-center">
      <BellIcon class="h-6 w-6 text-primary mr-2" />
      Notificacions
      <span 
        v-if="notificationStore.unreadCount > 0" 
        class="ml-2 px-2 py-1 text-xs bg-red-500 text-white rounded-full"
      >
        {{ notificationStore.unreadCount }}
      </span>
    </h3>
    
    <div class="space-y-4">
      <div 
        v-if="notificationStore.sortedNotifications.length === 0" 
        class="text-center text-gray-500"
      >
        No hi ha notificacions.
      </div>
      
      <div v-else class="space-y-4">
        <div 
          v-for="notification in notificationStore.sortedNotifications" 
          :key="notification.id"
          :class="[
            'p-4 rounded-lg transition-all cursor-pointer',
            notification.read ? 'bg-gray-50' : 'bg-blue-50 hover:bg-blue-100'
          ]"
          @click="notificationStore.markAsRead(notification.id)"
        >
          <div class="flex items-start justify-between">
            <div>
              <h4 class="font-semibold text-gray-900">
                {{ notification.title }}
              </h4>
              <p class="text-sm text-gray-600 mt-1">
                {{ notification.message }}
              </p>
              <p class="text-xs text-gray-500 mt-2">
                De: {{ notification.teacher_name }} - 
                {{ formatDate(notification.created_at) }}
              </p>
            </div>
            <div 
              v-if="!notification.read" 
              class="h-2 w-2 bg-blue-500 rounded-full"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>