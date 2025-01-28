<script setup>
import { XMarkIcon } from "@heroicons/vue/24/outline";
import { useFormAssignmentsStore } from "@/stores/formAssignments";
import { ref, computed } from "vue";

const props = defineProps({
  modelValue: Boolean,
  form: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue", "assigned"]);
const formAssignmentsStore = useFormAssignmentsStore();
const dueDate = ref(null);
const selectedStudents = ref([]);
const selectedCourseId = ref(null); // Estado para el curso seleccionado
const selectedDivisionId = ref(null); // Estado para la división seleccionada

const coursesStore = useCoursesStore();

onMounted(async () => {
  try {
    // Cargar los cursos desde el store
    await coursesStore.fetchCourses();
  } catch (err) {
    error.value = 'Error al cargar los cursos';
  } finally {
    isLoading.value = false;
  }
});

// Computed para acceder a los cursos cargados
const courses = computed(() => coursesStore.courses || []);

// Computada para obtener las divisiones del curso seleccionado
const filteredDivisions = computed(() => {
  const selectedCourse = courses.courses.find((course) => course.id === Number(selectedCourseId.value));
  return selectedCourse ? selectedCourse.divisions : [];
});

const assignForm = () => {
  // Lógica para asignar formulario (ajusta según tus necesidades)
  emit("assigned", {
    courseId: selectedCourseId.value,
    divisionId: selectedDivisionId.value,
    dueDate: dueDate.value ? new Date(dueDate.value) : null,
  });
  emit("update:modelValue", false);
};

const close = () => {
  emit("update:modelValue", false);
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
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Data límit (opcional)
              </label>
              <input v-model="dueDate" type="date"
                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent" />
            </div>

            <div>
              <div class="flex space-x-4">
                <!-- Selector de cursos -->
                <select v-model="selectedCourseId"
                  class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                  <option :value="null" disabled selected>Selecciona un curso</option>
                  <option v-for="course in courses.courses" :key="course.id" :value="course.id">
                    {{ course.name }}
                  </option>
                </select>

                <!-- Selector de divisiones -->
                <select v-model="selectedDivisionId"
                  class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                  :disabled="!selectedCourseId">
                  <option :value="null" disabled selected>Selecciona una división</option>
                  <option v-for="division in filteredDivisions" :key="division.id" :value="division.id">
                    {{ division.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-5 sm:mt-6 flex justify-end space-x-3">
          <button class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800" @click="close">
            Cancelar
          </button>
          <button class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90"
            @click="assignForm" :disabled="!selectedCourseId || !selectedDivisionId">
            Assignar Formulari
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
