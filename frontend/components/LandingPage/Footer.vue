<template>
  <footer class="bg-gray-900 text-white">
    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="flex justify-center">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
          <!-- Company Info -->
          <div class="space-y-4">
            <div class="flex items-center justify-center space-x-2">
              <img src="/img/icono.png" alt="Grupify Logo" class="h-8 w-auto" />
              <span class="font-bold text-xl bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                Grupify
              </span>
            </div>
            <p class="text-gray-300 text-sm max-w-xs mx-auto text-left break-words">
              Automatitza la gestió educativa i inspira l'aprenentatge amb grups equilibrats i analítiques avançades.
            </p>
          </div>

          <!-- Quick Links -->
          <div class="text-center">
            <h3 class="font-semibold text-lg mb-4">Enllaços ràpids</h3>
            <ul class="space-y-2">
              <li>
                <a href="#features" class="text-gray-300 hover:text-white transition-colors text-sm">
                  Funcionalitats
                </a>
              </li>
              <li>
                <a href="#education" class="text-gray-300 hover:text-white transition-colors text-sm">
                  Graus educatius
                </a>
              </li>
              <li>
                <a href="/about" class="text-gray-300 hover:text-white transition-colors text-sm">
                  Qui som
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Copyright Bar -->
    <div class="border-t border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="text-center">
          <div class="text-gray-400 text-sm">
            © {{ currentYear }} Grupify. Tots els drets reservats.
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Custom scrollbar for lists on mobile */
@media (max-width: 768px) {
  .overflow-x-auto::-webkit-scrollbar {
    height: 4px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-track {
    background: #374151;
    border-radius: 2px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #4b5563;
    border-radius: 2px;
  }
}
</style>
