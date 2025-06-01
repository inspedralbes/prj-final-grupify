<template>
  <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-100">
    <nav class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16" aria-label="Global">
      <!-- Logo Section -->
      <div class="flex items-center">
        <NuxtLink to="/" class="flex items-center space-x-2 hover:opacity-80 transition-opacity">
          <img class="h-8 w-auto" src="/img/icono.png" alt="Logo" />
          <span class="font-bold text-xl bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
            Grupify
          </span>
        </NuxtLink>
      </div>

      <!-- Desktop Navigation - Solo mostrar si no estamos en /login -->
      <template v-if="!isLoginPage">
        <div class="hidden md:flex items-center space-x-1">
          <NuxtLink to="#features" class="nav-link">
            Funcionalitats
          </NuxtLink>
          <NuxtLink to="#education" class="nav-link">
            Graus
          </NuxtLink>
          <NuxtLink to="#about" class="nav-link">
            Qui som
          </NuxtLink>
        </div>

        <!-- Action Buttons - Solo mostrar si no estamos en /login -->
        <div class="hidden md:flex items-center space-x-4">
          <NuxtLink
            to="/login"
            class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-primary to-secondary rounded-lg hover:shadow-lg transition-all duration-200 transform hover:scale-105"
          >
            Iniciar sessió
          </NuxtLink>
        </div>

        <!-- Mobile menu button - Solo mostrar si no estamos en /login -->
        <div class="md:hidden">
          <button
            type="button"
            class="inline-flex items-center justify-center p-2 rounded-lg text-gray-700 hover:text-primary hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary transition-all"
            @click="toggleMenu"
          >
            <span class="sr-only">Menú principal</span>
            <Transition name="rotate">
              <svg v-if="!isMenuOpen" key="menu" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
              <svg v-else key="close" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </Transition>
          </button>
        </div>
      </template>
    </nav>

    <!-- Mobile menu - Solo mostrar si no estamos en /login -->
    <Transition name="slide">
      <div v-if="!isLoginPage && isMenuOpen" class="md:hidden border-t border-gray-100">
        <div class="px-4 py-2 space-y-1 bg-white">
          <NuxtLink
            to="#features"
            class="mobile-nav-link"
            @click="closeMenu"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v2M7 7h10" />
            </svg>
            Funcionalitats
          </NuxtLink>
          <NuxtLink
            to="#education"
            class="mobile-nav-link"
            @click="closeMenu"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.247 18 17.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            Graus
          </NuxtLink>
          <NuxtLink
            to="#about"
            class="mobile-nav-link"
            @click="closeMenu"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Qui som
          </NuxtLink>
          
          <div class="border-t border-gray-100 my-2"></div>

          <NuxtLink
            to="/login"
            class="mobile-nav-link bg-gradient-to-r from-primary/10 to-secondary/10 text-primary font-medium"
            @click="closeMenu"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Iniciar sessió
          </NuxtLink>
        </div>
      </div>
    </Transition>
  </header>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'

const isMenuOpen = ref(false)
const emit = defineEmits(['show-contact'])
const route = useRoute()

const isLoginPage = computed(() => {
  return route.path === '/login'
})

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
  isMenuOpen.value = false
}

const handleContactClick = () => {
  closeMenu()
  emit('show-contact')
}
</script>

<style scoped>
.nav-link {
  @apply px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary rounded-lg transition-colors duration-200;
}

.mobile-nav-link {
  @apply flex items-center space-x-3 w-full px-3 py-3 text-base font-medium text-gray-700 hover:text-primary hover:bg-gray-50 rounded-lg transition-all duration-200;
}

/* Transitions */
.rotate-enter-active,
.rotate-leave-active {
  transition: all 0.3s ease;
}

.rotate-enter-from,
.rotate-leave-to {
  transform: rotate(180deg);
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: all 0.3s ease-out;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

/* Glass effect for navbar */
@supports (backdrop-filter: blur(10px)) {
  header {
    background-color: rgba(255, 255, 255, 0.9);
  }
}
</style>
