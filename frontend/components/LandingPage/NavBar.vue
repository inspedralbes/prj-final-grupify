<template>
  <header class="sticky top-0 z-50 bg-white shadow-md">
    <nav
      class="flex items-center justify-between p-6 lg:px-8"
      aria-label="Global"
    >
      <div class="flex lg:flex-1">
        <NuxtLink to="/" class="-m-1.5 p-1.5">
          <span class="sr-only">Grupify</span>
          <img class="h-8 w-auto" src="/img/icono.png" alt="Logo" />
        </NuxtLink>
      </div>
      <!-- Mobile menu button -->
      <div class="flex lg:hidden">
        <button
          type="button"
          class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500"
          @click="toggleMenu"
        >
          <span class="sr-only">Menú principal</span>
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
      <!-- Desktop navigation -->
      <div class="hidden lg:flex lg:flex-1 lg:justify-end space-x-4">
        <button
          class="text-sm font-semibold text-gray-900 hover:text-indigo-600"
          @click="$emit('show-contact')"
        >
          Contacte
        </button>
        <NuxtLink
          to="/login"
          class="text-sm font-semibold text-gray-900 hover:text-indigo-600"
          >Començar</NuxtLink
        >
      </div>
    </nav>

    <!-- Mobile menu -->
    <div v-show="isMenuOpen" class="lg:hidden">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <button
          class="block w-full text-left px-3 py-2 text-base font-medium text-gray-900 hover:bg-gray-100 hover:text-indigo-600"
          @click="handleContactClick"
        >
          Contacte
        </button>
        <NuxtLink
          to="/login"
          class="block px-3 py-2 text-base font-medium text-gray-900 hover:bg-gray-100 hover:text-indigo-600"
          @click="isMenuOpen = false"
        >
          Començar
        </NuxtLink>
      </div>
    </div>
  </header>
</template>

<script setup>
const isMenuOpen = ref(false);

// Métodos
const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const handleContactClick = () => {
  isMenuOpen.value = false;
  // Emitir evento para abrir el formulario de contacto
  const emit = defineEmits(["show-contact"]);
  emit("show-contact");
};
</script>
