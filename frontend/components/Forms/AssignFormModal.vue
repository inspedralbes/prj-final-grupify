<script setup>
import { XMarkIcon } from "@heroicons/vue/24/outline";
import { useFormAssignmentsStore } from "@/stores/formAssignments";
import { useAuthStore } from "@/stores/authStore";

const props = defineProps({
  modelValue: Boolean,
  form: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "assigned"]);
const formAssignmentsStore = useFormAssignmentsStore();
const authStore = useAuthStore();

//VARIABLE LOCALSE
const dueDate = ref(null);
const students = ref([]);
const selectedStudents = ref([]);
const courses = ref([]); 
const divisions = ref([]); 
const selectedCourse = ref(null); 
const selectedDivision = ref(null); 
const isLoading = ref(false);
const errorMessage = ref('');

const assignFormToCourseAndDivision = async () => {
  if (!selectedCourse.value || !selectedDivision.value) {
    alert("Por favor, selecciona un curso y una división antes de asignar el formulario.");
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';

  // Recoger los datos del formulario
  const selectedCourseId = selectedCourse.value;
  const selectedDivisionId = selectedDivision.value;
  const formId = props.form.id;

  try {
    const token = authStore.token;
    
    if (!token) {
      errorMessage.value = "Error de autenticación: No se encontró un token válido.";
      console.error("No authentication token found");
      return;
    }
    
    const response = await fetch('http://localhost:8000/api/forms/assign-to-course-division', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        course_id: selectedCourseId,
        division_id: selectedDivisionId,
        form_id: formId,
      }),
    });

    if (response.ok) {
      const data = await response.json();
      emit("assigned", data); 
      emit("update:modelValue", false);
    } else {
      const errorData = await response.json();
      console.error('Error al asignar formulario:', errorData.message);
      errorMessage.value = errorData.message || 'Hubo un error al asignar el formulario';
    }
  } catch (error) {
    console.error('Error en la petición:', error);
    errorMessage.value = 'Hubo un error al realizar la solicitud';
  } finally {
    isLoading.value = false;
  }
};

// Cargar los cursos y divisiones asignados al profesor al montar el componente
onMounted(async () => {
  isLoading.value = true;
  errorMessage.value = '';
  
  try {
    // Si el profesor tiene course_divisions en el authStore, usarlos directamente
    if (authStore.user && authStore.user.course_divisions && authStore.user.course_divisions.length > 0) {
      // Crear un conjunto único de course_ids
      const uniqueCourseIds = new Set();
      const uniqueCourses = [];
      
      // Filtrar y agregar solo cursos únicos
      authStore.user.course_divisions.forEach(cd => {
        if (!uniqueCourseIds.has(cd.course_id)) {
          uniqueCourseIds.add(cd.course_id);
          uniqueCourses.push({
            id: cd.course_id,
            name: cd.course_name
          });
        }
      });
      
      courses.value = uniqueCourses;
      
      // Si solo hay un curso, seleccionarlo automáticamente y cargar sus divisiones
      if (courses.value.length === 1) {
        selectedCourse.value = courses.value[0].id;
        await loadDivisionsForCourse();
      }
    } else {
      // Fallback: cargar todos los cursos si no se encontraron cursos asignados al profesor
      const response = await fetch("http://localhost:8000/api/courses");
      if (!response.ok) throw new Error("Error al cargar los cursos");
      
      const data = await response.json();
      courses.value = data;
    }
  } catch (error) {
    console.error("Error al cargar los cursos:", error);
    errorMessage.value = "Error al cargar los cursos. Por favor, inténtalo de nuevo.";
  } finally {
    isLoading.value = false;
  }
});

// Manejar la selección de un curso
const fetchDivisions = async () => {
  selectedDivision.value = null;
  await loadDivisionsForCourse();
};

