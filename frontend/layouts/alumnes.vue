<!-- layouts/alumnes.vue -->
<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <aside
      :class="[
        'bg-primary text-white transition-all duration-300',
        isMenuOpen ? 'w-64' : 'w-16',
        'lg:w-64'
      ]"
    >
      <!-- Encabezado del menú -->
      <div class="flex items-center justify-between p-4 border-b border-white/20">
        <h1 v-if="isMenuOpen" class="text-xl font-bold">Panell Estudiant</h1>
        <!-- Botón de menú móvil -->
        <button
          @click="toggleMenu"
          class="lg:hidden text-white p-2 rounded-md hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
        >
          <svg
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              v-if="!isMenuOpen"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- Navegación -->
      <nav :class="[isMenuOpen ? 'mt-4' : 'mt-2']">
        <NuxtLink
          v-for="item in menuItems"
          :key="item.path"
          :to="item.path"
          class="flex items-center px-4 py-3 text-white transition-colors duration-200 hover:bg-white/10 hover:text-gray-200"
          :class="{ 'bg-primary/80': $route.path === item.path }"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3" />
          <span v-if="isMenuOpen">{{ item.name }}</span>
        </NuxtLink>
      </nav>
    </aside>

    <!-- Contenido principal -->
    <main class="flex-1 overflow-y-auto">
      <div class="p-8">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
  HomeIcon,
  UserGroupIcon,
  DocumentTextIcon,
  PowerIcon,
} from '@heroicons/vue/24/outline'

// Elementos del menú
const menuItems = [
  { name: 'Dashboard', path: '/alumne/dashboard', icon: HomeIcon },
  { name: 'Grups', path: '/alumne/grups', icon: UserGroupIcon },
  { name: 'Formularis', path: '/alumne/formularis', icon: DocumentTextIcon },
  { name: 'Log out', path: '/alumne/tancar-sessio', icon: PowerIcon }
]

const isMenuOpen = ref(true)

const handleResize = () => {
  if (window.innerWidth < 1024) {
    isMenuOpen.value = false
  } else {
    isMenuOpen.value = true
  }
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
})

// Alternar el estado del menú
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}
</script>

<style scoped>
</style>
