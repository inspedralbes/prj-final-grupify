<script setup>
const props = defineProps({
  modelValue: String,
  hasError: Boolean,
});

const emit = defineEmits(["update:modelValue"]);

const showPassword = ref(false);

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};
</script>

<template>
  <div class="password-input">
    <!-- Campo de contraseña -->
    <input
      :type="showPassword ? 'text' : 'password'"
      :value="modelValue"
      placeholder="Contrasenya"
      :class="{ error: hasError }"
      @input="emit('update:modelValue', $event.target.value)"
    />

    <!-- Botón para alternar visibilidad -->
    <button
      type="button"
      class="toggle-password"
      :aria-label="
        showPassword ? 'Amaga la contrasenya' : 'Mostra la contrasenya'
      "
      @click="togglePasswordVisibility"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="20"
        height="20"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <!-- Ícono de ojo visible -->
        <path
          v-if="!showPassword"
          d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
        />
        <circle v-if="!showPassword" cx="12" cy="12" r="3" />

        <!-- Ícono de ojo tachado -->
        <path
          v-if="showPassword"
          d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"
        />
        <line v-if="showPassword" x1="1" y1="1" x2="23" y2="23" />
      </svg>
    </button>
  </div>
</template>

<style scoped>
.password-input {
  position: relative;
}

input {
  width: 100%;
  padding: 0.875rem 1rem;
  padding-right: 2.5rem;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 0.75rem;
  font-size: 1rem;
  background: var(--input-background);
  transition: border-color 0.2s;
}

input:focus {
  outline: none;
  border-color: var(--color-primary);
}

input.error {
  border-color: #ff4d4f;
}

input::placeholder {
  color: #999;
}

.toggle-password {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  padding: 0.25rem;
  cursor: pointer;
  color: #666;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-password:hover {
  color: var(--text-primary);
}
</style>
