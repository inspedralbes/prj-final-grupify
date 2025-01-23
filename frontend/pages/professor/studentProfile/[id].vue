<script setup>
import { useRoute } from 'vue-router'
import { onMounted, ref, computed } from 'vue'
import Dashboard from '../dashboard.vue'
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue'

const route = useRoute()
const studentsStore = useStudentsStore()
const student = ref(null)
const isLoading = ref(true)
const error = ref(null)
const studentId = route.params.id

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
      <div v-if="isLoading" 
           class="flex flex-col items-center justify-center min-h-[400px]">
        <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
        <p class="mt-4 text-gray-600 font-medium">Cargant perfil del estudiant...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" 
           class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg shadow-sm">
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
              <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                Actiu
              </span>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>