<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "~/stores/authStore";

const authStore = useAuthStore();
const router = useRouter();

const name = ref("");
const last_name = ref("");
const email = ref("");
const password = ref("");
const confirmPassword = ref("");
const isLoading = ref(false);
const msgError = ref("");
const invitationToken = ref(null);

// Extract invitation token from URL on component mount
onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  invitationToken.value = urlParams.get('invitation');
});

// Validate form before submission
const validateForm = () => {
  if (!name.value) {
    msgError.value = "El nom és obligatori";
    return false;
  }
  if (!last_name.value) {
    msgError.value = "El cognom és obligatori";
    return false;
  }
  if (!email.value) {
    msgError.value = "El correu electrònic és obligatori";
    return false;
  }
  if (!password.value) {
    msgError.value = "La contrasenya és obligatoria";
    return false;
  }
  if (password.value !== confirmPassword.value) {
    msgError.value = "Les contrasenyes no coincideixen";
    return false;
  }
  return true;
};

// Submit registration form
const gestioSubmit = async (e) => {
  e.preventDefault();
  msgError.value = "";

  if (!validateForm()) return;

  isLoading.value = true;

  try {
    // Prepare registration payload
    const registrationPayload = {
      name: name.value,
      last_name: last_name.value,
      email: email.value,
      password: password.value,
      password_confirmation: confirmPassword.value,
    };

    // Add invitation token if present
    if (invitationToken.value) {
      registrationPayload.invitation_token = invitationToken.value;
    }

    // Registration request
    const response = await fetch("https://api.basebrutt.com/api/register", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(registrationPayload),
    });

    const data = await response.json();

    if (!response.ok) {
      // Handle validation errors
      if (data.errors) {
        const errorMessages = Object.values(data.errors).flat();
        msgError.value = errorMessages.join(", ");
      } else {
        msgError.value = data.message || "Error desconegut";
      }
      throw new Error(msgError.value);
    }

    console.log("Usuari registrat!", data);

    // Iniciar sesión automáticamente después del registro
    const loginResponse = await fetch("https://api.basebrutt.com/api/login", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
      }),
    });

    if (!loginResponse.ok) {
      const errorData = await loginResponse.json();
      msgError.value = errorData.message || "Error en el login automàtic";
      throw new Error(msgError.value);
    }

    // Get authenticated user data
    const loginData = await loginResponse.json();

    if (!loginData.token || !loginData.user) {
      throw new Error("La API no ha retornat un token o usuari vàlid.");
    }

    // Save data in Pinia store
    authStore.setAuth(loginData.token, loginData.user);

    // Redirect to student dashboard
    router.push("/alumne/dashboard");

  } catch (err) {
    msgError.value = err.message || "No s'ha pogut registrar l'usuari.";
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div class="login-container">
    <div class="login-content">
      <div class="login-header">
        <h1>Registra't!</h1>
        <p>Gestió d'informació acadèmica</p>
      </div>

      <form class="login-form" @submit="gestioSubmit">
        <LoginTextInput v-model="name" placeholder="Nom " :has-msg-error="msgError && !name" />
        <LoginTextInput v-model="last_name" placeholder="Cognom " :has-msg-error="msgError && !last_name" />
        <LoginTextInput v-model="email" placeholder="Correu electrònic" :has-msg-error="msgError && !email" />
        <LoginPasswordInput v-model="password" :has-msg-error="msgError && !password" />
        <LoginPasswordInput 
          v-model="confirmPassword" 
          :has-msg-error="msgError && !confirmPassword"
          placeholder="Confirma la contrasenya" 
        />

        <button type="submit" class="sign-in-button" :disabled="isLoading">
          {{ isLoading ? "Registrant..." : "Registrar-se" }}
        </button>

        <div class="login-link">
          <p>
            Ja tens un compte?
            <router-link to="/login"><b>Inicia sessió</b></router-link>
          </p>
        </div>

        <div v-if="msgError" class="msgError-message">
          {{ msgError }}
        </div>

        <div class="divider"></div>

        <LoginSocialLogin />
      </form>
    </div>
  </div>
</template>

<style scoped>
.login-container {
  min-height: 100vh;
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
