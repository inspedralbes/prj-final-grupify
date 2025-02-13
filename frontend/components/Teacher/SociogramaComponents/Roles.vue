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
  <div class="space-y-8 mt-8">
      <div
      v-show="props.filteredRoles.length > 0"
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
              class="w-12 h-12 bg-gradient-to-br from-[#00ADEC] to-[#0080C0] rounded-full flex items-center justify-center text-white font-bold text-sm"
            >
              {{ role.peer_name[0] }}{{ role.peer_last_name[0] }}
            </div>
            <div class="ml-4">
              <h2 class="text-sm font-semibold text-gray-800">
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
