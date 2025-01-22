<script setup>
const { connect, sendNotification, socket } = useSocket();
const notificationText = ref('');

onMounted(() => {
  connect();
});

const handleSend = () => {
  if (notificationText.value.trim()) {
    sendNotification(notificationText.value);
    notificationText.value = '';
  }
};
</script>

<template>
  <div class="notification-panel">
    <h3 class="text-xl font-bold mb-4">Enviar Notificación</h3>
    <textarea
      v-model="notificationText"
      placeholder="Escribe tu mensaje..."
      class="w-full p-2 border rounded mb-4"
      rows="4"
    ></textarea>
    <button
      @click="handleSend"
      class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors"
      :disabled="!socket?.connected"
    >
      {{ socket?.connected ? 'Enviar a alumnos' : 'Conectando...' }}
    </button>
  </div>
</template>