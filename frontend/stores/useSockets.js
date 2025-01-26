// useSockets.js (versión final)
export const useSocket = () => {
  const { $socket } = useNuxtApp();
  const role = ref("");
  const isConnected = ref(false);

  const connect = () => {
    if (import.meta.client) {
      role.value = localStorage.getItem("role") || "";
      const user = JSON.parse(localStorage.getItem("user") || "{}");

      if ($socket && !$socket.connected) {
        $socket.connect();

        $socket.on("connect", () => {
          isConnected.value = true;
          if (role.value && user?.id) {
            $socket.emit("register", {
              role: role.value.toLowerCase(),
              userId: user.id
            });
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
    socket: $socket,
    isConnected
  };
};