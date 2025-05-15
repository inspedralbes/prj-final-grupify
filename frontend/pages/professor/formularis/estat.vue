<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Formularios Asignados</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-xl font-semibold mb-4">Asignar Nuevo Formulario</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-2">Formulario</label>
          <select 
            v-model="newAssignment.form_id" 
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="" disabled>Seleccionar formulario</option>
            <option v-for="form in forms" :key="form.id" :value="form.id">{{ form.title }}</option>
          </select>
        </div>
        
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-2">Curso</label>
          <select 
            v-model="newAssignment.course_id" 
            @change="loadDivisions"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="" disabled>Seleccionar curso</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">{{ course.name }}</option>
          </select>
        </div>
        
        <div>
          <label class="block text-gray-700 text-sm font-medium mb-2">División</label>
          <select 
            v-model="newAssignment.division_id"
            class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
            :disabled="!newAssignment.course_id"
          >
            <option value="" disabled>Seleccionar división</option>
            <option v-for="division in divisions" :key="division.id" :value="division.id">{{ division.name }}</option>
          </select>
        </div>
      </div>
      
      <button 
        @click="assignForm" 
        class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition"
        :disabled="!formValid"
      >
        Asignar Formulario
      </button>
    </div>
    
    <div class="bg-white rounded-lg shadow-md p-6">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Formularios Asignados</h2>
        <button 
          @click="loadAssignments" 
          class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-md flex items-center"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Actualizar
        </button>
      </div>
      
      <div v-if="assignments.length === 0" class="text-center py-8 text-gray-500">
        No hay formularios asignados
      </div>
      
      <div v-else class="overflow-x-auto">
        <table class="min-w-full bg-white">
          <thead class="bg-gray-100">
            <tr>
              <th class="py-2 px-4 text-left">Formulario</th>
              <th class="py-2 px-4 text-left">Curso</th>
              <th class="py-2 px-4 text-left">División</th>
              <th class="py-2 px-4 text-left">Respuestas</th>
              <th class="py-2 px-4 text-left">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="assignment in assignments" :key="assignment.id" class="border-b hover:bg-gray-50">
              <td class="py-2 px-4">{{ assignment.form.title || assignment.form.name }}</td>
              <td class="py-2 px-4">{{ assignment.course.name }}</td>
              <td class="py-2 px-4">{{ assignment.division.name }}</td>
              <td class="py-2 px-4">
                <div class="flex items-center">
                  <div class="flex items-center bg-gray-100 px-3 py-1 rounded-lg">
                    <span class="font-bold text-lg mr-2">{{ assignment.responses_count || 0 }}</span>
                    <span class="text-gray-600 text-sm">respuestas</span>
                    <button 
                      @click="updateCounter(assignment.id)" 
                      class="ml-2 text-blue-500 hover:text-blue-700"
                      title="Actualizar contador"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                      </svg>
                    </button>
                  </div>
                  <div class="ml-3 w-24 bg-gray-200 rounded-full h-2.5">
                    <div 
                      class="bg-blue-600 h-2.5 rounded-full" 
                      :style="{ width: calculateCompletionPercentage(assignment) + '%' }"
                    ></div>
                  </div>
                </div>
              </td>
              <td class="py-2 px-4">
                <NuxtLink :to="`/professor/formularis/respostat/${assignment.id}`" class="text-blue-600 hover:underline mr-3">
                  Ver Respuestas
                </NuxtLink>
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

const assignments = ref([]);
const forms = ref([]);
const courses = ref([]);
const divisions = ref([]);
const isLoading = ref(false);

const newAssignment = ref({
  form_id: '',
  course_id: '',
  division_id: '',
  teacher_id: ''
});

// Obtener el ID del usuario autenticado
const userId = ref(null);

