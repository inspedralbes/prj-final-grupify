<template>
  <div class="logout-wrapper">
    <div class="logout-content">
      <h2 class="text-2xl font-semibold text-gray-800 text-center">
        Estàs segur que vols tancar la sessió?
      </h2>
      <p class="text-center text-gray-600 mt-4">
        Després de tancar sessió, hauràs d'iniciar sessió de nou per accedir.
      </p>
      <div class="buttons mt-6 flex justify-center space-x-4">
        <button
          class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500"
          @click="handleLogout"
        >
          Tancar Sessió
        </button>
        <nuxt-link
          to="/alumne/dashboard"
          class="px-6 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 focus:ring-2 focus:ring-gray-400"
        >
          Cancel·lar
        </nuxt-link>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuthStore } from '~/stores/auth';

export default {
  name: "TancarSessio",
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  methods: {
    async handleLogout() {
      try {
        await this.authStore.logout();
        // Redirigir a la página de login y recargar
        window.location.href = '/login';  // Redirige a la página de login
      } catch (error) {
        console.error('Error cerrando sesión:', error);
        alert("Error al cerrar sesión. Intenta nuevamente.");
      }
    }
  }
};
</script>

<style scoped>
.logout-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background-color: #f9fafb;
  padding: 20px;
}

.logout-content {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}

.buttons button,
.buttons a {
  min-width: 120px;
  text-align: center;
}
</style>
