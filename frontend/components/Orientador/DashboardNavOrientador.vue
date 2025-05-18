<script setup>
import { useRouter, useRoute } from "vue-router";
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useAuthStore } from '~/stores/authStore';

const authStore = useAuthStore();
const openDropdowns = ref({});
const isMobileMenuOpen = ref(false);
const isMobileFormsOpen = ref(false);

// Configuración de los elementos de menú para el orientador
const menuItemsConfig = [
  {
    title: "Gestió de Alumnes",
    route: "/orientador/llista-alumnes",
    icon: "M12 4.354a4 4 0 110 5.292V4.354zM15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z",
  },
  {
    type: "dropdown",
    id: "formularis",
    title: "Formularis",
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01",
    items: [
      {
        title: "Cesc",
        route: "/orientador/cesc/CescView",
        icon: "M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z",
      },
      {
        title: "Sociograma",
        route: "/orientador/sociograma/SociogramaView",
        icon: "M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z",
      }
    ]
  },
  {
    type: "dropdown",
    id: "grafiques", 
    title: "Gràfiques",
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01",
    items: [
      {
        title: "CESC",
        route: "/orientador/graficas",
        icon: "M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Z",
      },
      {
        title: "Sociograma",
        route:"/orientador/sociograma/comparative",
        icon: "M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z",
      }
    ]
  },
  {
    title: "Notificacions", 
    route: "/orientador/notificacions",
    icon: "M12 22a2 2 0 002-2H10a2 2 0 002 2zm6-6V9a6 6 0 10-12 0v7a2 2 0 01-2 2h16a2 2 0 01-2-2zm-6-13a4 4 0 014 4h-8a4 4 0 014-4z",
  },
];

// Usamos directamente los elementos configurados para el orientador
const menuItems = computed(() => menuItemsConfig);

const router = useRouter();
const route = useRoute();

const goHome = () => {
  router.push("/orientador/dashboard");
};

const isActiveRoute = itemRoute => route.path === itemRoute;

// Nueva función para manejar cada desplegable individualmente
const toggleDropdown = (dropdownId, event) => {
  // Cerrar todos los otros desplegables
  for (const key in openDropdowns.value) {
    if (key !== dropdownId) {
      openDropdowns.value[key] = false;
    }
  }
  // Toggle el desplegable actual
  openDropdowns.value[dropdownId] = !openDropdowns.value[dropdownId];
};

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
  isMobileFormsOpen.value = false; // Cerrar el menú desplegable al abrir/cerrar el menú móvil
};

// Cerrar todos los desplegables cuando se hace clic fuera
const closeDropdowns = (event) => {
  if (!event.target.closest('.dropdown-container')) {
    for (const key in openDropdowns.value) {
      openDropdowns.value[key] = false;
    }
  }
};

onMounted(() => {
  document.addEventListener('click', closeDropdowns);
  // Inicializar el authStore para asegurar que los datos del usuario estén cargados
  authStore.initialize();
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdowns);
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
                @click="toggleDropdown(item.id, $event)"
                class="group px-3 py-2 rounded-md text-sm font-medium flex items-center space-x-2 transition-all duration-200 text-blue-50 hover:bg-blue-600 hover:text-white"
                :class="{ 'bg-blue-700': openDropdowns[item.id] }"
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
                  :class="{ 'transform rotate-180': openDropdowns[item.id] }"
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
                v-show="openDropdowns[item.id]"
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
                @click="toggleDropdown(item.id, $event)"
                class="w-full flex justify-between items-center px-3 py-2 rounded-md text-base font-medium text-white hover:bg-blue-600"
                :class="{ 'bg-blue-700': openDropdowns[item.id] }"
              >
                <span>{{ item.title }}</span>
                <svg
                  class="w-4 h-4 ml-1 transition-transform duration-200"
                  :class="{ 'transform rotate-180': openDropdowns[item.id] }"
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
                v-show="openDropdowns[item.id]"
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