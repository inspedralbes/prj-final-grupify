<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-xl font-bold mb-4">Notificacions</h2>
    <div v-if="loading" class="text-gray-500">Cargant notificacions...</div>
    <div v-else>
      <div v-if="notifications.length === 0" class="text-gray-500">
        No hi ha notificacions.
      </div>
      <ul>
        <li
          v-for="notification in notifications"
          :key="notification.id"
          class="mb-4 p-4 border rounded hover:bg-gray-50 transition-colors"
        >
          <h3 class="font-bold">{{ notification.title }}</h3>
          <p>{{ notification.body }}</p>
          <small class="text-gray-500">{{ formatDate(notification.created_at) }}</small>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '~/stores/auth';

const authStore = useAuthStore();
const notifications = ref([]);
const loading = ref(true);

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleString();
}

async function fetchNotifications() {
  try {
    const data = await $fetch('http://localhost:8000/api/notifications', {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        Accept: 'application/json'
      }
    });
    notifications.value = data.notifications;
  } catch (error) {
    console.error('Error al obtener notificaciones:', error);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchNotifications();
});
</script>