<script setup>
import { onMounted, ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useCoursesStore } from "~/stores/coursesStore";
import { useRelationshipsStore } from "~/stores/relationships";
import Relations from "@/components/Orientador/SociogramaComponents/Relations.vue";
import Roles from "@/components/Orientador/SociogramaComponents/Roles.vue";
import RolesGraphic from "~/components/Orientador/SociogramaComponents/RolesGraphic.vue";
import Skills from "@/components/Orientador/SociogramaComponents/Skills.vue";
import DashboardNavOrientador from "@/components/Orientador/DashboardNavOrientador.vue";
import SociogramStatus from "@/components/Orientador/SociogramaComponents/SociogramStatus.vue";
import { useStudentsStore } from "~/stores/studentsStore";
import { ScatterChart } from "echarts/charts";
// Existing setup code remains the same
const route = useRoute();
const classId = ref(null);
const error = ref(null);
const isLoading = ref(true);
const students = ref([]);
const course = ref(null);
const activeComponent = ref("relations");
const coursesStore = useCoursesStore();
const relationshipsStore = useRelationshipsStore();
const studentsStore = useStudentsStore();

classId.value = route.params.classId;

// Existing mounted logic remains the same
onMounted(async () => {
  try {
    if (!classId.value) throw new Error("classId no encontrado");

    await coursesStore.fetchCourses();
    course.value = coursesStore.courses.find(c => c.classId == classId.value);
    if (!course.value) throw new Error("Curso no encontrado");

    if (!course.value.hasOwnProperty("sociograma_available")) {
      course.value.sociograma_available = false;
    }
    await studentsStore.fetchStudents();
    students.value = studentsStore.students.filter(
      student =>
        student.course === course.value.courseName &&
        student.division === course.value.division.name
    );
    await relationshipsStore.fetchRelationships();
  } catch (err) {
    console.error("Error al cargar los datos:", err);
    error.value = "Error al cargar los datos";
  } finally {
    isLoading.value = false;
  }
});

const filteredRelationships = computed(() => {
  if (course.value) {
    return relationshipsStore.getRelationshipsByCourseAndDivision(
      course.value.courseName,
      course.value.division.name
    ).value;
  }
  return [];
});
const filteredSkills = computed(() => {
  if (course.value) {
    return relationshipsStore.getSkillsByCourseAndDivision(
      course.value.courseName,
      course.value.division.name
    ).value;
  }
  return [];
});
const filteredRoles = computed(() => {
  if (course.value) {
    return relationshipsStore.getRolesByCourseAndDivision(
      course.value.courseName,
      course.value.division.name
    ).value;
  }
  return [];
});
const toggleComponent = (component) => {
  activeComponent.value = activeComponent.value === component ? "" : component;
};
</script>
<template>
  <div class="min-h-screen bg-white">
    <DashboardNavOrientador class="w-full" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Simplified Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-semibold text-[#0080C0] text-center">
          RESULTATS SOCIOGRAMA
        </h1>
      </div>

      <!-- Loading State -->
      <div
        v-if="isLoading"
        class="bg-white rounded-lg shadow-md p-8 flex flex-col items-center justify-center min-h-[300px]"
      >
        <div class="relative w-16 h-16">
          <div
            class="absolute inset-0 rounded-full border-4 border-[#0080C0] border-t-transparent animate-spin"
          ></div>
        </div>
        <p class="mt-4 text-base text-gray-600">
          Carregant dades del Sociograma...
        </p>
      </div>

      <!-- Error State -->
      <div
        v-else-if="error"
        class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm flex items-center"
      >
        <svg
          class="w-6 h-6 text-red-500 mr-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <p class="text-red-700">{{ error }}</p>
      </div>

      <!-- Main Content -->
      <div v-else class="space-y-6">
        <!-- Simplified Course Information -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex justify-between items-center bg-gray-50 p-4 rounded-md text-gray-700">
            <div class="flex items-center space-x-2">
              <svg
                class="w-5 h-5 text-[#0080C0]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"
                />
              </svg>
              <span class="font-medium">
                {{ course ? course.courseName : "" }} - {{ course && course.division ? course.division.name : "" }}
              </span>
            </div>
            <!-- Status Bar -->
            <SociogramStatus
              v-if="course && course.sociograma_available !== undefined"
              :course="course"
            />
          </div>
        </div>

        <!-- Simplified Toggle Buttons -->
        <div class="flex justify-center space-x-4">
          <button
            v-for="(label, key) in {
              relations: 'Relacions',
              roles: 'Rols',
              skills: 'Competències',
            }"
            :key="key"
            @click="toggleComponent(key)"
            :class="[
              'px-6 py-2 rounded-md shadow-sm transition-colors duration-200',
              'text-sm font-medium',
              activeComponent === key
                ? 'bg-[#0080C0] text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
            ]"
          >
            {{ label }}
          </button>
        </div>

        <!-- Component Display -->
        <div class="mt-6">
          <TransitionGroup name="fade">
            <div
              v-if="activeComponent === 'relations'"
              key="relations"
              class="animate-fade-in"
            >
              <Relations :relationships="filteredRelationships" />
            </div>
            <div
              v-else-if="activeComponent === 'roles'"
              key="roles"
              class="animate-fade-in"
            >
              <RolesGraphic :filteredRoles="filteredRoles" />
              <Roles :filteredRoles="filteredRoles" />
            </div>
            <div
              v-else-if="activeComponent === 'skills'"
              key="skills"
              class="animate-fade-in"
            >
              <Skills :filteredSkills="filteredSkills" />
            </div>
          </TransitionGroup>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>