<script setup>
import { useAuthStore } from "~/stores/authStore";
import useAuth from "~//composables/useAuth";

const authStore = useAuthStore();


const { HandleLogin } = useAuth()

// Estado reactivo
const email = ref("");
const password = ref("");
const isLoading = ref(false);
const msgError = ref("");
const successMessage = ref("");

// Router y rutas
const route = useRoute();
const { $socket } = useNuxtApp();

// Mensaje de registro exitoso
onMounted(() => {
  if (route.query.registered === "true") {
    successMessage.value = "Usuari registrat correctament. Inicia sessió.";
  }
});

// Validar formulario
const validateForm = () => {
  if (!email.value.trim()) {
    return false;
  }
  if (!password.value.trim()) {
    return false;
  }
  return true;
};

// Enviar formulario de inicio de sesión
const gestioSubmit = async (e) => {
  e.preventDefault();
  msgError.value = "";
  successMessage.value = "";

  if (!validateForm()) return;

  isLoading.value = true;

  const userData = {
    email: email.value,
    password: password.value,
  };

  try {
    const response = await HandleLogin(userData);

    const token = response.token;

    // Guardamos con el nombre correcto
    authStore.setAuth(token, response.user, response.role);
    
    // Conexión inmediata del socket después del login
    if (!$socket && !$socket.connected) {
      $socket.connect();
    } 

    // Registrar usuario en el socket
    $socket.emit("register_user", response.user.id);

    // Determinamos el rol correctamente (verificando ambas estructuras posibles)
    const userRole = response.role || response.user?.role?.name;

    // Redirección basada en roles usando la respuesta del servidor
    const dashboardRoutes = {
      admin: "/admin/dashboard",
      profesor: "/professor/dashboard",
      alumno: "/alumne/dashboard",
    };

    // Usamos el mismo método de navegación para todos los roles para evitar problemas
    navigateTo(dashboardRoutes[userRole] || "/");

  } catch (err) {
    console.error("Error de login:", err);
    msgError.value = "Credencials incorrectes. Si us plau, torna-ho a intentar.";
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="bg-white min-h-screen flex flex-col">
    <!-- Navbar -->
    <LandingPageNavBar />

    <!-- Contenido principal -->
    <div class="login-container">
      <div class="login-content">
        <header class="login-header">
          <h1>Benvingut!</h1>
          <p>Gestió d'informació académica</p>
        </header>

        <form class="login-form" @submit="gestioSubmit">
          <!-- Mensaje de éxito -->
          <div v-if="successMessage" class="success-message">
            {{ successMessage }}
          </div>

          <!-- Campos del formulario -->
          <LoginTextInput v-model="email" placeholder="Email" :has-msg-error="msgError && !email" />
          <LoginPasswordInput v-model="password" :has-msg-error="msgError && !password" />

          <!-- Enlace para recuperar contraseña -->
          <div class="forgot-password">
            <a href="#">Heu oblidat la contrasenya?</a>
          </div>

          <!-- Botón de envío -->
          <button type="submit" class="sign-in-button" :disabled="isLoading">
            {{ isLoading ? "Iniciant sessió..." : "Iniciar sessió" }}
          </button>

          <!-- Mensaje de error -->
          <div v-if="msgError" class="error-message">
            {{ msgError }}
          </div>

          <div class="divider"></div>

          <LoginSocialLogin />
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Ajuste global para eliminar márgenes */
body,
html {
  margin: 0;
  padding: 0;
}

.bg-white {
  margin: 0;
  padding: 0;
}

.login-container {
  min-height: calc(100vh - 80px);
  /* Resta la altura del navbar (ajusta si es necesario) */
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.login-content {
  width: 100%;
  max-width: 380px;
  background: var(--card-background);
  padding: 2rem 1.5rem;
  border-radius: 1.25rem;
  backdrop-filter: blur(10px);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

h1 {
  font-size: 1.75rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

p {
  color: var(--text-secondary);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.forgot-password {
  text-align: right;
  margin-top: -0.5rem;
}

.forgot-password a {
  color: var(--text-secondary);
  font-size: 0.875rem;
  text-decoration: none;
}

.sign-in-button {
  background: var(--color-primary);
  color: white;
  border: none;
  padding: 0.875rem;
  border-radius: 0.75rem;
  font-weight: 500;
  font-size: 1rem;
  cursor: pointer;
  transition: opacity 0.2s;
  margin-top: 0.5rem;
}

.sign-in-button:hover {
  opacity: 0.9;
}

.sign-in-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error-message {
  color: #ff4d4f;
  font-size: 0.875rem;
  text-align: center;
}

.divider {
  position: relative;
  margin: 1.5rem 0;
  height: 1px;
  background: rgba(0, 0, 0, 0.1);
}

@media (min-width: 768px) {
  .login-content {
    padding: 2.5rem;
  }
}
</style>
