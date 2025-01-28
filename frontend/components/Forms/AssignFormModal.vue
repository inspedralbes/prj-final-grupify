<script setup>
import { XMarkIcon } from "@heroicons/vue/24/outline";
import { useFormAssignmentsStore } from "@/stores/formAssignments";

const props = defineProps({
  modelValue: Boolean,
  form: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "assigned"]);
const formAssignmentsStore = useFormAssignmentsStore();

//VARIABLE LOCALSE
const dueDate = ref(null);
const students = ref([]);
const selectedStudents = ref([]);
const courses = ref([]); 
const divisions = ref([]); 
const selectedCourse = ref(null); 
const selectedDivision = ref(null); 

const assignFormToCourseAndDivision = async () => {

  if (!selectedCourse.value || !selectedDivision.value) {
    alert("Por favor, selecciona un curso y una división antes de asignar el formulario.");
    return;
  }

  // Recoger los datos del formulario
  const selectedCourseId = selectedCourse.value;
  const selectedDivisionId = selectedDivision.value;
  const formId = props.form.id;
  // console.log(selectedCourseId, selectedDivisionId, formId);

  try {
    
    const response = await fetch('http://localhost:8000/api/forms/assign-to-course-division', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem("auth_token")}`, 
      },
      body: JSON.stringify({
        course_id: selectedCourseId,
        division_id: selectedDivisionId,
        form_id: formId,
      }),
    });

    if (response.ok) {
      const data = await response.json();
      // console.log('Formulario asignado correctamente', data);
      emit("assigned", data); 
      emit("update:modelValue", false);

    } else {
      const errorData = await response.json();
      console.error('Error al asignar formulario:', errorData.message);
      alert(errorData.message || 'Hubo un error al asignar el formulario');
    }
  } catch (error) {
    console.error('Error en la petición:', error);
    alert('Hubo un error al realizar la solicitud');
  }
};

// Cargar todos los cursos al montar el componente
onMounted(async () => {
  try {
    const response = await fetch("http://localhost:8000/api/courses"); 
    if (!response.ok) throw new Error("Error al cargar los cursos");

    const data = await response.json();
    courses.value = data; 
  } catch (error) {
    console.error("Error al cargar los cursos:", error);
  }
});

// Manejar la selección de un curso
const fetchDivisions = async () => {
  if (!selectedCourse.value) {
    divisions.value = []; 
    return;
  }

  try {
  
    const response = await fetch(`http://localhost:8000/api/course-divisions?course_id=${selectedCourse.value}`);
    if (!response.ok) throw new Error("Error al cargar las divisiones");

    const data = await response.json();
    if (data.divisions) {
      divisions.value = data.divisions; 
    } else {
      divisions.value = []; 
      console.error(data.message || 'No se encontraron divisiones');
    }
  } catch (error) {
    console.error("Error al cargar las divisiones:", error);
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

          <div class="space-y-4">
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
          </div>
        </div>

        <div class="mt-5 sm:mt-6 flex justify-end space-x-3">
          <button class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800" @click="close">
            Cancelar
          </button>
          <button class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90"
            @click="assignFormToCourseAndDivision">
            Assignar Formulari
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
