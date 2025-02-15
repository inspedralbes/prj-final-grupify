<script setup>
import { useRouter, useRoute } from "vue-router";
import { ref, onMounted, onUnmounted } from 'vue';

const isFormsOpen = ref(false);
const isMobileMenuOpen = ref(false);
const isMobileFormsOpen = ref(false);
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
    type: "dropdown",
    title: "Formularis",
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01",
    items: [
      {
        title: "General",
        route: "/professor/formularis",
        icon: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
      },
      {
        title: "Sociograma",
        route: "/professor/sociograma/SociogramaView",
        icon: "M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z",
      },
      {
        title: "Cesc",
        route: "/professor/cesc/CescView",
        icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2",
      }
    ]
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

const toggleForms = () => {
  isFormsOpen.value = !isFormsOpen.value;
};


const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
  isMobileFormsOpen.value = false; // Cerrar el menú desplegable al abrir/cerrar el menú móvil
};
const toggleMobileForms = () => {
  isMobileFormsOpen.value = !isMobileFormsOpen.value;
};

// Cerrar el menú cuando se hace clic fuera
const closeDropdown = (event) => {
  if (!event.target.closest('.dropdown-container')) {
    isFormsOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});
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

        <!-- Menú de navegación para pantallas grandes -->
        <div class="hidden md:flex space-x-1">
          <template v-for="item in menuItems" :key="item.title">
            <!-- Menú normal -->
            <NuxtLink
              v-if="!item.type"
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

            <!-- Menú desplegable -->
            <div v-else-if="item.type === 'dropdown'" class="relative dropdown-container">
              <button
                @click="toggleForms"
                class="group px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-all duration-200 text-blue-50 hover:bg-blue-600 hover:text-white"
                :class="{ 'bg-blue-700': isFormsOpen }"
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
                <svg
                  class="w-4 h-4 ml-1 transition-transform duration-200"
                  :class="{ 'transform rotate-180': isFormsOpen }"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </button>

              <!-- Menú desplegable mejorado -->
              <div
                v-show="isFormsOpen"
                class="absolute z-10 right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5 transform origin-top transition-all duration-200"
              >
                <div class="py-2 divide-y divide-gray-100">
                  <NuxtLink
                    v-for="subItem in item.items"
                    :key="subItem.title"
                    :to="subItem.route"
                    class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 transition-colors duration-200 first:rounded-t-lg last:rounded-b-lg"
                    :class="{ 'bg-blue-50 text-blue-700': isActiveRoute(subItem.route) }"
                  >
                    <svg
                      class="w-5 h-5 mr-3 text-gray-400 group-hover:text-blue-500"
                      :class="{ 'text-blue-500': isActiveRoute(subItem.route) }"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        :d="subItem.icon"
                      />
                    </svg>
                    <span class="flex-1">{{ subItem.title }}</span>
                    
                   
                  </NuxtLink>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- Botón de menú móvil -->
        <div class="md:hidden flex items-center">
          <button
            @click="toggleMobileMenu"
            class="text-white hover:text-blue-100 focus:outline-none"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16m-7 6h7"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Menú móvil -->
      <div v-show="isMobileMenuOpen" class="md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
          <template v-for="item in menuItems" :key="item.title">
            <!-- Menú normal -->
            <NuxtLink
              v-if="!item.type"
              :to="item.route"
              class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600"
              :class="{ 'bg-blue-700': isActiveRoute(item.route) }"
            >
              {{ item.title }}
            </NuxtLink>

            <!-- Menú desplegable -->
            <div v-else-if="item.type === 'dropdown'" class="relative">
              <button
                @click="toggleMobileForms"
                class="w-full flex justify-between items-center px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600"
                :class="{ 'bg-blue-700': isMobileFormsOpen }"
              >
                <span>{{ item.title }}</span>
                <svg
                  class="w-4 h-4 ml-1 transition-transform duration-200"
                  :class="{ 'transform rotate-180': isMobileFormsOpen }"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </button>

              <!-- Menú desplegable mejorado -->
              <div
                v-show="isMobileFormsOpen"
                class="pl-4"
              >
                <NuxtLink
                  v-for="subItem in item.items"
                  :key="subItem.title"
                  :to="subItem.route"
                  class="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600"
                  :class="{ 'bg-blue-700': isActiveRoute(subItem.route) }"
                >
                  {{ subItem.title }}
                </NuxtLink>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.dropdown-container {
  isolation: isolate;
}
</style>