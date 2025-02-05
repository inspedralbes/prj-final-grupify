<script setup>
import { onMounted, ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useCoursesStore } from "~/stores/coursesStore";
import { useStudentsStore } from "~/stores/studentsStore";
import { useRelationshipsStore } from "~/stores/relationships";
import Relations from "@/components/Teacher/SociogramaComponents/Relations.vue";
import Roles from "@/components/Teacher/SociogramaComponents/Roles.vue";
import Skills from "@/components/Teacher/SociogramaComponents/Skills.vue";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const route = useRoute();
const classId = ref(null);
const error = ref(null);
const isLoading = ref(true);
const students = ref([]);
const course = ref(null);
const activeComponent = ref("");
const coursesStore = useCoursesStore();
const studentsStore = useStudentsStore();
const relationshipsStore = useRelationshipsStore();
classId.value = route.params.classId;

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

const toggleComponent = (component) => {
  activeComponent.value = activeComponent.value === component ? "" : component;
};
</script>
<template>
    <div class="min-h-screen  bg-opacity-10 py-0 px-0">
      <DashboardNavTeacher class="w-full" />
      
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- Animated Header -->
        <div class="text-center mb-8 animate-fade-in">
          <div class="inline-block bg-white rounded-full px-6 py-2 shadow-md mb-4">
            <h1 class="text-4xl font-bold text-[#00ADEC] tracking-tight">
              RESULTATS SOCIOGRAMA
            </h1>
          </div>
          <div class="h-1 w-32 bg-[#00ADEC] mx-auto rounded-full"></div>
        </div>
  
        <!-- Loading State -->
        <div 
          v-if="isLoading"
          class="bg-white rounded-3xl shadow-2xl p-10 flex flex-col items-center justify-center min-h-[400px] transform transition-all hover:scale-[1.01]"
        >
          <div 
            class="animate-spin rounded-full h-16 w-16 border-4 border-[#00ADEC] border-t-transparent mb-4"
          ></div>
          <p class="text-lg text-gray-600 font-medium tracking-wide">
            Carregant dades del Sociograma...
          </p>
        </div>
  
        <!-- Error State -->
        <div
          v-else-if="error"
          class="bg-red-50 border-l-6 border-red-500 p-8 rounded-3xl shadow-lg flex items-center"
        >
          <svg class="w-12 h-12 text-red-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-red-700 font-semibold text-xl">{{ error }}</p>
        </div>
  
        <!-- Main Content -->
        <div v-else class="space-y-6">
          <!-- Course Information -->
          <div class="bg-white rounded-3xl shadow-2xl p-8 transform transition-all hover:scale-[1.02]">
            <div class="grid md:grid-cols-2 gap-6">
              <div class="bg-[#00ADEC] bg-opacity-10 p-6 rounded-2xl border border-[#00ADEC] hover:shadow-lg transition">
                <h2 class="text-sm font-medium text-[#00ADEC] uppercase tracking-wider mb-2">
                  Curs
                </h2>
                <p class="text-3xl font-bold text-gray-900 flex items-center">
                  <span class="mr-3 text-[#00ADEC]">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                    </svg>
                  </span>
                  {{ course ? course.courseName : "" }}
                </p>
              </div>
              <div class="bg-[#00ADEC] bg-opacity-10 p-6 rounded-2xl border border-[#00ADEC] hover:shadow-lg transition">
                <h2 class="text-sm font-medium text-[#00ADEC] uppercase tracking-wider mb-2">
                  Divisió
                </h2>
                <p class="text-3xl font-bold text-gray-900 flex items-center">
                  <span class="mr-3 text-[#00ADEC]">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.768-.152-1.507-.43-2.192M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.768.152-1.507.43-2.192m0 0a5.002 5.002 0 019.14 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  </span>
                  {{ course && course.division ? course.division.name : "" }}
                </p>
              </div>
            </div>
          </div>
  
          <!-- Buttons to toggle components -->
          <div class="flex justify-center space-x-4">
            <button
              @click="toggleComponent('relations')"
              class="group relative px-6 py-3 bg-[#0080C0] text-white rounded-xl shadow-lg hover:bg-[#005A8C] transition-all"
            >
              <span class="relative z-10">Relacions</span>
            </button>
            <button
              @click="toggleComponent('roles')"
              class="group relative px-6 py-3 bg-[#009FD4] text-white rounded-xl shadow-lg hover:bg-[#006B8E] transition-all"
            >
              <span class="relative z-10">Rols</span>
            </button>
            <button
              @click="toggleComponent('skills')"
              class="group relative px-6 py-3 bg-[#00BFFF] text-white rounded-xl shadow-lg hover:bg-[#0080A2] transition-all"
            >
              <span class="relative z-10">Competencies</span>
            </button>
          </div>
  
          <!-- Component Display -->
          <div v-if="activeComponent === 'relations'" class="animate-fade-in">
            <Relations :relationships="filteredRelationships" />
          </div>
          <div v-else-if="activeComponent === 'roles'" class="animate-fade-in">
            <Roles />
          </div>
          <div v-else-if="activeComponent === 'skills'" class="animate-fade-in">
            <Skills />
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <style scoped>
  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }
  
  .animate-fade-in {
    animation: fadeIn 0.5s ease-out;
  }
  </style>


