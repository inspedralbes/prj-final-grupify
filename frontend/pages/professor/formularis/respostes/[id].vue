<script setup>
const route = useRoute();
const formId = route.params.id;
const isLoading = ref(true);
const students = ref([]);

onMounted(() => {
  getUsersByForm(formId);
});

const goToFormularis = () => {
  navigateTo("/professor/formularis");
};

const getUsersByForm = async formId => {
  try {
    const response = await fetch(
      `http://localhost:8000/api/forms/${formId}/users`,
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
      }
    );

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
  <div class="p-6">
    <div class="relative flex items-center mb-6">
      <button
        class="absolute left-0 flex items-center space-x-1 text-gray-700 hover:text-gray-900"
        @click="goToFormularis"
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

      <h1 class="flex-grow text-center text-2xl font-bold">Alumnos</h1>
    </div>
    <div v-if="isLoading" class="text-center p-8">Carregant estudiants...</div>
    <div v-else>
      <TeacherStudentAnsweredList :students="students" :formId="formId" />
    </div>
  </div>
</template>
