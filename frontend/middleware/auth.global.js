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
  if (authStore.token) {
    // Verificamos si no hay usuario O si hay usuario pero no tiene role (incompleto)
    if (!authStore.user || !authStore.user.role) {
      console.log("User data incomplete, loading from localStorage and server...");

      // Intenta cargar el usuario desde localStorage primero
      const userFromStorage = localStorage.getItem("user");
      if (userFromStorage) {
        try {
          const parsedUser = JSON.parse(userFromStorage);
          console.log("User data loaded from localStorage:", parsedUser.role?.name);

          // Si tenemos datos en localStorage pero no en el store, inicializar
          if (parsedUser && parsedUser.role && !authStore.user) {
            console.log("Setting user from localStorage");
            authStore.user = parsedUser;
          }
        } catch (e) {
          console.error("Error parsing user from localStorage:", e);
        }
      }

      // De todas formas, intentamos inicializar desde el servidor
      await authStore.initialize();

      // Si después de inicializar aún no hay usuario o role, intentamos checkAuth
      if (!authStore.user || !authStore.user.role) {
        console.log("Still missing user data, running checkAuth...");
        try {
          await authStore.checkAuth();
        } catch (e) {
          console.error("Error in checkAuth:", e);
        }
      }
    } else {
      console.log("User data complete:", authStore.user.role.name);
    }
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
    tutor: "/professor/dashboard", // Los tutores usan el mismo dashboard que los profesores
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

    // Los tutores ahora tienen los mismos permisos que los profesores
    // No hay restricciones específicas para tutores
  }

  // Si intenta acceder a rutas específicas de orientador siendo otro rol
  if (to.path.includes('/orientador') && userRole !== 'orientador') {
    return navigateTo(dashboardRoutes[userRole] || '/login');
  }

  // Si intenta acceder a rutas específicas de tutor siendo otro rol
  if (to.path.includes('/tutor') && userRole !== 'tutor') {
    // Si es tutor, redirigir al dashboard de profesor
    if (userRole === 'tutor') {
      return navigateTo('/professor/dashboard');
    }
    // Si no es tutor, redirigir a su dashboard correspondiente
    return navigateTo(dashboardRoutes[userRole] || '/login');
  }

  // Si intenta acceder a rutas de admin pero no es admin
  if (to.path.includes('/admin') && userRole !== 'admin') {
    return navigateTo(dashboardRoutes[userRole] || '/login');
  }
});