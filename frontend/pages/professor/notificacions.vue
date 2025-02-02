<template>
  <div class="notification-panel max-w-2xl mx-auto">
    <div class="bg-white p-6 rounded-lg shadow-md relative">
      <h1 class="text-2xl font-bold mb-4">Enviar Notificació</h1>
      
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

        <div class="mb-4">
          <label class="block text-gray-700 mb-2">
            <input
              type="checkbox"
              v-model="isScheduled"
              class="mr-2"
            />
            Programar enviament
          </label>
          
          <input
            v-if="isScheduled"
            v-model="form.scheduled_at"
            type="datetime-local"
            class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :min="minDateTime"
          />
        </div>
        
        <button
          type="submit"
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors"
        >
          {{ isScheduled ? 'Programar notificació' : 'Enviar notificació' }}
        </button>
      </form>

      <div v-if="message" class="mt-4 text-green-600">
        {{ message }}
      </div>
      <div v-if="error" class="mt-4 text-red-600">
        {{ error }}
      </div>
    </div>

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
          <pre class="bg-gray-100 p-4 rounded whitespace-pre-wrap break-words text-sm">{{ form.body }}</pre>
        </div>
        <div v-if="isScheduled" class="mb-4">
          <p class="text-sm text-gray-600">
            <strong>Data programada:</strong> {{ formatDateTime(form.scheduled_at) }}
          </p>
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
import { ref, computed } from 'vue'
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()
const isScheduled = ref(false)

const form = ref({
  title: '',
  body: '',
  scheduled_at: ''
})

const message = ref('')
const error = ref('')
const showConfirmModal = ref(false)

const minDateTime = computed(() => {
  const now = new Date()
  now.setMinutes(now.getMinutes() + 5) // Mínimo 5 minutos en el futuro
  return now.toISOString().slice(0, 16)
})

function formatDateTime(dateString) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('ca-ES', {
    dateStyle: 'medium',
    timeStyle: 'short'
  })
}

function prepararEnvio() {
  if (!form.value.title.trim() || !form.value.body.trim()) return
  if (isScheduled.value && !form.value.scheduled_at) {
    error.value = 'Si vols programar la notificació, has de seleccionar una data'
    return
  }
  showConfirmModal.value = true
}

async function confirmarEnvio() {
  showConfirmModal.value = false
  message.value = ''
  error.value = ''

  const formData = {
    ...form.value
  }

  if (!isScheduled.value) {
    delete formData.scheduled_at
  }

  try {
    const response = await $fetch('http://localhost:8000/api/notifications', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        Accept: 'application/json'
      },
      body: formData
    })
    
    message.value = response.message
    form.value = {
      title: '',
      body: '',
      scheduled_at: ''
    }
    isScheduled.value = false
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