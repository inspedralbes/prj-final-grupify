<script setup>
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";
import { EyeIcon } from '@heroicons/vue/24/outline';
import { useAuthStore } from "~/stores/authStore";

const route = useRoute();
const navigate = useRouter();
const formId = route.params.id;
const isLoading = ref(true);
const students = ref([]);
const isMobile = ref(false);
const formNotAssigned = ref(false);
const authStore = useAuthStore();

// Detectar si es mobile
onMounted(() => {
  getUsersByForm(formId);
  checkIfMobile();
  window.addEventListener('resize', checkIfMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkIfMobile);
});

const checkIfMobile = () => {
  isMobile.value = window.innerWidth < 768;
};

const getUsersByForm = async (formId) => {
  try {
    // Para todos los formularios, incluido el sociograma (ID 3), usamos la misma API
    // que nos devolverá solo estudiantes de cursos asignados al profesor
    const apiUrl = `http://localhost:8000/api/form-user/${formId}/assigned-users`;
    const response = await fetch(apiUrl, {
      method: "GET",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${authStore.token}`,
      },
    });

    // Si no está autorizado o no se encontró el recurso, marcamos como no asignado
    if (response.status === 401 || response.status === 403) {
      console.log("Formulario no asignado a ninguno de tus cursos");
      formNotAssigned.value = true;
      students.value = [];
      return;
    }

    if (response.status === 404) {
      console.log("Recurso no encontrado");
      formNotAssigned.value = true;
      students.value = [];
      return;
    }

    if (!response.ok) {
      throw new Error(`Error al obtener usuarios asignados: ${response.status}`);
    }

    const data = await response.json();
    console.log("Datos recibidos:", data);
    
    if (data.users && data.users.length > 0) {
      students.value = data.users;
    } else {
      // Si no hay usuarios pero el formulario está asignado, mostramos una lista vacía
      students.value = [];
    }
  } catch (error) {
    console.error("Error:", error);
    // Si hay un error de API, asumimos que hay un problema con la asignación
    formNotAssigned.value = true;
  } finally {
    isLoading.value = false;
  }
};

const handleReturn = () => {
  navigateTo('/professor/formularis');
};

// Función para manejar errores de carga de imágenes
const onImageError = (event, student) => {
  // Ocultar la imagen con error y mostrar iniciales en su lugar
  event.target.style.display = 'none';
  const parent = event.target.parentElement;
  if (parent) {
    parent.classList.add('bg-primary/10', 'flex', 'items-center', 'justify-center', 'text-primary', 'font-bold');
    parent.textContent = student.name.split(" ").map(n => n[0]).join("").toUpperCase();
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <DashboardNavTeacher class="shadow-md z-10" />

    <div class="flex-1 container mx-auto px-4 py-8">
      <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <!-- Header mejorado para mobile -->
        <div class="flex items-center justify-between mb-6 md:mb-8">
          <NuxtLink
            to="/professor/formularis"
            class="flex items-center text-gray-700 hover:text-gray-900 text-sm md:text-base"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4 md:h-5 md:w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 19l-7-7 7-7"
              />
            </svg>
            <span class="ml-1 md:ml-2">Tornar</span>
          </NuxtLink>

          <h1 class="text-xl md:text-3xl font-bold text-gray-800 text-center truncate">
            Llistat d'Estudiants
          </h1>

          <div class="w-8"></div>
        </div>

        <!-- Loading state -->
        <div v-if="isLoading" class="flex justify-center items-center h-48 md:h-64">
          <div class="animate-spin rounded-full h-10 w-10 md:h-12 md:w-12 border-b-2 border-blue-500"></div>
        </div>

        <!-- Not assigned state -->
        <div
          v-else-if="formNotAssigned"
          class="text-center py-10"
        >
          <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
            <div class="flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <div>
                <p class="text-amber-800 font-medium">Aquest formulari no està assignat als teus cursos.</p>
                <p class="text-amber-700 mt-1">No tens accés a veure les respostes d'aquest formulari perquè no ha estat assignat a cap dels cursos que imparteixes.</p>
              </div>
            </div>
          </div>
          <NuxtLink to="/professor/formularis" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
            Tornar a la llista de formularis
          </NuxtLink>
        </div>

        <!-- Empty state -->
        <div
          v-else-if="students.length === 0"
          class="text-center text-gray-500 py-6 md:py-8"
        >
          No hi ha estudiants en aquest formulari
        </div>

        <!-- Students list -->
        <div v-else class="space-y-4">
          <div class="card">
            <!-- Status summary -->
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
              <div class="flex justify-between items-center mb-2">
                <h3 class="font-medium text-gray-800">Estat de Respostes</h3>
                <span class="text-sm font-bold">{{ Math.round((students.filter(s => s.answered).length / students.length) * 100) || 0 }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div
                  class="bg-blue-600 h-2.5 rounded-full"
                  :style="`width: ${Math.round((students.filter(s => s.answered).length / students.length) * 100) || 0}%`"
                ></div>
              </div>
              <div class="mt-2 text-sm text-gray-600">
                {{ students.filter(s => s.answered).length }} de {{ students.length }} estudiants han contestat
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b">
                    <th class="text-left py-3 px-4">Nom</th>
                    <th class="text-left py-3 px-4">Email</th>
                    <th class="text-left py-3 px-4">Curs</th>
                    <th class="text-left py-3 px-4">Estat</th>
                    <th class="text-left py-3 px-4">Accions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="student in students" :key="student.id" class="border-b hover:bg-gray-50">
                    <td class="py-4 px-4">
                      <div class="flex items-center space-x-3">
                        <!-- Avatar con imagen si existe, o iniciales como fallback -->
                        <div v-if="student.image" class="w-10 h-10 rounded-full overflow-hidden">
                          <img :src="student.image" alt="Avatar" class="w-full h-full object-cover" 
                               @error="onImageError($event, student)" />
                        </div>
                        <div
                          v-else
                          class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold"
                        >
                          {{ student.name.split(" ").map(n => n[0]).join("").toUpperCase() }}
                        </div>
                        <span>{{ student.name }}</span>
                        <span>{{ student.last_name }}</span>
                      </div>
                    </td>
                    <td class="py-4 px-4">{{ student.email }}</td>
                    <td class="py-4 px-4">{{ student.course }} {{ student.division }}</td>
                    <td class="py-4 px-4">
                      <div class="flex items-center">
                        <span
                          class="px-2 py-1 rounded-full text-xs font-medium"
                          :class="student.answered ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                        >
                          {{ student.answered ? 'Contestado' : 'Pendiente' }}
                        </span>
                      </div>
                    </td>
                    <td class="py-4 px-4">
                      <div class="flex space-x-2">
                        <button
                          class="p-1 hover:text-primary flex items-center space-x-1"
                          @click="navigateTo(`/professor/formularis/${formId}/users/${student.id}/answers`)"
                          :disabled="!student.answered"
                          :class="{'opacity-50 cursor-not-allowed': !student.answered, 'text-blue-600 hover:text-blue-800': student.answered}"
                        >
                          <span>Veure Respostes</span>
                          <EyeIcon class="w-5 h-5" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-if="students.length === 0" class="text-center py-8 text-gray-500">
              No s'han trobat estudiants amb els filtres seleccionats
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>