const express = require('express');
const { createServer } = require('node:http');
const { Server } = require('socket.io');

const app = express();
const server = createServer(app);
const io = new Server(server, {
  cors: {
    origin: process.env.FRONTEND_URL, // Usar variable de entorno
    methods: ["GET", "POST"]
  }
});

io.on('connection', (socket) => {
  console.log('Usuario conectado:', socket.id);

  socket.on('notificacion', (data) => {
    console.log('Recibida notificación:', data);
    io.emit('nueva-notificacion', data);
  });

  socket.on('disconnect', () => {
    console.log('Usuario desconectado:', socket.id);
  });
});

server.listen(5000, () => {
  console.log('Servidor Socket.io escuchando en *:5000');
});