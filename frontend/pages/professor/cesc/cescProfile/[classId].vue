<script setup>
import { ref, onMounted, computed } from "vue";
import { useCoursesStore } from "~/stores/coursesStore";
import { useResultatCescStore } from "~/stores/resultatsCescStore";
import { useStudentsStore } from "~/stores/studentsStore";
import { useRoute } from "vue-router";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const route = useRoute();
const classId = ref(null);
const error = ref(null);
const isLoading = ref(true);
const students = ref([]);
const course = ref(null);
const coursesStore = useCoursesStore();
const studentsStore = useStudentsStore();
const resultatsCescStore = useResultatCescStore();

classId.value = route.params.classId;

// Initialize component
onMounted(async () => {
  try {
    if (!classId.value) throw new Error("classId no encontrado");

    await coursesStore.fetchCourses();
    course.value = coursesStore.courses.find(c => c.classId == classId.value);
    if (!course.value) throw new Error("Curso no encontrado");

    await studentsStore.fetchStudents();
    students.value = studentsStore.students.filter(
      student =>
        student.course === course.value.courseName &&
        student.division === course.value.division.name
    );
    await resultatsCescStore.fetchResults();
  } catch (err) {
    console.error("Error al cargar los datos:", err);
    error.value = "Error al cargar los datos";
  } finally {
    isLoading.value = false;
  }
});

// Filtrar datos en base al curso y la división
const filtered = computed(() => {
  if (!course.value) return [];

  console.log("Obteniendo datos filtrados...");

  const data = resultatsCescStore.getCescByCourseAndDivision(
    course.value.courseName,
    course.value.division.name
  );

  console.log(
    "CESC POR CURSO:",
    course.value.courseName,
    course.value.division.name,
    data
  );

  return data;
});
</script>

<template>
  <div class="min-h-screen bg-white">
    <DashboardNavTeacher class="w-full" />
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8">
        <h1 class="text-3xl font-semibold text-[#0080C0] text-center">
          RESULTATS CESC
        </h1>
      </div>
      <div v-if="isLoading" class="bg-white rounded-lg shadow-md p-8 flex flex-col items-center justify-center min-h-[300px]">Cargando...</div>
      <div v-else-if="error">{{ error }}</div>

      <!-- Verificación de datos -->
      <div v-else>
        <p v-if="filtered.length === 0">
          No hay datos filtrados para este curso y división.
        </p>

        <!-- Mostrar datos -->
        <ul v-if="filtered.length > 0">
          <li v-for="item in filtered" :key="item.id">
            <p>
              <strong>Peer:</strong> {{ item.peer_name }}
              {{ item.peer_last_name }}
            </p>
            <p><strong>Tag:</strong> {{ item.tag_name }}</p>
            <!-- Aquí mostramos el nombre del tag -->
            <p><strong>Vote Count:</strong> {{ item.vote_count }}</p>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
