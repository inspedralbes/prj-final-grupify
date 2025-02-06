<script setup>
import { ref, defineProps } from "vue";

const props = defineProps({
  filteredRoles: {
    type: Array,
    required: true,
  },
});

// Función para obtener el porcentaje
const getSkillPercentage = value => (value / 5) * 100;
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div
      class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl p-8 border border-gray-100"
    >
      <div
        v-if="props.filteredRoles.length > 0"
        class="grid gap-4 md:grid-cols-2 lg:grid-cols-3"
      >
        <div
          v-for="(role, index) in props.filteredRoles"
          :key="index"
          class="bg-white rounded-2xl shadow-xl p-6 transform transition-all duration-300 hover:shadow-2xl hover:scale-105 backdrop-blur-sm bg-opacity-90"
        >
          <!-- Nombre del alumno -->
          <div class="flex items-center mb-6">
            <div
              class="w-12 h-12 bg-gradient-to-br from-[#00ADEC] to-[#0080C0] rounded-full flex items-center justify-center text-white font-bold text-xl"
            >
              {{ role.peer_name[0] }}{{ role.peer_last_name[0] }}
            </div>
            <div class="ml-4">
              <h2 class="text-xl font-semibold text-gray-800">
                {{ role.peer_name }} {{ role.peer_last_name }}
              </h2>
            </div>
          </div>

          <!-- Barra de Popularitat (siempre verde) -->
          <div class="group">
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-gray-700">Popularitat</span>
              <span class="text-sm font-medium text-gray-600"
                >{{ role.popularitat }}/5</span
              >
            </div>
            <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 transition-all duration-300 group-hover:scale-x-105"
                :style="{ width: `${getSkillPercentage(role.popularitat)}%` }"
              ></div>
            </div>
          </div>

          <!-- Barra de Aïllament (siempre roja) -->
          <div class="group">
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-gray-700">Aïllament</span>
              <span class="text-sm font-medium text-gray-600"
                >{{ role.aïllament }}/5</span
              >
            </div>
            <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r from-rose-400 to-rose-500 transition-all duration-300 group-hover:scale-x-105"
                :style="{ width: `${getSkillPercentage(role.aïllament)}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-else
        class="bg-white rounded-3xl shadow-xl p-12 text-center transform transition-all duration-500 hover:shadow-2xl backdrop-blur-sm bg-opacity-90"
      >
        <svg
          class="w-20 h-20 mx-auto text-gray-400 mb-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <p class="text-xl text-gray-600 font-medium">
          No hi ha dades de rols disponibles
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Animations for hover effects */
@keyframes pulse {
  0%,
  100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.hover-pulse:hover {
  animation: pulse 1s infinite;
}
</style>
