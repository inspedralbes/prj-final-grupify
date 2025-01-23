<script setup>
import { useRouter } from 'vue-router'; 

const name = ref("");
const last_name = ref("");
const email = ref("");
const password = ref("");
const confirmPassword = ref("");
const isLoading = ref(false);
const msgError = ref("");
const router = useRouter();

// Valida el formulari
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

// Enviar el formulario de registro
const gestioSubmit = async (e) => {
  e.preventDefault();
  msgError.value = "";

  if (!validateForm()) return;

  isLoading.value = true;

  try {
    // Solicitar registro al servidor
    const response = await fetch("http://localhost:8000/api/register", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        name: name.value,
        last_name: last_name.value,
        email: email.value,
        password: password.value,
        password_confirmation: confirmPassword.value,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      // Manejar errores de validación
      if (data.errors) {
        const errorMessages = Object.values(data.errors).flat();
        msgError.value = errorMessages.join(", ");
      } else {
        msgError.value = data.message || "Error desconegut";
      }
      throw new Error(msgError.value);
    }

    console.log("Usuari registrat!", data);

    // fer login després de registrar-se
    const loginResponse = await fetch("http://localhost:8000/api/login", {
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

    const loginData = await loginResponse.json();

    if (!loginResponse.ok) {
      msgError.value = loginData.message || "Error en el login automàtic";
      throw new Error(msgError.value);
    }

    // Desar token i dades del usuari en localStorage
    localStorage.setItem("auth_token", loginData.token);
    localStorage.setItem("role", loginData.role);
    localStorage.setItem("user", JSON.stringify(loginData.user));

    // Redirigir al dashboard alumne
    router.push("/alumne/dashboard");
  } catch (err) {
    msgError.value =
      err.message || "No s'ha pogut registrar l'usuari. Si us plau, torna-ho a provar.";
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
        <LoginTextInput
          v-model="name"
          placeholder="Nom "
          :has-msg-error="msgError && !name"
        />
        <LoginTextInput
          v-model="last_name"
          placeholder="Cognom "
          :has-msg-error="msgError && !last_name"
        />
        <LoginTextInput
          v-model="email"
          placeholder="Correu electrònic"
          :has-msg-error="msgError && !email"
        />
        <LoginPasswordInput
          v-model="password"
          :has-msg-error="msgError && !password"
        />
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
