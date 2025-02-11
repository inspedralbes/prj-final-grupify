<script setup>
import { useRouter, useRoute } from "vue-router";

const menuItems = [
  {
    title: "Gestió de Alumnes",
    route: "/professor/alumnes",
    icon: "M12 4.354a4 4 0 110 5.292V4.354zM15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z",
  },
  {
    title: "Grups",
    route: "/professor/grups",
    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
  },
  {
    title: "Formularis",
    route: "/professor/formularis",
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01",
  },
  {
    title: "Sociograma",
    route: "/professor/sociograma/SociogramaView",
    icon: "M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z",
  },
  {
    title: "Chat IA",
    route: "/professor/assistent",
    icon: "M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z",
  },
  {
    title: "Notificacions", 
    route: "/professor/notificacions",
    icon: "M12 22a2 2 0 002-2H10a2 2 0 002 2zm6-6V9a6 6 0 10-12 0v7a2 2 0 01-2 2h16a2 2 0 01-2-2zm-6-13a4 4 0 014 4h-8a4 4 0 014-4z",
  },
];

const router = useRouter();
const route = useRoute();

const goHome = () => {
  router.push("/professor/dashboard");
};

const isActiveRoute = itemRoute => route.path === itemRoute;
</script>

<template>
  <nav class="bg-[#00ADEC] shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Botón de inicio -->
        <button
          @click="goHome"
          class="flex items-center text-white hover:text-blue-100 transition-colors duration-200"
        >
          <svg
            class="w-6 h-6 mr-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
            />
          </svg>
          <span class="text-lg font-semibold">Inici</span>
        </button>

        <!-- Menú de navegación -->
        <div class="flex space-x-1">
          <NuxtLink
            v-for="item in menuItems"
            :key="item.title"
            :to="item.route"
            class="group px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-all duration-200"
            :class="[
              isActiveRoute(item.route)
                ? 'bg-blue-700 text-white'
                : 'text-blue-50 hover:bg-blue-600 hover:text-white',
            ]"
          >
            <svg
              class="w-5 h-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                :d="item.icon"
              />
            </svg>
            <span>{{ item.title }}</span>
          </NuxtLink>
        </div>
      </div>
    </div>
  </nav>
</template>
