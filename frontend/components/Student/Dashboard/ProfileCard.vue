<script setup>
const authStore = useAuthStore();
const userData = ref({
  name: "",
  last_name: "",
  email: "",
  image: "",
  course_id: "",  // Ahora usarás `course_id`
  division_id: "", // Ahora usarás `division_id`
});

onMounted(() => {
  const user = authStore.user;

  if (user) {
    userData.value = {
      name: user.name,
      last_name: user.last_name,
      email: user.email,
      image: user.image,
      course_id: user.course_id || "Sin Curso",  // Asignamos `course_id` directamente
      division_id: user.division_id || "Sin División",  // Asignamos `division_id` directamente
    };
  }
});
</script>

<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex flex-col items-center">
      <div class="relative group">
        <!-- Avatar -->
        <img
          :src="userData.image"
          alt="Avatar"
          class="w-24 h-24 rounded-full object-cover mb-4"
        />
        <div
          class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
          @click="triggerFileInput"
        >
          <img
            :src="userData.image"
            alt="Avatar"
            class="w-24 h-24 rounded-full object-cover mb-4"
          />
          <div
            class="absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer"
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
      </div>
      <input
        ref="fileInput"
        type="file"
        class="hidden"
        accept="image/*"
        @change="handleFileChange"
      />
      <!-- Información del estudiante -->
      <h2 class="text-2xl font-bold text-gray-800">
        {{ userData.name }} {{ userData.last_name }}
      </h2>
      <p class="text-gray-500 text-sm">{{ userData.email }}</p>
      <p class="text-gray-500 text-sm">
      <!--  Curso: {{ userData.course_id }} | División: {{ userData.division_id }}-->
      </p>
    </div>
  </div>
</template>
