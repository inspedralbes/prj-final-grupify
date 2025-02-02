<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import StudentProfile from "@/components/Students/StudentProfile.vue";
import { useStudentsStore } from "@/stores/studentsStore";

const route = useRoute();
const studentsStore = useStudentsStore();

// Estado para el estudiante encontrado, error y loading
const student = ref(null);
const isLoading = ref(true);
const error = ref("");
const id = Number(route.params.id); // Convierte el ID a número

// Usar onMounted para la carga inicial
onMounted(async () => {
  try {
    // Llamar a la API al montar el componente
    await studentsStore.fetchStudents();

    // Verifica que los estudiantes estén cargados
    // console.log('Estudiantes cargados:', studentsStore.students);

    if (!id) {
      console.error("No se pasó un ID de estudiante en la URL");
      error.value = "ID no proporcionado";
      return;
    }

    // Buscar el estudiante por el ID
    student.value = studentsStore.getStudentById(id);

    // Verificar si se encontró al estudiante
    // console.log('Estudiante encontrado:', student.value);

    if (!student.value) {
      console.error("Estudiante no encontrado con ID:", id);
      error.value = "Estudiante no encontrado";
    }
  } catch (err) {
    console.error("Error al cargar los estudiantes:", err);
    error.value = "Error al cargar los estudiantes";
  } finally {
    isLoading.value = false;
  }
});
</script>

<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Perfil d'Estudiant</h1>
    </div>

    <!-- Mostrar cargando si la información está siendo obtenida -->
    <div v-if="isLoading" class="text-center py-8 text-gray-500">
      Carregant perfil...
    </div>

    <!-- Mostrar error si hay algún problema al encontrar el estudiante -->
    <div v-else-if="error" class="text-center py-8 text-red-500">
      {{ error }}
    </div>

    <!-- Mostrar el perfil del estudiante si se encontró -->
    <div v-if="student" class="space-y-6">
      <StudentProfile :student="student" />
    </div>
  </div>
</template>
