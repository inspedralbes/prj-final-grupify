<template>
  <div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h2 class="text-3xl font-bold text-gray-900">Crear Notificació</h2>
          <p class="mt-1 text-sm text-gray-500">Gestiona les notificacions que desitges enviar.</p>
        </div>
      </div>

      <!-- Toast Notification -->
      <div v-if="showToast" :class="['p-4 mb-4 rounded-md', toastType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
        <div class="flex items-center">
          <span class="font-medium">{{ toastMessage }}</span>
        </div>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700">Títol</label>
          <input
            type="text"
            id="title"
            v-model="form.title"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          />
        </div>

        <div>
          <label for="content" class="block text-sm font-medium text-gray-700">Missatge</label>
          <textarea
            id="content"
            v-model="form.body"
            rows="4"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          ></textarea>
        </div>

        <div>
          <div class="flex items-center">
            <input
              id="scheduled"
              type="checkbox"
              v-model="form.isScheduled"
              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
            <label for="scheduled" class="ml-2 block text-sm text-gray-700">Programar</label>
          </div>

          <div v-if="form.isScheduled" class="mt-4 grid grid-cols-2 gap-4">
            <div>
              <label for="date" class="block text-sm font-medium text-gray-700">Data</label>
              <input
                type="date"
                id="date"
                v-model="form.scheduledDate"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>
            <div>
              <label for="time" class="block text-sm font-medium text-gray-700">Hora</label>
              <input
                type="time"
                id="time"
                v-model="form.scheduledTime"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <button
            type="button"
            @click="resetForm"
            class="rounded-md bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm border border-gray-300 hover:bg-gray-50"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="isLoading"
            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
          >
            <span v-if="isLoading">Enviant...</span>
            <span v-else>Enviar Notificació</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/authStore'

const authStore = useAuthStore()
const isLoading = ref(false)
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('')

const form = reactive({
  title: '',
  body: '',
  isScheduled: false,
  scheduledDate: '',
  scheduledTime: ''
})

const resetForm = () => {
  form.title = ''
  form.body = ''
  form.isScheduled = false
  form.scheduledDate = ''
  form.scheduledTime = ''
}

const handleSubmit = async () => {
  try {
    isLoading.value = true

    const scheduledAt = form.isScheduled
      ? `${form.scheduledDate}T${form.scheduledTime}:00`
      : null

    const payload = {
      title: form.title,
      body: form.body,
      scheduled_at: scheduledAt
    }

    await $fetch('https://api.grupify.cat/api/notifications', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        Accept: 'application/json'
      },
      body: payload
    })

    // Show success toast notification
    toastMessage.value = 'Notificació creada correctament'
    toastType.value = 'success'
    showToast.value = true

    // Reset the form after successful submission
    resetForm()

    // Hide toast after 3 seconds
    setTimeout(() => {
      showToast.value = false
    }, 3000)
  } catch (error) {
    // Show error toast notification
    toastMessage.value = 'Error al crear la notificació'
    toastType.value = 'error'
    showToast.value = true

    setTimeout(() => {
      showToast.value = false
    }, 3000)
  } finally {
    isLoading.value = false
  }
}
</script>
