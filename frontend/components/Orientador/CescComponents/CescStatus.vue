<script setup>
import { onMounted } from "vue";
import { useCoursesStore } from "@/stores/coursesStore";

const coursesStore = useCoursesStore();
defineProps({
  course: {
    type: Object,
    required: true,
    default: () => ({}),
  },
});

const checkFormCompletion = async (course) => {
  if (!course) {
    console.error("No s'ha passat un curs vàlid");
    return;
  }

  try {
    const response = await fetch(
      `https://api.grupify.cat/api/check-form-completion/${course.courseId}/${course.division.id}/2`
    );

    if (!response.ok) {
      throw new Error("Error en obtenir les dades");
    }

    const data = await response.json();

    if (data.all_answered) {
      course.cesc_available = true;
    } else {
      course.cesc_available = false;
    }
  } catch (error) {
    console.error("Error en verificar si el formulari ha estat contestat:", error);
    course.cesc_available = false;
  }
};

onMounted(() => {
  if (Array.isArray(coursesStore.courses)) {
    coursesStore.courses.forEach((course) => {
      if (!course.hasOwnProperty('cesc_available')) {
        course.cesc_available = false; // Inicializar cesc_available si no existe
      }
      checkFormCompletion(course);
    });
  }
});
</script>

<template>
  <div class="flex items-center justify-center">
    <div 
      class="relative group transform transition-all duration-300 hover:scale-105"
      :class="[
        'px-6 py-3 rounded-xl shadow-lg',
        'backdrop-blur-sm',
        course.cesc_available ? 'bg-gradient-to-r from-emerald-400 to-emerald-500' : 'bg-gradient-to-r from-rose-400 to-rose-500'
      ]"
    >
      <div class="absolute inset-0 rounded-xl bg-white opacity-0 group-hover:opacity-10 transition-opacity duration-300"></div>
      
      <div class="flex items-center space-x-3">
        <!-- Animated Icon -->
        <svg 
          v-if="course.cesc_available"
          class="w-6 h-6 text-white transform transition-transform group-hover:rotate-12" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <svg 
          v-else
          class="w-6 h-6 text-white transform transition-transform group-hover:rotate-12" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>

        <!-- Status Text -->
        <span 
          class="text-white font-semibold tracking-wide text-sm"
          :class="{'animate-pulse': course.cesc_available === undefined}"
        >
          {{ course.cesc_available !== undefined 
            ? (course.cesc_available ? "Contestat" : "No contestat") 
            : "Carregant..." 
          }}
        </span>
      </div>

      <!-- Animated Dots (for loading state) -->
      <div 
        v-if="course.cesc_available === undefined"
        class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 flex space-x-1"
      >
        <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0s"></div>
        <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
        <div class="w-2 h-2 bg-white rounded-full animate-bounce" style="animation-delay: 0.4s"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-bounce {
  animation: bounce 1s infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-4px);
  }
}
</style>
