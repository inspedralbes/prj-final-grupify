<script setup>
const userData = ref(null);

// Definimos los items del menú
const menuItems = [
  {
    title: "Gestió de Alumnes",
    icon: "M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z",
    route: "/professor/alumnes",
  },
  {
    title: "Grups",
    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
    route: "/professor/grups",
  },
  {
    title: "Formularis",
    icon: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
    route: "/professor/formularis",
  },
  {
    title: "Sociograma",
    icon: "M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-2.83-.48-5.08-2.73-5.56-5.56H5v-2h1.44c.48-2.83 2.73-5.08 5.56-5.56V5h2v1.44c2.83.48 5.08 2.73 5.56 5.56H19v2h-1.44c-.48 2.83-2.73 5.08-5.56 5.56V19h-2v-1.07zM12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4z",
    route: "/professor/sociograma",
  },
  {
    title: "Chat IA",
    icon: "M8.5 2a1 1 0 000 2h2.086a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2h1.5z",
    route: "/professor/assistent",
  },
];

// Cargar el usuario desde el localStorage cuando el componente se monta
onMounted(() => {
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    userData.value = JSON.parse(storedUser);
  }
});

// Función de logout
const logout = () => {
  localStorage.removeItem("user");
  navigateTo("/professor/tancar-sessio");
};
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Navbar envolvente azul -->
    <div class="bg-[#00ADEC] text-white p-6">
      <div class="max-w-7xl mx-auto flex justify-between items-center">
        <!-- Título -->
        <h1 class="text-3xl font-bold">Panell Professor</h1>

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
          <span>Logout</span>
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
            <!-- Información del profesor -->
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
              Benvingut, {{ userData.name }} {{ userData.last_name }}!
            </h1>
            <p class="text-gray-600">{{ userData.email }}</p>
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
.dashboard-container {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.text-primary {
  color: #00adec;
}
</style>
