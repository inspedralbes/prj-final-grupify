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
export default {
  name: "TancarSessio",
  methods: {
    async handleLogout() {
      try {
        const authToken = localStorage.getItem("auth_token");

        if (!authToken) {
          console.warn(
            "No hay token de autenticación disponible. Se redirigirá al inicio de sesión."
          );
          this.$router.push("/login");
          return;
        }

        // Solicitud para cerrar sesión
        const response = await fetch("http://localhost:8000/api/logout", {
          method: "POST",
          headers: {
            Authorization: `Bearer ${authToken}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        });

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(
            errorData.message || "Error al cerrar sesión. Intenta nuevamente."
          );
        }

        // Limpieza del almacenamiento local
        localStorage.removeItem("auth_token");
        localStorage.removeItem("user");

        // Redirección al login
        if (this.$router) {
          this.$router.push("/login");
        } else {
          window.location.href = "/login";
        }

        // console.log('Sesión cerrada correctamente');
      } catch (error) {
        console.error(
          "Error cerrando sesión:",
          error.message || "Error desconocido."
        );
        alert(
          error.message ||
            "Ocurrió un error desconocido al cerrar sesión. Intenta nuevamente."
        );
      }
    },
  },
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
