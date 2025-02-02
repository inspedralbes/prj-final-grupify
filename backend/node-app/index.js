const express = require('express');
const { createServer } = require('node:http');
const { Server } = require('socket.io');

const app = express();
const server = createServer(app);

const io = new Server(server, {
  cors: {
    origin: 'https://grupify.cat',
    methods: ['GET', 'POST'],
    allowedHeaders: ['Content-Type', 'Authorization'],
    credentials: true
  },
  transports: ['websocket', 'polling']
});

// Almacén de usuarios conectados: { userId: socketId }
const onlineUsers = new Map();

io.on('connection', (socket) => {
  console.log('Usuario conectado:', socket.id);

  // Registrar usuario autenticado
  socket.on('register_user', (userId) => {
    onlineUsers.set(userId, socket.id);
    console.log(`Usuario registrado: ${userId}`);
    io.emit('user_online', userId); // Notificar a todos
  });

  socket.on('disconnect', () => {
    // Encontrar y eliminar el usuario desconectado
    const entries = Array.from(onlineUsers.entries());
    const [userId] = entries.find(([_, sid]) => sid === socket.id) || [];
    
    if (userId) {
      onlineUsers.delete(userId);
      io.emit('user_offline', userId); // Notificar a todos
      console.log(`Usuario desconectado: ${userId}`);
    }
  });
});

server.listen(5000, () => {
  console.log('🚀 Servidor Socket.IO escuchando en puerto 5000');
});