import { io } from 'socket.io-client';

export default defineNuxtPlugin((nuxtApp) => {
  
  const socket = io('http://localhost:5000', {
    autoConnect: false,
    withCredentials: true,
    transports: ['websocket', 'polling']
  });

  return {
    provide: {
      socket: socket
    }
  };
});