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
  
          $socket.on("delivery-confirmation", (data) => {
            console.log(`Notificación entregada a ${data.count} alumnos`);
          });
        }
      }
    };
  
    return {
      connect,
      sendNotification: (message, priority = 'normal') => {
        if ($socket?.connected && role.value === "profesor") {
          return new Promise((resolve) => {
            $socket.emit("notificacion", {
              message,
              priority,
              from: JSON.parse(localStorage.getItem("user"))?.name || "Profesor",
              timestamp: new Date().toISOString(),
              target: 'student'
            }, (response) => {
              console.log('Confirmación de entrega:', response);
              resolve(response);
            });
          });
        }
        return Promise.resolve();
      },
      socket: $socket,
      isConnected
    };
  };