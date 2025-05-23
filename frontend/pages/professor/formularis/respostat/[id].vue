<template>
  <div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <h1 class="text-2xl font-bold mb-4" v-if="assignmentData">
        Estado de respuestas: {{ assignmentData.assignment.form.name }}
      </h1>
      <div class="flex items-center mb-4" v-if="assignmentData">
        <div class="mr-8">
          <p class="text-gray-600">
            Curso: <span class="font-medium">{{ assignmentData.assignment.course.name }}</span>
          </p>
          <p class="text-gray-600">
            División: <span class="font-medium">{{ assignmentData.assignment.division.name }}</span>
          </p>
        </div>
        
        <div class="flex items-center">
          <div class="relative w-40 h-40">
            <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
              <path
                class="stroke-current text-gray-200"
                fill="none"
                stroke-width="3.8"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <path
                :class="progressColor"
                fill="none"
                stroke-width="3.8"
                :stroke-dasharray="`${percentageComplete}, 100`"
                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
              />
              <text x="18" y="19" text-anchor="middle" class="text-3xl font-semibold" transform="rotate(90 18 18)">
                {{ percentageComplete }}%
              </text>
            </svg>
          </div>
          <div class="ml-4">
            <p class="font-medium">{{ assignmentData.stats.answered }} de {{ assignmentData.stats.total }} estudiantes han respondido</p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-bold mb-4">Listado de estudiantes</h2>
      
      <div class="mb-4">
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Buscar estudiante..."
          class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
          <thead class="bg-gray-100">
            <tr>
              <th class="py-2 px-4 text-left">Nombre</th>
              <th class="py-2 px-4 text-left">Email</th>
              <th class="py-2 px-4 text-left">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in filteredStudents" :key="student.id" class="border-b hover:bg-gray-50">
              <td class="py-2 px-4">{{ student.name }}</td>
              <td class="py-2 px-4">{{ student.email }}</td>
              <td class="py-2 px-4">
                <span
                  :class="student.answered ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                  class="px-2 py-1 rounded-full text-xs font-medium"
                >
                  {{ student.answered ? 'Completado' : 'Pendiente' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const assignmentData = ref(null);
const searchTerm = ref('');
const isLoading = ref(true);

const assignmentId = computed(() => route.params.id);

onMounted(async () => {
  try {
    await fetchAssignmentDetails();
  } catch (error) {
    console.error('Error al cargar los datos:', error);
  } finally {
    isLoading.value = false;
  }
});

const fetchAssignmentDetails = async () => {
  try {
    const response = await fetch(`/api/form-assignments/${assignmentId.value}`, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    });
    
    if (!response.ok) {
      throw new Error('Error al obtener los detalles de la asignación');
    }
    
    assignmentData.value = await response.json();
  } catch (error) {
    console.error('Error:', error);
  }
};

const percentageComplete = computed(() => {
  if (!assignmentData.value) return 0;
  
  const { answered, total } = assignmentData.value.stats;
  return Math.round((answered / total) * 100) || 0;
});

const progressColor = computed(() => {
  const percentage = percentageComplete.value;
  
  if (percentage < 25) {
    return 'stroke-current text-red-500';
  } else if (percentage < 75) {
    return 'stroke-current text-yellow-500';
  } else {
    return 'stroke-current text-green-500';
  }
});

const filteredStudents = computed(() => {
  if (!assignmentData.value) return [];
  
  return assignmentData.value.students.filter(student => {
    const searchTermLower = searchTerm.value.toLowerCase();
    return (
      student.name.toLowerCase().includes(searchTermLower) ||
      student.email.toLowerCase().includes(searchTermLower)
    );
  });
});
</script>