onMounted(async () => {
  try {
    // Cargar el usuario autenticado
    const userResponse = await fetch('/api/user', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (userResponse.ok) {
      const userData = await userResponse.json();
      userId.value = userData.id;
      newAssignment.value.teacher_id = userData.id;
      
      // Cargar las asignaciones del profesor
      await loadAssignments();
      
      // Cargar formularios, cursos y divisiones
      await Promise.all([
        loadForms(),
        loadCourses()
      ]);
    }
  } catch (error) {
    console.error('Error al cargar datos iniciales:', error);
  }
});

const loadAssignments = async () => {
  try {
    isLoading.value = true;
    
    const response = await fetch(`/api/form-assignments/teacher/${userId.value}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      console.log('Asignaciones cargadas:', data);
      assignments.value = data;
      
      // Agregar un botón de recarga
      for (const assignment of assignments.value) {
        console.log(`Asignación ID ${assignment.id}: ${assignment.responses_count} respuestas`);
      }
    }
  } catch (error) {
    console.error('Error al cargar asignaciones:', error);
  } finally {
    isLoading.value = false;
  }
};

const loadForms = async () => {
  try {
    const response = await fetch('/api/forms', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      forms.value = await response.json();
    }
  } catch (error) {
    console.error('Error al cargar formularios:', error);
  }
};

const loadCourses = async () => {
  try {
    const response = await fetch('/api/courses', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      courses.value = await response.json();
    }
  } catch (error) {
    console.error('Error al cargar cursos:', error);
  }
};

// Función para actualizar manualmente el contador de un formulario
const updateCounter = async (assignmentId) => {
  try {
    const response = await fetch(`/api/form-assignments/${assignmentId}/update-count`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      console.log('Contador actualizado:', data);
      
      // Actualizar el contador en la lista de asignaciones
      const assignment = assignments.value.find(a => a.id === assignmentId);
      if (assignment) {
        assignment.responses_count = data.count;
      }
      
      // Notificar al usuario
      alert(`Contador actualizado: ${data.count} respuestas`);
    } else {
      console.error('Error al actualizar el contador');
    }
  } catch (error) {
    console.error('Error:', error);
  }
};

const loadDivisions = async () => {
  try {
    if (!newAssignment.value.course_id) return;
    
    const response = await fetch(`/api/course-divisions?course_id=${newAssignment.value.course_id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      divisions.value = await response.json();
    }
  } catch (error) {
    console.error('Error al cargar divisiones:', error);
  }
};

const formValid = computed(() => {
  return newAssignment.value.form_id && 
         newAssignment.value.course_id && 
         newAssignment.value.division_id &&
         newAssignment.value.teacher_id;
});

const assignForm = async () => {
  try {
    if (!formValid.value) return;
    
    const response = await fetch('/api/forms/assign-to-course-division', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      },
      body: JSON.stringify(newAssignment.value)
    });
    
    if (response.ok) {
      // Recargar las asignaciones
      await loadAssignments();
      
      // Resetear el formulario
      newAssignment.value = {
        form_id: '',
        course_id: '',
        division_id: '',
        teacher_id: userId.value
      };
      divisions.value = [];
    } else {
      // Manejo de errores
      const errorData = await response.json();
      console.error('Error al asignar formulario:', errorData);
      alert(`Error: ${errorData.message || 'No se pudo asignar el formulario'}`);
    }
  } catch (error) {
    console.error('Error al asignar formulario:', error);
    alert('Error al conectar con el servidor');
  }
};

const calculateCompletionPercentage = (assignment) => {
  // Obtener información de estudiantes para este curso/división
  if (!assignment || typeof assignment.responses_count === 'undefined') {
    return 0;
  }
  
  // Crear un endpoint para obtener el total de estudiantes
  const totalEstimated = 20; // Valor por defecto en caso de que no haya otro dato
  
  // Calcular el porcentaje
  const percentage = Math.round((assignment.responses_count / totalEstimated) * 100);
  
  // Mostrar el porcentaje en la consola para depuración
  console.log(`Formulario ID: ${assignment.id}, Respuestas: ${assignment.responses_count}, Porcentaje: ${percentage}%`);
  
  return percentage;
};
</script>
