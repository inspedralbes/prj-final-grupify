import { defineStore } from "pinia";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    token: localStorage.getItem("token") || null,
    user: JSON.parse(localStorage.getItem("user")) || null,
    isAuthenticated: !!localStorage.getItem("token"), // Verifica si hay token
  }),

  actions: {
    // Inicializa el store (se llama al cargar la app)
    initialize() {
      if (this.token) {
        this.checkAuth();
      }
    },

    // Verifica si el token es válido
    async checkAuth() {
      try {
        const response = await $fetch('http://localhost:8000/api/user', {
          headers: {
            Authorization: `Bearer ${this.token}`,
            Accept: 'application/json',
          },
        });

        if (response.user) {
          this.user = response.user;
          this.isAuthenticated = true;
        } else {
          this.logout();
        }
      } catch (error) {
        this.logout();
      }
    },

    // Guarda los datos de autenticación
    setAuth(token, user) {
      this.token = token;
      this.user = user;
      this.isAuthenticated = true;

      // Almacena en localStorage
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));
    },

    // Cierra la sesión
    async logout() {
      try {
        const token = this.token; // Guarda el token antes de eliminarlo
        if (token) {
          await $fetch('http://localhost:8000/api/logout', {
            method: 'POST',
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: 'application/json',
            },
          });
        }
      } catch (error) {
        console.error('Error durante el logout:', error);
      } finally {
        this.token = null;
        this.user = null;
        this.isAuthenticated = false;
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        navigateTo('/login');
      }
    }
  },
});