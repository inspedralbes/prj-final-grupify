<script setup>
const notifications = ref([]);
const { connect, socket } = useSocket();

onMounted(() => {
  connect();

  if (socket) {
    socket.on("nueva-notificacion", data => {
      notifications.value.unshift(data);
    });
  }
});

onBeforeUnmount(() => {
  if (socket) {
    socket.off("nueva-notificacion");
  }
});
</script>

<template>
  <div class="notifications-container">
    <h3 class="text-xl font-bold mb-4">Notificaciones Recibidas</h3>
    <div
      v-for="(notif, index) in notifications"
      :key="index"
      class="bg-white p-4 rounded shadow mb-2"
    >
      <div class="flex justify-between items-center mb-2">
        <strong class="text-blue-600">{{ notif.from }}</strong>
        <span class="text-sm text-gray-500">
          {{ new Date(notif.timestamp).toLocaleDateString() }}
          {{ new Date(notif.timestamp).toLocaleTimeString() }}
        </span>
      </div>
      <p class="text-gray-800">{{ notif.message }}</p>
    </div>
  </div>
</template>
