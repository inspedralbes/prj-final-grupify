<script setup>
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";
import { EyeIcon } from '@heroicons/vue/24/outline';

const route = useRoute();
const navigate = useRouter();
const formId = route.params.id;
const isLoading = ref(true);
const students = ref([]);
const isMobile = ref(false);

// Detectar si es mobile
onMounted(() => {
  getUsersByForm(formId);
  checkIfMobile();
  window.addEventListener('resize', checkIfMobile);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkIfMobile);
});

const checkIfMobile = () => {
  isMobile.value = window.innerWidth < 768;
};

const getUsersByForm = async (formId) => {
  try {
    const apiUrl = formId === "3"
      ? `https://api.grupify.cat/api/forms/${formId}/responded-users`
      : `https://api.grupify.cat/api/forms/${formId}/users`;
    
    const response = await fetch(apiUrl, {
      method: "GET",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
      },
    });

    if (!response.ok) {
      throw new Error("Error al obtener usuarios.");
    }

    const data = await response.json();
    students.value = data;
  } catch (error) {
    console.error("Error:", error);
  } finally {
    isLoading.value = false;
  }
};

const handleReturn = () => {
  navigateTo('/professor/formularis');
};
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <DashboardNavTeacher class="shadow-md z-10" />
    
    <div class="flex-1 container mx-auto px-4 py-8">
      <div class="bg-white rounded-xl shadow-lg p-4 md:p-6">
        <!-- Header mejorado para mobile -->
        <div class="flex items-center justify-between mb-6 md:mb-8">
          <NuxtLink
            to="/professor/formularis"
            class="flex items-center text-gray-700 hover:text-gray-900 text-sm md:text-base"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4 md:h-5 md:w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M15 19l-7-7 7-7"
              />
            </svg>
            <span class="ml-1 md:ml-2">Tornar</span>
          </NuxtLink>
          
          <h1 class="text-xl md:text-3xl font-bold text-gray-800 text-center truncate">
            Llistat d'Estudiants
          </h1>
          
          <div class="w-8"></div>
        </div>

        <!-- Loading state -->
        <div v-if="isLoading" class="flex justify-center items-center h-48 md:h-64">
          <div class="animate-spin rounded-full h-10 w-10 md:h-12 md:w-12 border-b-2 border-blue-500"></div>
        </div>

        <!-- Empty state -->
        <div
          v-else-if="students.length === 0"
          class="text-center text-gray-500 py-6 md:py-8"
        >
          No hi ha estudiants en aquest formulari
        </div>

        <!-- Students list -->
        <div v-else class="space-y-4">
          <TeacherStudentAnsweredList
            :students="students"
            :formId="formId"
            class="w-full"
          >
            <template #action-button v-if="isMobile">
              <EyeIcon class="h-5 w-5 text-blue-600" />
            </template>
          </TeacherStudentAnsweredList>
        </div>
      </div>
    </div>
  </div>
</template>