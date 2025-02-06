<script setup>
import { onMounted, ref, computed } from "vue";
import { useRoute } from "vue-router";
import { useCoursesStore } from "~/stores/coursesStore";
import { useRelationshipsStore } from "~/stores/relationships";
import Relations from "@/components/Teacher/SociogramaComponents/Relations.vue";
import Roles from "@/components/Teacher/SociogramaComponents/Roles.vue";
import Skills from "@/components/Teacher/SociogramaComponents/Skills.vue";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";
import SociogramStatus from "@/components/Teacher/SociogramaComponents/SociogramStatus.vue";
import { useStudentsStore } from "~/stores/studentsStore";

// Existing setup code remains the same
const route = useRoute();
const classId = ref(null);
const error = ref(null);
const isLoading = ref(true);
const students = ref([]);
const course = ref(null);
const activeComponent = ref("");
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
const toggleComponent = component => {
  activeComponent.value = activeComponent.value === component ? "" : component;
};
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-sky-50 to-white">
    <DashboardNavTeacher class="w-full" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Enhanced Animated Header -->
      <div
        class="text-center mb-12 transform transition-all duration-500 hover:scale-105"
      >
        <div
          class="inline-block bg-white rounded-2xl px-8 py-4 shadow-xl mb-6 bg-opacity-90 backdrop-blur-sm"
        >
          <h1
            class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-[#00ADEC] to-[#0080C0]"
          >
            RESULTATS SOCIOGRAMA
          </h1>
        </div>
        <div
          class="h-1.5 w-48 bg-gradient-to-r from-[#00ADEC] to-[#0080C0] mx-auto rounded-full"
        ></div>
      </div>

      <!-- Enhanced Loading State -->
      <div
        v-if="isLoading"
        class="bg-white rounded-3xl shadow-2xl p-12 flex flex-col items-center justify-center min-h-[400px] transform transition-all duration-500 hover:shadow-3xl backdrop-blur-sm bg-opacity-90"
      >
        <div class="relative w-20 h-20">
          <div
            class="absolute inset-0 rounded-full border-4 border-[#00ADEC] border-t-transparent animate-spin"
          ></div>
          <div
            class="absolute inset-2 rounded-full border-4 border-[#0080C0] border-t-transparent animate-spin-slow"
          ></div>
        </div>
        <p
          class="mt-6 text-xl text-gray-600 font-medium tracking-wide animate-pulse"
        >
          Carregant dades del Sociograma...
        </p>
      </div>

      <!-- Enhanced Error State -->
      <div
        v-else-if="error"
        class="bg-red-50 border-l-8 border-red-500 p-8 rounded-3xl shadow-xl flex items-center transform transition-all duration-500 hover:scale-105"
      >
        <svg
          class="w-16 h-16 text-red-500 mr-6"
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
        <p class="text-red-700 font-bold text-2xl">{{ error }}</p>
      </div>

      <!-- Enhanced Main Content -->
      <div v-else class="space-y-8">
        <!-- Enhanced Course Information with Status -->
        <div
          class="bg-white rounded-3xl shadow-xl p-8 transform transition-all duration-500 hover:shadow-2xl backdrop-blur-sm bg-opacity-90"
        >
          <div class="flex flex-col space-y-8">
            <!-- Status Bar -->
            <div class="w-full flex justify-center mb-4">
              <SociogramStatus
                v-if="course && course.sociograma_available !== undefined"
                :course="course"
              />
            </div>

            <!-- Course Info Grid -->
            <div class="grid md:grid-cols-2 gap-8">
              <div
                class="bg-gradient-to-br from-[#00ADEC]/10 to-[#0080C0]/10 p-8 rounded-2xl border-2 border-[#00ADEC]/20 hover:shadow-xl transition-all duration-300 group"
              >
                <h2
                  class="text-sm font-semibold text-[#00ADEC] uppercase tracking-wider mb-3 group-hover:translate-x-1 transition-transform"
                >
                  Curs
                </h2>
                <p
                  class="text-4xl font-bold text-gray-800 flex items-center group-hover:translate-x-2 transition-transform"
                >
                  <span
                    class="mr-4 text-[#00ADEC] transform transition-transform group-hover:rotate-12"
                  >
                    <svg
                      class="w-10 h-10"
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
                  </span>
                  {{ course ? course.courseName : "" }}
                </p>
              </div>

              <div
                class="bg-gradient-to-br from-[#00ADEC]/10 to-[#0080C0]/10 p-8 rounded-2xl border-2 border-[#00ADEC]/20 hover:shadow-xl transition-all duration-300 group"
              >
                <h2
                  class="text-sm font-semibold text-[#00ADEC] uppercase tracking-wider mb-3 group-hover:translate-x-1 transition-transform"
                >
                  Divisió
                </h2>
                <p
                  class="text-4xl font-bold text-gray-800 flex items-center group-hover:translate-x-2 transition-transform"
                >
                  <span
                    class="mr-4 text-[#00ADEC] transform transition-transform group-hover:rotate-12"
                  >
                    <svg
                      class="w-10 h-10"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.768-.152-1.507-.43-2.192M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.768.152-1.507.43-2.192m0 0a5.002 5.002 0 019.14 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                      />
                    </svg>
                  </span>
                  {{ course && course.division ? course.division.name : "" }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Enhanced Toggle Buttons -->
        <div class="flex justify-center space-x-6">
          <button
            v-for="(label, key) in {
              relations: 'Relacions',
              roles: 'Rols',
              skills: 'Competències',
            }"
            :key="key"
            @click="toggleComponent(key)"
            :class="[
              'group relative px-8 py-4 rounded-xl shadow-lg transition-all duration-300 transform hover:-translate-y-1',
              'text-white font-semibold text-lg',
              'overflow-hidden',
              activeComponent === key
                ? 'ring-4 ring-offset-2 ring-[#00ADEC]/50'
                : '',
              key === 'relations'
                ? 'bg-[#0080C0]'
                : key === 'roles'
                  ? 'bg-[#009FD4]'
                  : 'bg-[#00BFFF]',
            ]"
          >
            <span class="relative z-10">{{ label }}</span>
            <div
              class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity"
            ></div>
          </button>
        </div>

        <!-- Enhanced Component Display -->
        <div class="mt-8">
          <TransitionGroup name="fade">
            <div
              v-if="activeComponent === 'relations'"
              key="relations"
              class="animate-fade-in"
            >
              <div v-if="activeComponent === 'relations'">
                <Relations :relationships="filteredRelationships" />
              </div>
            </div>
            <div
              v-else-if="activeComponent === 'roles'"
              key="roles"
              class="animate-fade-in"
            >
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
.animate-spin-slow {
  animation: spin 2s linear infinite;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.6s ease-out;
}

.fade-enter-active,
.fade-leave-active {
  transition:
    opacity 0.5s ease,
    transform 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
