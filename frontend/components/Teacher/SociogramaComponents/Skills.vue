<script setup>
import { ref, onMounted, defineProps, computed } from "vue";

const props = defineProps({
  filteredSkills: {
    type: Array,
    required: true,
  },
});

// Calcular el promedio de habilidades para la barra de progreso
const getSkillPercentage = value => (value / 5) * 100;

// Obtener el color basado en el valor
const getSkillColor = value => {
  if (value >= 4) return "from-emerald-400 to-emerald-500";
  if (value >= 3) return "from-blue-400 to-blue-500";
  if (value >= 2) return "from-yellow-400 to-yellow-500";
  return "from-rose-400 to-rose-500";
};
</script>

<template>
  <div class="space-y-8 mt-8">
    <!-- Skills Content -->
    <div
      v-if="props.filteredSkills.length > 0"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
    >
      <div
        v-for="(skill, index) in props.filteredSkills"
        :key="index"
        class="bg-white rounded-2xl shadow-xl p-6 transform transition-all duration-300 hover:shadow-2xl hover:scale-105 backdrop-blur-sm bg-opacity-90"
      >
        <!-- Student Name -->
        <div class="flex items-center mb-6">
          <div
            class="w-12 h-12 bg-gradient-to-br from-[#00ADEC] to-[#0080C0] rounded-full flex items-center justify-center text-white font-bold text-sm"
          >
            {{ skill.peer_name[0] }}{{ skill.peer_last_name[0] }}
          </div>
          <div class="ml-4">
            <h2 class="text-sm font-semibold text-gray-800">
              {{ skill.peer_name }} {{ skill.peer_last_name }}
            </h2>
          </div>
        </div>

        <!-- Skills Bars -->
        <div class="space-y-4">
          <!-- Creatividad -->
          <div class="group">
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-gray-700">Creativitat</span>
              <span class="text-sm font-medium text-gray-600"
                >{{ skill.creatividad }}/5</span
              >
            </div>
            <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r transition-all duration-300 group-hover:scale-x-105"
                :class="getSkillColor(skill.creatividad)"
                :style="{ width: `${getSkillPercentage(skill.creatividad)}%` }"
              ></div>
            </div>
          </div>

          <!-- Liderazgo -->
          <div class="group">
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-gray-700">Lideratge</span>
              <span class="text-sm font-medium text-gray-600"
                >{{ skill.liderazgo }}/5</span
              >
            </div>
            <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r transition-all duration-300 group-hover:scale-x-105"
                :class="getSkillColor(skill.liderazgo)"
                :style="{ width: `${getSkillPercentage(skill.liderazgo)}%` }"
              ></div>
            </div>
          </div>

          <!-- Organización -->
          <div class="group">
            <div class="flex justify-between mb-1">
              <span class="text-sm font-medium text-gray-700"
                >Organització</span
              >
              <span class="text-sm font-medium text-gray-600"
                >{{ skill.organizacion }}/5</span
              >
            </div>
            <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full bg-gradient-to-r transition-all duration-300 group-hover:scale-x-105"
                :class="getSkillColor(skill.organizacion)"
                :style="{ width: `${getSkillPercentage(skill.organizacion)}%` }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
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
        No hi ha dades de competències disponibles
      </p>
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
