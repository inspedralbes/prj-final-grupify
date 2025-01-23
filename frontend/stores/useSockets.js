export const useSocket = () => {
  const { $socket } = useNuxtApp();
  const role = ref("");
  const isConnected = ref(false);

  const connect = () => {
    if (import.meta.client) {
      role.value = localStorage.getItem("role") || "";

      if ($socket && !$socket.connected) {
        $socket.connect();

        $socket.on("connect", () => {
          isConnected.value = true;
          if (role.value) {
            $socket.emit("register_role", role.value);
          }
        });

        $socket.on("disconnect", () => {
          isConnected.value = false;
        });
      }
    }
  };

  return {
    connect,
    sendNotification: (message) => {
      if ($socket?.connected && role.value === "profesor") {
        $socket.emit("notificacion", {
          message,
          from: JSON.parse(localStorage.getItem("user"))?.name || "Profesor",
          timestamp: new Date().toISOString(),
          target: 'student'
        });
      }
    },
    socket: $socket,
    isConnected
  };
};