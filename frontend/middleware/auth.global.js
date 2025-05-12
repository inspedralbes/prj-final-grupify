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
  
  // Registrar información para depuración
  console.log("Auth middleware running for path:", to.path);
  console.log("Auth state:", {
    token: !!authStore.token,
    isAuthenticated: authStore.isAuthenticated,
    userRole: authStore.userRole
  });
  
  // Inicializamos solo si hay token pero no hay usuario
  if (authStore.token && !authStore.user) {
    console.log("Initializing auth store...");
    await authStore.initialize();
  }

  // Verificación de autenticación para todas las rutas no públicas
  if (!authStore.isAuthenticated) {
    console.log("User not authenticated, redirecting to login");
    return navigateTo('/login');
  }

  // Rutas del dashboard por rol
  const dashboardRoutes = {
    admin: "/admin/dashboard",
    profesor: "/professor/dashboard",
    alumno: "/alumne/dashboard",
    tutor: "/tutor/dashboard",
    orientador: "/orientador/dashboard"
  };
  
  // Obtenemos el rol usando los getters
  const userRole = authStore.userRole;
  console.log("User role detected:", userRole);
  console.log("Dashboard route for role:", dashboardRoutes[userRole]);
  
  // Si intenta acceder a rutas de alumno pero no es alumno
  if (to.path.includes('/alumne') && !authStore.isAlumno) {
    return navigateTo(dashboardRoutes[userRole] || '/login');
  }

  // Si intenta acceder a rutas de profesor
  if (to.path.includes('/professor')) {
    // Los roles de profesor, tutor y orientador pueden acceder al área de profesor
    if (userRole !== 'profesor' && userRole !== 'tutor' && userRole !== 'orientador') {
      return navigateTo(dashboardRoutes[userRole] || '/login');
    }
    
    // Restricciones específicas para profesores
    if (userRole === 'profesor') {
      // Los profesores no pueden acceder a rutas de sociograma, cesc y gráficas
      if (to.path.includes('/sociogram') || to.path.includes('/cesc') || to.path.includes('/grafico')) {
        return navigateTo('/professor/dashboard');
      }
    }
    
    // Restricciones específicas para orientadores
    if (userRole === 'orientador') {
      // Los orientadores no pueden acceder a la asignación de formularios
      if (to.path.includes('/formularis/assignar')) {
        return navigateTo('/orientador/dashboard');
      }
    }
    
    // Restricciones específicas para tutores
    if (userRole === 'tutor') {
      // Los tutores no pueden acceder a ver respuestas detalladas
      if ((to.path.includes('/sociogram') || to.path.includes('/cesc') || to.path.includes('/grafico')) && 
          !to.path.includes('/estat')) {
        return navigateTo('/tutor/dashboard');
      }
    }
  }
  
  // Si intenta acceder a rutas específicas de orientador siendo otro rol
  if (to.path.includes('/orientador') && userRole !== 'orientador') {
    return navigateTo(dashboardRoutes[userRole] || '/login');
  }
  
  // Si intenta acceder a rutas específicas de tutor siendo otro rol
  if (to.path.includes('/tutor') && userRole !== 'tutor') {
    return navigateTo(dashboardRoutes[userRole] || '/login');
  }
  
  // Si intenta acceder a rutas de admin pero no es admin
  if (to.path.includes('/admin') && userRole !== 'admin') {
    return navigateTo(dashboardRoutes[userRole] || '/login');
  }
});