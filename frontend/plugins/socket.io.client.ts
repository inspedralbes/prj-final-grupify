import { io } from 'socket.io-client';

export default defineNuxtPlugin((nuxtApp) => {
  const socket = io('http://localhost:5000', {
    autoConnect: true,
    withCredentials: true,
    transports: ['websocket', 'polling']
  });

  const user = JSON.parse(localStorage.getItem('user') || '{}');
  
  if (user?.id) {
    socket.connect();
    socket.emit('register_user', user.id);
  }

  return {
    provide: {
      socket: socket
    }
  };
});