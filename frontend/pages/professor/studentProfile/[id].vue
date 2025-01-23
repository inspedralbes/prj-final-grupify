<script setup>
import { useRoute } from 'vue-router'
import { onMounted, ref } from 'vue'
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue'

const route = useRoute()
const studentsStore = useStudentsStore()
const student = ref(null)
const isLoading = ref(true)
const error = ref(null)
const studentId = route.params.id

// Estado para manejar el modal de "Donar de Baixa" y los motivos seleccionados
const showBajaModal = ref(false)
const selectedReason = ref('')
const reasons = ['Falta de assistència', 'Baixa voluntària', 'Altres motius']

// Función para confirmar la baja
const handleBaja = () => {
  if (!selectedReason.value) {
    alert('Selecciona un motiu per donar de baixa.')
    return
  }

  // Cambiar el estado del estudiante a inactivo
  student.value.active = false
  studentsStore.updateStudent({
    ...student.value,
    active: false,
    reason: selectedReason.value, // Guardar motivo seleccionado (si necesario)
  })

  // Cerrar el modal y reiniciar el motivo
  showBajaModal.value = false
  selectedReason.value = ''
}

// Función para activar nuevamente al estudiante
const handleAlta = () => {
  // Cambiar el estado del estudiante a activo
  student.value.active = true
  studentsStore.updateStudent({
    ...student.value,
    active: true,
    reason: null, // El motivo no es necesario al reactivar
  })

  // Reiniciar cualquier selección previa de motivo y ocultar el modal
  showBajaModal.value = false
  selectedReason.value = ''
}

onMounted(async () => {
  try {
    if (!studentsStore.students.length) {
      await studentsStore.fetchStudents()
    }
    student.value = studentsStore.getStudentById(Number(studentId))
    if (!student.value) {
      error.value = 'Estudiant no trobat'
    }
  } catch (err) {
    console.error(err)
    error.value = 'Error al cargar els estudiants'
  } finally {
    isLoading.value = false
  }
})
</script>


<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />

    <main class="max-w-4xl mx-auto p-6">
      <!-- Loading State -->
      <div v-if="isLoading" class="flex flex-col items-center justify-center min-h-[400px]">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
        <p class="mt-4 text-gray-600 font-medium">Cargant perfil del estudiant...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg shadow-sm">
        <div class="flex items-center">
          <svg class="h-6 w-6 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-red-700 font-medium">{{ error }}</p>
        </div>
      </div>

      <!-- Student Profile -->
      <div v-else class="bg-white rounded-xl shadow-sm p-8">
        <div class="flex items-center space-x-6 mb-8 border-b pb-6">
          <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold text-3xl shadow-inner">
            {{ student.name.split(' ').map(n => n[0]).join('').toUpperCase() }}
          </div>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              {{ student.name }} {{ student.last_name }}
            </h1>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-sm font-medium text-gray-500 mb-1">Curs</h3>
              <p class="text-lg font-semibold text-gray-900">{{ student.course }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-sm font-medium text-gray-500 mb-1">Divisió</h3>
              <p class="text-lg font-semibold text-gray-900">{{ student.division }}</p>
            </div>
          </div>
          <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-sm font-medium text-gray-500 mb-1">Email</h3>
              <p class="text-lg font-semibold text-gray-900">{{ student.email }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <h3 class="text-sm font-medium text-gray-500 mb-1">Estat</h3>
              <div class="flex items-center space-x-4">
                <span
                  :class="student.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                  {{ student.active ? 'Actiu' : 'Inactiu' }}
                </span>
                <!-- Botones para cambiar estado -->
                <button
                  v-if="student.active"
                  @click="showBajaModal = true"
                  class="px-4 py-2 bg-red-600 text-white font-medium text-sm rounded-lg shadow-sm hover:bg-red-700">
                  Donar de Baixa
                </button>
                <button
                  v-else
                  @click="handleAlta"
                  class="px-4 py-2 bg-green-600 text-white font-medium text-sm rounded-lg shadow-sm hover:bg-green-700">
                  Donar d'Alta
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal para seleccionar motivo de baja -->
        <div v-if="showBajaModal" class="mt-8 bg-gray-50 p-6 rounded-lg border shadow-md">
          <h2 class="text-lg font-bold text-gray-800 mb-4">Selecciona un motiu per donar de baixaaaa</h2>
          <div class="space-y-2">
            <label v-for="reason in reasons" :key="reason" class="flex items-center space-x-2">
              <input
                type="radio"
                :value="reason"
                v-model="selectedReason"
                class="text-primary focus:ring-primary" />
              <span class="text-gray-700">{{ reason }}</span>
            </label>
          </div>
          <div class="mt-6 flex space-x-4">
            <button
              @click="handleBaja"
              class="px-4 py-2 bg-primary text-white font-medium text-sm rounded-lg shadow-sm hover:bg-primary-dark">
              Confirmar Baixa
            </button>
            <button
              @click="showBajaModal = false"
              class="px-4 py-2 bg-gray-300 text-gray-700 font-medium text-sm rounded-lg shadow-sm hover:bg-gray-400">
              Cancel·lar
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>
