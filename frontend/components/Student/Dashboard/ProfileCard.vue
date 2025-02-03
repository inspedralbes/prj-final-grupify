<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex flex-col items-center">
      <div class="relative group">
        <img
          :src="userData.image"
          alt="Avatar"
          class="w-24 h-24 rounded-full object-cover mb-4"
        />
        <div
          class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
          @click="triggerFileInput"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-8 w-8 text-white"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
            />
          </svg>
        </div>
      </div>
      <input
        ref="fileInput"
        type="file"
        class="hidden"
        accept="image/*"
        @change="handleFileChange"
      />
      <h2 class="text-2xl font-bold text-gray-800">
        {{ userData.name }} {{ userData.last_name }}
      </h2>
      <p class="text-gray-500 text-sm">{{ userData.email }}</p>
      <p class="text-gray-500 text-sm">
        Curs: {{ userData.course_name }} {{ userData.division_name }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '~/stores/auth';

const authStore = useAuthStore();
const userData = ref({
  name: "",
  last_name: "",
  email: "",
  image: "",
  course_name: "",
  division_name: "",
});

// Datos de cursos y divisiones (simulados o obtenidos de una API)
const courses = [
  { id: 1, name: "1 ESO" },
  { id: 2, name: "2 ESO" },
  { id: 3, name: "3 ESO" },
  { id: 4, name: "4 ESO" },
  { id: 5, name: "BATXILLERAT" },
];

const divisions = [
  { id: 1, division: "1" },
  { id: 2, division: "2" },
  { id: 3, division: "A" },
  { id: 4, division: "B" },
  { id: 5, division: "C" },
  { id: 6, division: "D" },
  { id: 7, division: "E" },
];

// Función para obtener el nombre del curso o división basado en el ID
const getNameById = (id, data) => {
  const item = data.find((item) => item.id === id);
  return item ? item.name || item.division : "Sense dades";
};

onMounted(() => {
  const user = authStore.user;

  if (user) {
    userData.value = {
      name: user.name,
      last_name: user.last_name,
      email: user.email,
      image: user.image,
      course_name: getNameById(user.course_id, courses),
      division_name: getNameById(user.division_id, divisions),
    };
  }
});
</script>