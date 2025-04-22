import { defineNuxtRouteMiddleware, navigateTo } from '#app';
import { useAuthStore } from '~/stores/authStore';

export default defineNuxtRouteMiddleware(async (to) => {
  // Rutas públicas que no requieren autenticación
  const publicRoutes = ['/login', '/register', '/', '/recuperar-password'];
  
  // Si la ruta es pública, permitir acceso sin verificación
  if (publicRoutes.includes(to.path) || to.path.startsWith('/public/')) {
    return;
  }
  
  const authStore = useAuthStore();
  
  // Inicializamos solo si hay token pero no hay usuario
  if (authStore.token && !authStore.user) {
    await authStore.initialize();
  }

  // Verificación de autenticación para todas las rutas no públicas
  if (!authStore.isAuthenticated) {
    return navigateTo('/login');
  }

  // Rutas del dashboard por rol
  const dashboardRoutes = {
    admin: "/admin/dashboard",
    profesor: "/professor/dashboard",
    alumno: "/alumne/dashboard"
  };
  
  // Obtenemos el rol usando los getters
  const userRole = authStore.userRole;
  
  // Si intenta acceder a rutas de alumno pero no es alumno
  if (to.path.includes('/alumne') && !authStore.isAlumno) {
    return navigateTo(dashboardRoutes[userRole] || '/login');
  }

  // Si intenta acceder a rutas de profesor pero no es profesor
  if (to.path.includes('/professor') && !authStore.isProfesor) {
    return navigateTo(dashboardRoutes[userRole] || '/login');
  }
  
  // Si intenta acceder a rutas de admin pero no es admin
  if (to.path.includes('/admin') && userRole !== 'admin') {
    return navigateTo(dashboardRoutes[userRole] || '/login');
  }
});