<script setup>
import { onMounted } from "vue";
import { useAuthStore } from "~/stores/authStore";
import { useRouter } from "vue-router";

const config = useRuntimeConfig();
const clientId = config.public.googleClientId;
const authStore = useAuthStore();
const router = useRouter();
const errorMessage = ref('');

const loadGoogleScript = async () => {
  if (window.google?.accounts?.id) return;
  return new Promise((resolve, reject) => {
    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });
};

const handleGoogleResponse = (response) => {
  // Ver el token JWT de Google
  const userData = parseJwt(response.credential);
  sendToBackend(userData);
};

const parseJwt = (token) => {
  try {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(
      atob(base64)
        .split('')
        .map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
        .join('')
    );
    return JSON.parse(jsonPayload);
  } catch (e) {
    console.error('Error parsing JWT:', e);
    return null;
  }
};

const sendToBackend = async (userData) => {
  errorMessage.value = '';

  // Construir el objeto de datos a enviar
  const postData = {
    email: userData.email,
    google_id: userData.sub,
    name: userData.given_name,
    last_name: userData.family_name || '',
    image: userData.picture,
  };

  // Si existe un token de invitación en la URL, lo incluimos en la petición
  const currentQuery = router.currentRoute.value.query;
  if (currentQuery.invitation) {
    postData.invitation_token = currentQuery.invitation;
  }

  console.log('Datos enviados al backend:', postData); // Para depuración

  try {
    const { token, user, role } = await $fetch('http://localhost:8000/api/google-login', {
      method: 'POST',
      body: postData,
    });

    console.log('Respuesta del servidor:', { token, user, role }); // Depuración

    // Asegurarse de que se guardan correctamente todos los datos del usuario
    authStore.setAuth(token, user, role || user?.role?.name);

    // Determinar la ruta del dashboard basada en el rol
    const userRole = role || user?.role?.name;
    const dashboardRoutes = {
      admin: "/admin/dashboard",
      profesor: "/professor/dashboard",
      alumne: "/alumne/dashboard",
      tutor: "/professor/dashboard", // Los tutores usan el mismo dashboard que los profesores
      orientador: "/orientador/dashboard"
    };

    // Redirigir al dashboard correspondiente
    const route = dashboardRoutes[userRole] || '/login';
    window.location.href = route;
  } catch (error) {
    console.error('Error en el login:', error);
    if (error.response && error.response.status === 400) {
      errorMessage.value = error.response.data?.errors?.email[0] || 'Error en el formato del correo';
    } else {
      errorMessage.value = 'Error en el servidor. Por favor, intenta de nuevo más tarde.';
    }
    // Limpiar mensaje después de 5 segundos
    setTimeout(() => {
      errorMessage.value = '';
    }, 5000);
  }
};

const gestioGoogleLogin = () => {
  if (!window.google?.accounts?.id) {
    console.error('Google script no cargado');
    return;
  }
  window.google.accounts.id.prompt();
};

onMounted(async () => {
  try {
    await loadGoogleScript();
    window.google.accounts.id.initialize({
      client_id: clientId,
      callback: handleGoogleResponse,
    });
  } catch (error) {
    console.error('Error cargando Google Script:', error);
  }
});
</script>

<template>
  <div class="social-login">
    <!-- Mensaje de error -->
    <div v-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </div>

    <button class="social-button" aria-label="Entra amb Google" @click="gestioGoogleLogin">
      <img src="/icons/google.svg" alt="Google icon" />
      <span>Google / @inspedralbes.cat</span>
    </button>
  </div>
</template>

<style scoped>
.social-login {
  display: flex;
  justify-content: center;
  width: 100%;
}

.social-button {
  width: 100%;
  height: 2.75rem;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 0.75rem;
  background: var(--input-background);
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: var(--text-primary);
  font-weight: 500;
}

.social-button:hover {
  border-color: var(--color-primary);
  transform: translateY(-2px);
}

.social-button img {
  width: 1.25rem;
  height: 1.25rem;
  opacity: 0.8;
}
</style>
