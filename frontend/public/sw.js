self.addEventListener('push', function (event) {
  let data = {};

  if (event.data) {
      data = event.data.json(); // Parsear el payload recibido
  }

  const title = data.title || 'Nueva notificación';
  const options = {
      body: data.body || 'Tienes una nueva notificación.',
      icon: data.icon || 'icono.png', // Cambia a tu ícono
      badge: data.badge || 'icono.png', // Opcional
      data: {
          url: data.url || '/' // URL a la que redirigir cuando se haga clic
      }
  };

  // Mostrar la notificación como un popup
  event.waitUntil(
      self.registration.showNotification(title, options)
  );
});

// Manejar el clic en la notificación
self.addEventListener('notificationclick', function (event) {
  event.notification.close();

  const url = event.notification.data.url || '/';
  event.waitUntil(
      clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
          for (const client of clientList) {
              if (client.url === url && 'focus' in client) {
                  return client.focus();
              }
          }
          if (clients.openWindow) {
              return clients.openWindow(url);
          }
      })
  );
});