// Función para cargar las divisiones del curso seleccionado
const loadDivisionsForCourse = async () => {
  if (!selectedCourse.value) {
    divisions.value = [];
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';

  try {
    // Si el profesor tiene course_divisions en el authStore, filtrar las divisiones del curso seleccionado
    if (authStore.user && authStore.user.course_divisions && authStore.user.course_divisions.length > 0) {
      const divisionsForCourse = authStore.user.course_divisions
        .filter(cd => cd.course_id === selectedCourse.value)
        .map(cd => ({
          id: cd.division_id,
          division: cd.division_name
        }));
      
      divisions.value = divisionsForCourse;
      
      // Si solo hay una división, seleccionarla automáticamente
      if (divisions.value.length === 1) {
        selectedDivision.value = divisions.value[0].id;
      }
    } else {
      // Fallback: obtener las divisiones del curso desde la API
      const response = await fetch(`http://localhost:8000/api/course-divisions?course_id=${selectedCourse.value}`);
      if (!response.ok) throw new Error("Error al cargar las divisiones");

      const data = await response.json();
      if (data.divisions) {
        divisions.value = data.divisions;
      } else {
        divisions.value = [];
        console.error(data.message || 'No se encontraron divisiones');
      }
    }
  } catch (error) {
    console.error("Error al cargar las divisiones:", error);
    errorMessage.value = "Error al cargar las divisiones. Por favor, inténtalo de nuevo.";
    divisions.value = [];
  } finally {
    isLoading.value = false;
  }
};

// Asignar formulario a los estudiantes seleccionados
const assignForm = () => {
  const studentsToAssign = students.value.filter((s) =>
    selectedStudents.value.includes(s.id)
  );
  const assignments = formAssignmentsStore.assignFormToStudents(
    props.form,
    studentsToAssign,
    dueDate.value ? new Date(dueDate.value) : null
  );

  emit("assigned", assignments);
  emit("update:modelValue", false);
};

// Cerrar el modal y reiniciar los filtros
const close = () => {
  emit("update:modelValue", false);
  selectedCourse.value = null; // Reiniciar curso seleccionado
  selectedDivision.value = null; // Reiniciar división seleccionada
  divisions.value = []; // Limpiar divisiones
  dueDate.value = null; // Reiniciar la fecha límite si es necesario
  selectedStudents.value = []; // Limpiar estudiantes seleccionados
  errorMessage.value = ''; // Limpiar mensaje de error
};
</script>

<template>
  <div v-if="props.modelValue" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <div class="flex min-h-full items-end justify-center p-4 sm:items-center sm:p-0">
      <div
        class="relative transform rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
        <button class="absolute right-4 top-4 text-gray-400 hover:text-gray-500" @click="close">
          <XMarkIcon class="h-6 w-6" />
        </button>

        <div class="mt-3 sm:mt-5">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
            Assignar Formulari
          </h3>

          <!-- Error message -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
            {{ errorMessage }}
          </div>

          <div v-if="isLoading" class="flex justify-center my-4">
            <div class="w-8 h-8 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
          </div>

          <div v-else class="space-y-4">
            <!-- Selector de cursos -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Seleccionar Curso
              </label>
              <select v-model="selectedCourse" @change="fetchDivisions"
                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="" disabled selected>Selecciona un curso</option>
                <option v-for="course in courses" :key="course.id" :value="course.id">
                  {{ course.name }}
                </option>
              </select>
            </div>

            <!-- Selector de divisiones -->
            <div v-if="divisions.length > 0">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Seleccionar División
              </label>
              <select v-model="selectedDivision"
                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="" disabled selected>Selecciona una división</option>
                <option v-for="division in divisions" :key="division.id" :value="division.id">
                  {{ division.division }}
                </option>
              </select>
            </div>
            
            <!-- No divisiones message -->
            <div v-else-if="selectedCourse && !isLoading" class="text-yellow-600 text-sm mt-2">
              No hay divisiones disponibles para este curso.
            </div>
          </div>
        </div>

        <div class="mt-5 sm:mt-6 flex justify-end space-x-3">
          <button class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800" @click="close">
            Cancelar
          </button>
          <button 
            class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90 disabled:bg-gray-300 disabled:cursor-not-allowed"
            @click="assignFormToCourseAndDivision"
            :disabled="isLoading || !selectedCourse || !selectedDivision">
            <span v-if="isLoading" class="inline-block mr-1">
              <svg class="animate-spin h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            Assignar Formulari
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
