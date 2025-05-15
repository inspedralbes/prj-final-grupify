<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Diagnóstico de Form Assignments</h1>
    
    <div class="grid grid-cols-1 gap-6">
      <!-- Estado de la migración -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Estado de la Migración</h2>
        <div v-if="isLoading" class="flex justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        </div>
        <div v-else>
          <div class="flex items-center mb-2">
            <div class="w-4 h-4 rounded-full mr-2" :class="migrationStatus ? 'bg-green-500' : 'bg-red-500'"></div>
            <span class="font-medium">Tabla form_assignments:</span>
            <span class="ml-2">{{ migrationStatus ? 'Existe' : 'No existe' }}</span>
          </div>
          <p v-if="!migrationStatus" class="text-red-600 mt-2">
            La tabla form_assignments no existe. Por favor, ejecuta la migración siguiendo las instrucciones en INSTRUCCIONES_MIGRACION.md
          </p>
        </div>
      </div>
      
      <!-- Contador de respuestas -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Contador de Respuestas</h2>
        <div v-if="isLoading" class="flex justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        </div>
        <div v-else>
          <div class="mb-4">
            <label class="block text-gray-700 text-sm font-medium mb-2">Selecciona una asignación:</label>
            <select 
              v-model="selectedAssignment" 
              class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="loadAssignmentDetails"
            >
              <option value="" disabled>Seleccionar asignación</option>
              <option v-for="assignment in assignments" :key="assignment.id" :value="assignment.id">
                {{ assignment.form.title || assignment.form.name }} - {{ assignment.course.name }} {{ assignment.division.name }}
              </option>
            </select>
          </div>
          
          <div v-if="selectedAssignment && assignmentDetails">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div class="bg-gray-100 p-3 rounded-lg">
                <p class="text-sm text-gray-600">Contador actual</p>
                <p class="text-2xl font-bold">{{ assignmentDetails.assignment.responses_count }}</p>
              </div>
              <div class="bg-gray-100 p-3 rounded-lg">
                <p class="text-sm text-gray-600">Estudiantes que han respondido</p>
                <p class="text-2xl font-bold">{{ assignmentDetails.stats.answered }}</p>
              </div>
            </div>
            
            <div v-if="assignmentDetails.assignment.responses_count !== assignmentDetails.stats.answered" class="bg-yellow-100 p-3 rounded-lg mb-4">
              <p class="text-yellow-800">
                <strong>Atención:</strong> El contador ({{ assignmentDetails.assignment.responses_count }}) no coincide con el número de estudiantes que han respondido ({{ assignmentDetails.stats.answered }}).
              </p>
            </div>
            
            <button 
              @click="updateCounter" 
              class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition"
            >
              Actualizar Contador
            </button>
          </div>
          
          <p v-else-if="assignments.length === 0" class="text-gray-500">
            No hay asignaciones disponibles. Primero asigna un formulario a un curso y división.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const migrationStatus = ref(false);
const isLoading = ref(true);
const assignments = ref([]);
const selectedAssignment = ref('');
const assignmentDetails = ref(null);

onMounted(async () => {
  try {
    await checkMigrationStatus();
    await loadAssignments();
  } catch (error) {
    console.error('Error al cargar datos iniciales:', error);
  } finally {
    isLoading.value = false;
  }
});

const checkMigrationStatus = async () => {
  try {
    const response = await fetch('/api/check-migration-status', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      migrationStatus.value = data.form_assignments_exists;
    }
  } catch (error) {
    console.error('Error al verificar el estado de la migración:', error);
    // Si hay error, asumimos que la tabla no existe
    migrationStatus.value = false;
  }
};

const loadAssignments = async () => {
  try {
    // Obtener el ID del usuario autenticado
    const userResponse = await fetch('/api/user', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (userResponse.ok) {
      const userData = await userResponse.json();
      
      // Cargar las asignaciones
      const response = await fetch(`/api/form-assignments/teacher/${userData.id}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Accept': 'application/json'
        }
      });
      
      if (response.ok) {
        assignments.value = await response.json();
      }
    }
  } catch (error) {
    console.error('Error al cargar asignaciones:', error);
  }
};

const loadAssignmentDetails = async () => {
  if (!selectedAssignment.value) return;
  
  try {
    isLoading.value = true;
    
    const response = await fetch(`/api/form-assignments/${selectedAssignment.value}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      assignmentDetails.value = await response.json();
    }
  } catch (error) {
    console.error('Error al cargar detalles de la asignación:', error);
  } finally {
    isLoading.value = false;
  }
};

const updateCounter = async () => {
  if (!selectedAssignment.value) return;
  
  try {
    isLoading.value = true;
    
    const response = await fetch(`/api/form-assignments/${selectedAssignment.value}/update-count`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Accept': 'application/json'
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      
      // Recargar los detalles
      await loadAssignmentDetails();
      
      // Actualizar la asignación en la lista
      const assignment = assignments.value.find(a => a.id === parseInt(selectedAssignment.value));
      if (assignment) {
        assignment.responses_count = data.count;
      }
      
      alert(`Contador actualizado: ${data.count} respuestas`);
    }
  } catch (error) {
    console.error('Error al actualizar el contador:', error);
    alert('Error al actualizar el contador. Consulta la consola para más detalles.');
  } finally {
    isLoading.value = false;
  }
};
</script>
