import { io } from 'socket.io-client';

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig();
  
  const socket = io(config.public.SOCKET_URL, {
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