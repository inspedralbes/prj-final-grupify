export default defineNuxtRouteMiddleware(async (to) => {
    const authStore = useAuthStore();
    authStore.initialize();
  
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      return navigateTo('/login');
    }
  });