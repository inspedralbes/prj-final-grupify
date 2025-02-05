import { useAuthStore } from '@/stores/auth'; // Importa el store de autenticación

export default async function ({ $config }) {
  if ('serviceWorker' in navigator) {
    try {
      const authStore = useAuthStore(); // Instancia el store de autenticación

      const registration = await navigator.serviceWorker.register('/sw.js');
      console.log('Service Worker registrado con éxito:', registration);

      // Obtener la suscripción actual
      let subscription = await registration.pushManager.getSubscription();

      if (!subscription) {
        // Generar una nueva suscripción
        const publicVapidKey = $config.publicVapidKey; // Clave pública VAPID desde runtimeConfig

        subscription = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(publicVapidKey)
        });
      }

      console.log('Suscripción obtenida:', subscription);

      // Verificar si el usuario está autenticado
      if (authStore.isAuthenticated && authStore.token) {
        // Enviar la suscripción al servidor
        await fetch('http://localhost:8000/api/notifications/subscribe', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authStore.token}`, // Usar el token del store
          },
          body: JSON.stringify(subscription)
        });

        console.log('Suscripción enviada al servidor correctamente.');
      } else {
        console.warn('Usuario no autenticado. No se envió la suscripción al servidor.');
      }
    } catch (error) {
      console.error('Error al registrar el Service Worker o la suscripción:', error);
    }
  }
}

// Función para convertir la clave VAPID
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}
