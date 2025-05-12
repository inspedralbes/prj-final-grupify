<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "~/stores/authStore";

const userData = ref(null);
const router = useRouter();
const authStore = useAuthStore();

// Menú con solo los elementos a los que tiene acceso el orientador
const menuItems = [
  {
    title: "Alumnes",
    icon: "M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z",
    route: "/professor/llista-alumnes",
  },
  {
    title: "Sociograma",
    icon: "M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z",
    route: "/professor/sociograma/SociogramaView",
  },
  {
    title: "Cesc",
    icon: "M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z",
    route: "/professor/cesc/CescView",
  },
  {
    title: "Gràfiques",
    icon: "M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z",
    route: "/orientador/graficas",
  },
  {
    title: "Formularis",
    icon: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
    route: "/professor/formularis",
  },
  {
    title: "Notificacions",
    icon: "M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5",
    route: "/professor/notificacions",
  },
];

// Cargar el usuario desde el localStorage cuando el componente se monta
onMounted(() => {
  // Verificar que sea un orientador
  if (!authStore.isOrientador) {
    router.push('/login');
    return;
  }
  
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    userData.value = JSON.parse(storedUser);
    console.log("User data loaded:", userData.value);
  }
});

// Función de logout
const logout = () => {
  authStore.logout();
};
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navbar envolvente azul -->
    <div class="bg-[#00ADEC] text-white p-6">
      <div class="max-w-7xl mx-auto flex justify-between items-center">
        <!-- Título -->
        <h1 class="text-3xl font-bold">Panell Orientador</h1>

        <!-- Botón de logout -->
        <button
          class="flex items-center gap-2 px-4 py-2 hover:bg-white/10 rounded-lg transition-colors duration-200 underline hover:no-underline"
          @click="logout"
        >
          <svg
            class="w-6 h-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
            />
          </svg>
          <span>Tancar sessió</span>
        </button>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto mt-6">
      <!-- Welcome Section -->
      <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
        <div v-if="userData" class="text-center">
          <div class="flex flex-col items-center">
            <!-- Avatar -->
            <img
              :src="userData.image || 'https://via.placeholder.com/150'"
              alt="Avatar"
              class="w-24 h-24 rounded-full object-cover mb-4"
            />
            <!-- Información del orientador -->
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              Benvingut, {{ userData.name }} {{ userData.last_name }}!
            </h1>
            <p class="text-gray-600">{{ userData.email }}</p>

            <!-- Materias que imparte (si aplica) -->
            <div v-if="userData.subjects && userData.subjects.length > 0" class="mt-4 flex items-center">
              <div class="flex flex-wrap gap-4">
                <span
                  v-for="subject in userData.subjects"
                  :key="subject.id"
                  class="text-[#00ADEC] font-normal text-xl"
                >
                  {{ subject.name }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Menu Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 m-0">
        <NuxtLink
          v-for="item in menuItems"
          :key="item.title"
          :to="item.route"
          class="bg-white p-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1"
        >
          <div class="flex flex-col items-center space-y-4">
            <svg
              class="w-12 h-12 text-primary"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="item.icon"
              />
            </svg>
            <span class="text-lg font-medium text-gray-800">{{
              item.title
            }}</span>
          </div>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.text-primary {
  color: #00adec;
}
</style>
