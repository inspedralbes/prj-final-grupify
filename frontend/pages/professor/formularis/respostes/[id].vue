<script setup>
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue'
const route = useRoute();
const formId = route.params.id;
const isLoading = ref(true);
const students = ref([]);


onMounted(() => {
  getUsersByForm(formId);
});


const getUsersByForm = async (formId) => {
  try {
    const apiUrl =
      formId === "3"
        ? `http://localhost:8000/api/forms/${formId}/responded-users`
        : `http://localhost:8000/api/forms/${formId}/users`;

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
</script>


<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <DashboardNavTeacher class="shadow-md z-10" />
   
    <div class="flex-1 container mx-auto px-4 py-8">
      <div class=" relative bg-white rounded-xl shadow-lg p-6">
        <button
          class="absolute left-0 ml-6 flex items-center space-x-1 text-gray-700 hover:text-gray-900"
          @click="navigateTo(`/professor/formularis`)"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
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
          <span>Tornar</span>
        </button>
        <div class="flex items-center mb-8">
          <h1 class="text-3xl font-bold text-gray-800 flex-grow text-center">
            Llistat d'Estudiants
          </h1>
        </div>


        <div v-if="isLoading" class="flex justify-center items-center h-64">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
        </div>
       
        <div v-else-if="students.length === 0" class="text-center text-gray-500 py-8">
          No hi ha estudiants en aquest formulari
        </div>
       
        <div v-else>
          <TeacherStudentAnsweredList
            :students="students"
            :formId="formId"
            class="w-full"
          />
        </div>
      </div>
    </div>
  </div>
</template>