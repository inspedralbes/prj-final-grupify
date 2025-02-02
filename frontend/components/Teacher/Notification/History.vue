<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h2 class="text-xl font-semibold mb-6">Historial de Notificacions</h2>

      <div v-if="loading" class="text-gray-500">Cargant historial...</div>

      <div v-else>
        <div v-if="notifications.length === 0" class="text-gray-500">
          No has enviat cap notificació
        </div>
        <ul>
          <li v-for="notification in notifications" :key="notification.id"
            class="mb-4 p-4 border rounded hover:bg-gray-50 transition-colors">
            <h3 class="font-bold">{{ notification.title }}</h3>
            <p>{{ notification.body }}</p>
            <small class="text-gray-500 block">
              {{ notification.status === 'pending' ? 'Programada per:' : 'Enviada el:' }}
              {{ formatDate(notification.scheduled_at || notification.created_at) }}
            </small>
            <span class="text-xs px-2 py-1 rounded-full" :class="{
              'bg-yellow-100 text-yellow-800': notification.status === 'pending',
              'bg-green-100 text-green-800': notification.status === 'sent',
              'bg-red-100 text-red-800': notification.status === 'canceled'
            }">
              {{ notification.status === 'pending' ? 'Pendent' : notification.status === 'sent' ? 'Enviada' :
                'Cancelada' }}
            </span>
            <div v-if="notification.status === 'pending'" class="mt-2">
              <button @click="cancelNotification(notification.id)" class="text-red-600 text-sm hover:underline">
                Cancelar
              </button>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '~/stores/auth';

const authStore = useAuthStore();
const notifications = ref([]);
const loading = ref(true);

function formatDate(dateString) {
  return new Date(dateString).toLocaleString();
}

async function fetchNotifications() {
  try {
    const data = await $fetch('http://localhost:8000/api/teacher-notifications', {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        Accept: 'application/json',
      },
    });
    notifications.value = data.notifications;
  } catch (error) {
    console.error('Error al obtener historial:', error);
    console.log('Detalles del error:', error.data); // Agrega esto
  } finally {
    loading.value = false;
  }
}

async function cancelNotification(id) {
  if (!confirm('¿Seguro que quieres cancelar esta notificación?')) return;

  try {
    await $fetch(`http://localhost:8000/api/notifications/${id}`, {
      method: 'DELETE',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        Accept: 'application/json',
      },
    });

    // Recargar la lista de notificaciones
    await fetchNotifications();
    alert('Notificación cancelada correctamente');
  } catch (error) {
    alert('Error al cancelar la notificación');
  }
}

onMounted(() => {
  fetchNotifications();
});
</script>