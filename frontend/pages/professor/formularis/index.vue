<script setup>
import {
  PlusIcon,
  UserGroupIcon,
  EyeIcon,
  EyeSlashIcon,
} from "@heroicons/vue/24/outline";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const authStore = useAuthStore();
const user = authStore.user;
const teacherId = ref(user.id);
const router = useRouter();
const searchQuery = ref("");
const selectedDivision = ref("all");
const selectedDate = ref("all");
const showAssignModal = ref(false);
const selectedForm = ref(null);
const forms = ref([]);
const toastMessage = ref("");
const toastType = ref("success");
const showToast = ref(false);

onMounted(async () => {
  try {
    // Obtener el token del authStore
    const token = authStore.token;
    
    if (!token) {
      throw new Error("No se encontró token de autenticación. Por favor, inicie sesión de nuevo.");
    }
    
    // Mantener la forma original de obtener los formularios desde la tabla forms
    const response = await fetch(`http://localhost:8000/api/forms?teacher_id=${teacherId.value}`, {
      method: "GET",
      headers: { 
        Accept: "application/json",
        Authorization: `Bearer ${token}`
      }
    });
    
    if (!response.ok) throw new Error("Error obteniendo los datos.");
    
    // Obtener los formularios
    forms.value = await response.json();
    
    // Adicionalmente, obtenemos las asignaciones para tener los datos de course_id y division_id
    const assignmentsResponse = await fetch(`http://localhost:8000/api/form-assignments/teacher/${teacherId.value}`, {
      method: "GET",
      headers: { 
        Accept: "application/json",
        Authorization: `Bearer ${token}` 
      }
    });
    
    if (assignmentsResponse.ok) {
      const formAssignments = await assignmentsResponse.json();
      
      // Enriquecer los formularios con la información de asignaciones
      forms.value = forms.value.map(form => {
        // Buscar la asignación correspondiente a este formulario
        const assignment = formAssignments.find(a => a.form_id === form.id);
        
        if (assignment) {
          // Si encontramos la asignación, añadimos course_id y division_id al formulario
          // Y obtenemos el estado desde assignments si está disponible
          return {
            ...form,
            course_id: assignment.course_id,
            division_id: assignment.division_id,
            // Usar el status de la asignación si está disponible (asegurando que sea 0 o 1)
            status: assignment.status !== undefined ? 
                   (typeof assignment.status === 'boolean' ? 
                     (assignment.status ? 1 : 0) : 
                     parseInt(assignment.status)) : 
                   form.status
          };
        }
        
        return form;
      });
    }
    
    filterForms(forms.value);
  } catch (error) {
    console.error("Error al cargar los formularios:", error.message);
    toastMessage.value = "Error al cargar los formularios: " + error.message;
    toastType.value = "error";
    showToast.value = true;
    setTimeout(() => {
      showToast.value = false;
    }, 3000);
  }
});

const filterForms = (forms) => {
  // Recorremos el array en reversa para poder eliminar elementos sin afectar índices
  for (let i = forms.length - 1; i >= 0; i--) {
    const form = forms[i];
    if (form.is_global === 2 && !authStore.isTutor) {
      // Si is_global es 2 y NO es tutor, eliminar del array
      forms.splice(i, 1);
    }
  }
};

const updateFormStatus = async (formId, newStatus, courseId, divisionId) => {
  try {
    // Verificar si tenemos course_id y division_id
    if (!courseId || !divisionId) {
      throw new Error("No se encontró información de curso y división para este formulario.");
    }
    
    // Obtener el token del authStore
    const token = authStore.token;
    
    if (!token) {
      throw new Error("No se encontró token de autenticación. Por favor, inicie sesión de nuevo.");
    }
    
    const url = `http://localhost:8000/api/forms/${formId}/assignment-status`;
    const body = { 
      status: newStatus,
      course_id: courseId,
      division_id: divisionId
    };
    
    // Actualizar la tabla form_assignments con valor 0 o 1
    const response = await fetch(url, {
      method: "PATCH",
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(body),
    });
    
    if (!response.ok) {
      const errorText = await response.text();
      
      try {
        const errorData = JSON.parse(errorText);
        throw new Error(`Error al actualizar el estado: ${errorData.message || response.statusText}`);
      } catch (e) {
        throw new Error(`Error al actualizar el estado: ${response.statusText}`);
      }
    }
    
    const responseData = await response.json();
      
    // Actualizar la interfaz de usuario para reflejar el cambio
    forms.value = forms.value.map(form =>
      form.id === formId ? { ...form, status: newStatus } : form
    );
    
    // Mostrar mensaje de éxito
    toastMessage.value = "Estado de la asignación actualizado correctamente";
    toastType.value = "success";
    showToast.value = true;
    setTimeout(() => {
      showToast.value = false;
    }, 3000);
  } catch (error) {
    console.error("Error al actualizar el estado:", error.message);
    // Mostrar mensaje de error
    toastMessage.value = "Error al actualizar el estado: " + error.message;
    toastType.value = "error";
    showToast.value = true;
    setTimeout(() => {
      showToast.value = false;
    }, 3000);
  }
};

