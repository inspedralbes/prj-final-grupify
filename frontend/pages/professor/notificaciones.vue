<script setup>
const { $socketIO: socket } = useNuxtApp();

onMounted(() => {
  socket.connect();
  
  socket.on('nueva-notificacion', (data) => {
    console.log('Nueva notificación:', data);
    // Aquí tu lógica para mostrar la notificación
  });
});

onBeforeUnmount(() => {
  socket.disconnect();
});

const enviarNotificacion = () => {
  socket.emit('notificacion', {
    mensaje: '¡Nueva actualización!',
    fecha: new Date()
  });
};
</script>

<template>
  <button @click="enviarNotificacion">
    Enviar notificación
  </button>
</template>