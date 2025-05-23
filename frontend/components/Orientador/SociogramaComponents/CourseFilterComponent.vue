<script setup>
import { ref, computed, watch } from "vue";

// Definir las props que recibirá el componente
const props = defineProps({
  allCourses: {
    type: Array,
    required: true
  },
  initialCourseAndDivision: {
    type: String,
    default: null
  },
  initialThreshold: {
    type: Number,
    default: 1.5
  },
  showAllData: {
    type: Boolean,
    default: false
  }
});

// Definir los eventos que emitirá el componente
const emit = defineEmits(['update:courseAndDivision', 'update:threshold', 'loadData']);

// Estado interno del componente
const selectedCourseAndDivision = ref(props.initialCourseAndDivision);
const threshold = ref(props.initialThreshold);
const viewAllData = ref(props.showAllData || localStorage.getItem('orientadorViewAllData') === 'true');

// Computed property para obtener las opciones del selector combinado
const courseOptions = computed(() => {
  if (!props.allCourses || props.allCourses.length === 0) return [];
  
  return props.allCourses.map(course => ({
    value: `${course.courseName}_${course.division.name}`,
    label: `${course.courseName} ${course.division.name}`
  }));
});

// Vigilar cambios en las props externas
watch(() => props.initialCourseAndDivision, (newValue) => {
  if (newValue !== selectedCourseAndDivision.value) {
    selectedCourseAndDivision.value = newValue;
  }
});

watch(() => props.initialThreshold, (newValue) => {
  if (newValue !== threshold.value) {
    threshold.value = newValue;
  }
});

// Vigilar cambios en los valores internos y emitir eventos
watch(selectedCourseAndDivision, (newValue) => {
  emit('update:courseAndDivision', newValue);
});

watch(threshold, (newValue) => {
  emit('update:threshold', newValue);
});

// Método para cargar datos
const loadData = () => {
  emit('loadData');
};

// Método para alternar entre ver todos los datos o filtrados
const toggleViewAllData = () => {
  viewAllData.value = !viewAllData.value;
  localStorage.setItem('orientadorViewAllData', viewAllData.value ? 'true' : 'false');
  emit('loadData');
};
</script>

<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Selector de curso combinado -->
      <div>
        <div class="flex items-center justify-between">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Seleccionar curs i grup
          </label>
          <div class="flex items-center">
            <input 
              type="checkbox" 
              id="viewAllData" 
              v-model="viewAllData"
              @change="toggleViewAllData"
              class="h-4 w-4 rounded border-gray-300 text-[#0080C0] focus:ring-[#0080C0]"
            />
            <label for="viewAllData" class="ml-2 block text-sm text-gray-700">Mostrar tots els cursos</label>
          </div>
        </div>
        <select
          v-model="selectedCourseAndDivision"
          class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#0080C0] focus:border-[#0080C0] sm:text-sm rounded-md"
          :disabled="viewAllData"
        >
          <option
            v-for="option in courseOptions"
            :key="option.value"
            :value="option.value"
          >
            {{ option.label }}
          </option>
        </select>
        <button
          @click="loadData"
          class="mt-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[#0080C0] hover:bg-[#006699] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0080C0]"
        >
          Carregar dades
        </button>
      </div>

      <!-- Selector de umbral -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Llindar de destacat (desviacions estàndard): {{ threshold }}
        </label>
        <input
          type="range"
          v-model="threshold"
          min="0.5"
          max="3"
          step="0.1"
          @change="loadData"
          class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
        />
        <div class="flex justify-between text-xs text-gray-500 mt-1">
          <span>0.5 (Més inclusiu)</span>
          <span>3 (Més selectiu)</span>
        </div>
      </div>
    </div>
  </div>
</template>