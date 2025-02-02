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
            <input type="checkbox" v-model="isScheduled" class="mr-2" />
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

      <div v-if="message" class="mt-4 text-green-600">{{ message }}</div>
      <div v-if="error" class="mt-4 text-red-600">{{ error }}</div>
    </div>

    <!-- Historial de notificaciones -->
    <div class="bg-white p-6 rounded-lg shadow-md mt-6">
      <h2 class="text-xl font-bold mb-4">Historial de Notificacions</h2>

      <div v-if="loading" class="text-gray-500">Cargando historial...</div>

      <div v-else>
        <div v-if="notifications.length === 0" class="text-gray-500">
          No has enviat cap notificació.
        </div>
        <ul>
          <li
            v-for="notification in notifications"
            :key="notification.id"
            class="mb-4 p-4 border rounded hover:bg-gray-50 transition-colors"
          >
            <h3 class="font-bold">{{ notification.title }}</h3>
            <p>{{ notification.body }}</p>
            <small class="text-gray-500">
              Enviada: {{ formatDate(notification.created_at) }}
            </small>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
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

const notifications = ref([])
const loading = ref(true)

const minDateTime = computed(() => {
  const now = new Date()
  now.setMinutes(now.getMinutes() + 5)
  return now.toISOString().slice(0, 16)
})

function formatDate(dateString) {
  return new Date(dateString).toLocaleString('ca-ES', {
    dateStyle: 'medium',
    timeStyle: 'short'
  })
}

async function fetchTeacherNotifications() {
  try {
    const data = await $fetch('http://localhost:8000/api/teacher-notifications', {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        Accept: 'application/json'
      }
    })
    notifications.value = data.notifications
  } catch (error) {
    console.error('Error al obtenir el historial de notificacions:', error)
  } finally {
    loading.value = false
  }
}

async function confirmarEnvio() {
  showConfirmModal.value = false
  message.value = ''
  error.value = ''

  const formData = { ...form.value }

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
    form.value = { title: '', body: '', scheduled_at: '' }
    isScheduled.value = false

    // Recargar historial después de enviar
    fetchTeacherNotifications()
  } catch (err) {
    error.value = err.data?.errors
      ? Object.values(err.data.errors).flat().join(', ')
      : 'Error en enviar la notificació'
  }
}

onMounted(() => {
  fetchTeacherNotifications()
})
</script>
