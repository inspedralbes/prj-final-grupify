<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <h2 class="text-xl font-bold mb-4">Notificacions</h2>
    <!-- Botón para activar las notificaciones push -->
    <button @click="activatePush" class="mb-4 px-4 py-2 bg-blue-500 text-white rounded">
      Activar Notificaciones
    </button>
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

// Función para formatear fechas
function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleString();
}

// Función para obtener las notificaciones del usuario
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
    console.error('Error al obtener notificacions:', error);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchNotifications();
});

// Función para convertir una cadena Base64 URL-safe en Uint8Array
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/-/g, '+')
    .replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

// Función para activar las notificaciones push
async function activatePush() {
  if (!('serviceWorker' in navigator)) {
    alert('Service Workers no son soportados en este navegador.');
    return;
  }
  if (!('PushManager' in window)) {
    alert('La API Push no es soportada en este navegador.');
    return;
  }

  try {
    // Registra el Service Worker (asegúrate de que la ruta sea correcta)
    const registration = await navigator.serviceWorker.register('/sw.js');
    console.log('Service Worker registrado:', registration);

    // Solicita permiso para notificaciones
    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
      alert('Permiso para notificaciones denegado.');
      return;
    }

    // Clave pública VAPID (debe coincidir con la configurada en el backend)
    // Reemplaza 'TU_PUBLIC_VAPID_KEY' por tu clave pública sin encabezados ni saltos de línea.
    const vapidPublicKey = 'BJfWWy6SFL83diyANaMQIiYHHmAYbZUPEGbi7t_R3WOpp7bQO1R9XGROJoiRPe30k5JtWE_N6MnR-IW6lFZpOkg';

    // Verificar si ya existe una suscripción; si es así, desuscribirla
    const existingSubscription = await registration.pushManager.getSubscription();
    if (existingSubscription) {
      console.log('Suscripción existente encontrada. Cancelando suscripción previa.');
      await existingSubscription.unsubscribe();
    }

    // Crear una nueva suscripción
    const subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(vapidPublicKey)
    });
    console.log('Nueva suscripción creada:', subscription);

    // Enviar la suscripción al backend. Se utiliza subscription.toJSON() para obtener la estructura correcta:
    // {
    //   endpoint: "...",
    //   expirationTime: null,
    //   keys: { p256dh: "...", auth: "..." }
    // }
    const response = await $fetch('http://localhost:8000/api/push-subscriptions', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${authStore.token}`
      },
      body: JSON.stringify(subscription.toJSON())
    });
    console.log('Respuesta del backend:', response);
    alert('Te has suscrito correctamente a las notificaciones push.');
  } catch (error) {
    console.error('Error al activar las notificaciones push:', error);
    alert('Error al activar las notificaciones push.');
  }
}
</script>
