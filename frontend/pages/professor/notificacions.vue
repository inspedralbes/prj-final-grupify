<template>
  <div class="notification-panel max-w-2xl mx-auto">
    <div class="bg-white p-6 rounded-lg shadow-md relative">
      <h1 class="text-2xl font-bold mb-4">Enviar Notificació</h1>
      
      <!-- Formulario -->
      <form @submit.prevent="prepararEnvio">
        <div class="mb-4">
          <label class="block text-gray-700 mb-2">Títol</label>
          <input
            v-model="form.title"
            type="text"
            placeholder="Títol de la notificació"
            class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            required
          />
        </div>
        
        <div class="mb-4">
          <label class="block text-gray-700 mb-2">Missatge</label>
          <textarea
            v-model="form.body"
            placeholder="Escriu el teu missatge..."
            class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            rows="4"
            required
          ></textarea>
        </div>
        
        <button
          type="submit"
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors"
        >
          Enviar notificació
        </button>
      </form>

      <!-- Mensaje d'èxit o error -->
      <div v-if="message" class="mt-4 text-green-600">
        {{ message }}
      </div>
      <div v-if="error" class="mt-4 text-red-600">
        {{ error }}
      </div>
    </div>

    <!-- Modal de confirmació per enviar -->
    <div 
      v-if="showConfirmModal" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
    >
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-bold mb-4">Confirmar notificació</h3>
        <div class="mb-2">
          <p class="text-sm text-gray-600"><strong>Títol:</strong> {{ form.title }}</p>
        </div>
        <div class="mb-4">
          <p class="text-sm text-gray-600"><strong>Missatge:</strong></p>
          <pre class="bg-gray-100 p-4 rounded whitespace-pre-wrap break-words text-sm">
{{ form.body }}
          </pre>
        </div>
        <div class="flex justify-end gap-2">
          <button
            @click="showConfirmModal = false"
            class="px-4 py-2 border rounded hover:bg-gray-50 transition-colors"
          >
            Cancel·lar
          </button>
          <button
            @click="confirmarEnvio"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
          >
            Confirmar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

// Form amb els camps "title" i "body"
const form = ref({
  title: '',
  body: ''
})

// Variables per mostrar missatges i errors
const message = ref('')
const error = ref('')

// Variable per controlar la visualització del modal de confirmació
const showConfirmModal = ref(false)

/**
 * Funció que prepara l'enviament. Comprova que els camps no estiguin buits i mostra el modal.
 */
function prepararEnvio() {
  if (!form.value.title.trim() || !form.value.body.trim()) return
  showConfirmModal.value = true
}

/**
 * Funció que, en confirmar, envia la notificació utilitzant la lògica actual amb Laravel.
 */
async function confirmarEnvio() {
  showConfirmModal.value = false
  // Reiniciem els missatges
  message.value = ''
  error.value = ''

  try {
    // Es realitza la crida a la API de Laravel
    const response = await $fetch('http://localhost:8000/api/notifications', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        Accept: 'application/json'
      },
      body: form.value
    })
    message.value = response.message || 'Notificació enviada correctament'
    // Netejar el formulari
    form.value.title = ''
    form.value.body = ''
  } catch (err) {
    error.value = err.data?.errors 
      ? Object.values(err.data.errors).flat().join(', ')
      : 'Error en enviar la notificació'
  }
}
</script>

<style scoped>
.notification-panel {
  min-height: calc(100vh - 128px);
  padding: 2rem 1rem;
}
pre {
  max-width: 100%;
  overflow-x: auto;
  font-family: inherit;
  line-height: 1.5;
  padding: 0.75rem;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
