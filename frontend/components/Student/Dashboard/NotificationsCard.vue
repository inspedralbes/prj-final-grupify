<script setup>
import { onMounted, onBeforeUnmount } from "vue";
import { useNotificationStore } from "~/stores/notifications";
import { useSocket } from "~/stores/useSockets";
import { BellIcon } from "@heroicons/vue/24/outline";

const notificationStore = useNotificationStore();
const { connect, socket } = useSocket();

const handleNewNotification = data => {
  notificationStore.addNotification({
    message: data.message,
    teacher_name: data.from,
    priority: data.priority || "normal",
    created_at: data.timestamp || new Date().toISOString(),
  });
};

onMounted(async () => {
  try {
    await notificationStore.fetchNotifications();
    connect();

    if (socket) {
      socket.on("nueva-notificacion", handleNewNotification);
    }
  } catch (error) {
    console.error("Error initializing notifications:", error);
  }
});

onBeforeUnmount(() => {
  if (socket) {
    socket.off("nueva-notificacion", handleNewNotification);
  }
});

const formatDate = dateString => {
  return new Date(dateString).toLocaleDateString("ca-ES", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

// Configuración de colores por prioridad
const priorityStyles = {
  low: {
    border: "border-l-gray-400",
    bg: "bg-gray-50",
    badge: "bg-gray-200 text-gray-700",
  },
  normal: {
    border: "border-l-blue-400",
    bg: "bg-blue-50",
    badge: "bg-blue-200 text-blue-700",
  },
  high: {
    border: "border-l-red-400",
    bg: "bg-red-50",
    badge: "bg-red-200 text-red-700",
  },
};

const getPriorityLabel = priority => {
  return (
    {
      low: "Baja",
      normal: "Normal",
      high: "Alta",
    }[priority] || "Normal"
  );
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
        v-if="notificationStore.notifications.length === 0"
        class="text-center text-gray-500"
      >
        No hi ha notificacions.
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="notification in notificationStore.sortedNotifications"
          :key="notification.id"
          :class="[
            'p-4 rounded-lg transition-all cursor-pointer border-l-4',
            priorityStyles[notification.priority || 'normal'].border,
            priorityStyles[notification.priority || 'normal'].bg,
            notification.read ? 'opacity-75' : 'hover:shadow-md',
          ]"
          @click="notificationStore.markAsRead(notification.id)"
        >
          <div class="flex items-start justify-between">
            <div class="w-full">
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-semibold text-gray-900">
                  {{ notification.title }}
                </h4>
                <span
                  :class="[
                    'text-xs px-2 py-1 rounded-full',
                    priorityStyles[notification.priority || 'normal'].badge,
                  ]"
                >
                  {{ getPriorityLabel(notification.priority) }}
                </span>
              </div>
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
              class="h-2 w-2 bg-blue-500 rounded-full ml-2"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Transiciones suaves */
div[class*="border-l-"] {
  transition: all 0.2s ease-in-out;
}
</style>
