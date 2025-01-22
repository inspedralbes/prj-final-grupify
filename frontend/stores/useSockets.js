export const useSocket = () => {
    const { $socket } = useNuxtApp();
    const role = ref('');
  
    const connect = () => {
      if (import.meta.client) {
        role.value = localStorage.getItem('role') || '';
        
        if (!$socket.connected) {
          $socket.connect();
          
          $socket.on('connect', () => {
            if (role.value) {
              $socket.emit('register_role', role.value);
            }
          });
        }
      }
    };
  
    const sendNotification = (message) => {
      if ($socket?.connected && role.value === 'profesor') {
        $socket.emit('notificacion', {
          message,
          from: JSON.parse(localStorage.getItem('user'))?.name || 'Profesor'
        });
      }
    };
  
    return {
      connect,
      sendNotification,
      socket: $socket
    };
  };