const viewUsersAnswered = formId => {
  navigateTo(`/professor/formularis/respostes/${formId}`);
};

const openAssignModal = form => {
  selectedForm.value = form;
  showAssignModal.value = true;
};

const handleFormAssigned = assignments => {
  toastMessage.value =
    "Formulario asignado correctamente a los estudiantes seleccionados";
  toastType.value = "success";
  showToast.value = true;
  setTimeout(() => {
    showToast.value = false;
    toastMessage.value = "";
    toastType.value = "";
  }, 3000);
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Formularis</h1>
          <p class="mt-1 text-sm text-gray-500">
            Gestiona els formularis i les seves assignacions
          </p>
        </div>
        <button @click="navigateTo('/professor/formularis/nou')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
          <PlusIcon class="w-5 h-5 mr-2" />
          Nou Formulari
        </button>
      </div>

      <CommonToast v-if="showToast" :message="toastMessage" :type="toastType" />

      <!-- Filters Section -->
      <div class="bg-white rounded-xl shadow-sm mb-6">
        <div class="p-4 space-y-4 md:space-y-0 md:flex md:items-center md:space-x-4">
          <div class="flex-1">
            <div class="relative">
              <input v-model="searchQuery" type="text" placeholder="Buscar formularis..."
                class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
              <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          <div class="flex flex-col md:flex-row gap-4">
            <select v-model="selectedDivision"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="all">Tots los estats</option>
              <option value="active">Actius</option>
              <option value="draft">Borradors</option>
              <option value="closed">Tancats</option>
            </select>
            <select v-model="selectedDate"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="all">Totes les dates</option>
              <option value="today">Avui</option>
              <option value="week">Aquesta setmana</option>
              <option value="month">Aquest mes</option>
            </select>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Títol
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Estat
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Data
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Time
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Accions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="form in forms" :key="form.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ form.title }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ form.description }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="{
                    'bg-green-100 text-green-800': form.status === 1,
                    'bg-red-100 text-red-800': form.status === 0,
                  }">
                    {{ form.status === 1 ? "actiu" : "inactiu" }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ form.date_limit }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ form.time_limit }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <div class="flex justify-end space-x-3">
                    <button
                      class="flex items-center space-x-1 px-3 py-1 bg-primary text-white rounded-md hover:bg-primary/90 transition-colors"
                      title="Asignar a estudiantes" @click="openAssignModal(form)">
                      <UserGroupIcon class="w-4 h-4" />
                      <span>Assignar</span>
                    </button>
                    <button 
                      class="text-gray-400 hover:text-primary" 
                      @click="updateFormStatus(form.id, form.status === 1 ? 0 : 1, form.course_id, form.division_id)"
                      :disabled="!form.course_id || !form.division_id"
                      :title="!form.course_id || !form.division_id ? 'Este formulario no tiene asignación' : `Cambiar estado (${form.status === 1 ? 'desactivar' : 'activar'})`"
                    >
                      <!-- Cambiar el ícono dependiendo del estado del formulario -->
                      <component :is="form.status === 0 ? EyeIcon : EyeSlashIcon" class="w-5 h-5" />
                      <span class="sr-only">{{ form.status === 1 ? 'Desactivar' : 'Activar' }} formulario</span>
                    </button>
                    <button @click="viewUsersAnswered(form.id)">
                      Ver respuestas
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <FormsAssignFormModal v-model="showAssignModal" :form="selectedForm || {}" @assigned="handleFormAssigned" />
    </main>
  </div>
</template>
