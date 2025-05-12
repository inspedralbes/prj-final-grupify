<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-xl font-bold mb-4 flex items-center">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6 text-primary mr-2"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 17h5l-1.405-1.405A2 2 0 0018 14V9a6 6 0 10-12 0v5a2 2 0 00-1.595 1.595L4 17h5m6 0a3 3 0 11-6 0"
        />
      </svg>
      Notificacions
    </h2>
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
          <small class="text-gray-500">{{
            formatDate(notification.created_at)
          }}</small>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useAuthStore } from "~/stores/authStore";

const authStore = useAuthStore();
const notifications = ref([]);
const loading = ref(true);

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleString();
}

async function fetchNotifications() {
  try {
    const data = await $fetch("https://api.grupify.cat/api/notifications", {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        Accept: "application/json",
      },
    });
    notifications.value = data.notifications;
  } catch (error) {
    console.error("Error al obtener notificaciones:", error);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchNotifications();
});
</script>
