const http = require('http');

const server = http.createServer((req, res) => {
  // Configurar la respuesta HTTP
  res.statusCode = 200;
  res.setHeader('Content-Type', 'text/plain');
  
  // Responder a todas las solicitudes con un mensaje simple
  res.end('Servidor Node.js funcionando en el puerto 5000!\n');
});

// Especificar el puerto y manejar errores
server.listen(5000, '0.0.0.0', () => {
  console.log('Servidor corriendo en http://localhost:5000/');
});

// Manejar errores del servidor
server.on('error', (error) => {
  if (error.code === 'EADDRINUSE') {
    console.error('Error: El puerto 5000 ya está en uso');
  } else {
    console.error('Error al iniciar el servidor:', error);
  }
});