<template>
  <div 
    v-if="modelValue"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto"
    :class="{ 'fade-enter': animation && isVisible, 'fade-leave': animation && !isVisible }"
    @click.self="closeModal"
  >
    <div 
      class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-auto"
      :class="{ 'slide-up-enter': animation && isVisible, 'slide-down-leave': animation && !isVisible }"
    >
      <div class="bg-gradient-to-r from-red-600 via-orange-500 to-yellow-500 text-white p-4 rounded-t-xl flex justify-between items-center">
        <div class="flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <h3 class="text-xl font-bold">Alumnes en Risc - Alerta</h3>
        </div>
        <button @click="closeModal" class="text-white hover:text-gray-200 focus:outline-none transition-transform transform hover:scale-110">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="p-6 overflow-y-auto max-h-[70vh]">
        <p class="text-gray-600 mb-6">S'han identificat els següents alumnes amb factors de risc que requereixen atenció immediata:</p>
        
        <!-- Categorias de riesgo con animaciones -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="risk-category-count bg-red-100 text-red-800 rounded-lg p-4 shadow-sm border border-red-200 flex flex-col items-center justify-center transition-all hover:shadow-md">
            <span class="text-3xl font-bold animate-bounce-delayed">{{ aggressiveStudents.length }}</span>
            <span class="text-sm font-medium">Agressius</span>
          </div>
          <div class="risk-category-count bg-yellow-100 text-yellow-800 rounded-lg p-4 shadow-sm border border-yellow-200 flex flex-col items-center justify-center transition-all hover:shadow-md">
            <span class="text-3xl font-bold animate-bounce-delayed-2">{{ victimStudents.length }}</span>
            <span class="text-sm font-medium">Víctimes</span>
          </div>
          <div class="risk-category-count bg-blue-100 text-blue-800 rounded-lg p-4 shadow-sm border border-blue-200 flex flex-col items-center justify-center transition-all hover:shadow-md">
            <span class="text-3xl font-bold animate-bounce-delayed-3">{{ rejectedStudents.length }}</span>
            <span class="text-sm font-medium">Rebutjats</span>
          </div>
        </div>
        
        <!-- Alumnos agresivos -->
        <div v-if="aggressiveStudents.length > 0" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg risk-category-appear">
          <h4 class="text-red-800 font-semibold flex items-center mb-3">
            <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
            Alumnes amb comportament agressiu
          </h4>
          <div class="space-y-2">
            <div 
              v-for="(student, index) in aggressiveStudents" 
              :key="student.fullName" 
              class="bg-white p-3 rounded-lg shadow-sm border border-red-100 hover:shadow-md transition-all risk-student-appear"
              :style="{ animationDelay: `${index * 0.2}s` }"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-500 flex items-center justify-center text-white font-bold">
                    {{ student.fullName.substring(0, 2).toUpperCase() }}
                  </div>
                  <div class="ml-3">
                    <div class="font-semibold text-red-800">{{ student.fullName }}</div>
                    <div class="text-xs text-red-600">Puntuació: <span class="font-bold">{{ student.tags['Agressiu'] }}</span></div>
                  </div>
                </div>
                <div class="flex space-x-2">
                  <div class="text-xs font-medium px-2 py-1 rounded-full bg-red-200 text-red-800 animate-pulse">
                    Alt Risc
                  </div>
                  <button 
                    @click="$emit('view-student', student)"
                    class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded flex items-center transition-colors"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Veure
                  </button>
                </div>
              </div>
              <div class="mt-2 text-xs text-gray-600">
                <strong>Recomanació:</strong> Programar sessió individual i avaluar necessitat d'intervenció.
              </div>
            </div>
          </div>
        </div>
        
        <!-- Alumnos víctimas -->
        <div v-if="victimStudents.length > 0" class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg risk-category-appear" style="animation-delay: 0.2s">
          <h4 class="text-yellow-800 font-semibold flex items-center mb-3">
            <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
            Alumnes identificats com a víctimes
          </h4>
          <div class="space-y-2">
            <div 
              v-for="(student, index) in victimStudents" 
              :key="student.fullName" 
              class="bg-white p-3 rounded-lg shadow-sm border border-yellow-100 hover:shadow-md transition-all risk-student-appear"
              :style="{ animationDelay: `${0.2 + index * 0.2}s` }"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 rounded-full bg-yellow-500 flex items-center justify-center text-white font-bold">
                    {{ student.fullName.substring(0, 2).toUpperCase() }}
                  </div>
                  <div class="ml-3">
                    <div class="font-semibold text-yellow-800">{{ student.fullName }}</div>
                    <div class="text-xs text-yellow-600">Puntuació: <span class="font-bold">{{ student.tags['Víctima'] }}</span></div>
                  </div>
                </div>
                <div class="flex space-x-2">
                  <div class="text-xs font-medium px-2 py-1 rounded-full bg-yellow-200 text-yellow-800 animate-pulse">
                    Necessita Suport
                  </div>
                  <button 
                    @click="$emit('view-student', student)"
                    class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded flex items-center transition-colors"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Veure
                  </button>
                </div>
              </div>
              <div class="mt-2 text-xs text-gray-600">
                <strong>Recomanació:</strong> Oferir suport emocional i estratègies d'afrontament.
              </div>
            </div>
          </div>
        </div>
        
        <!-- Alumnos rechazados -->
        <div v-if="rejectedStudents.length > 0" class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg risk-category-appear" style="animation-delay: 0.4s">
          <h4 class="text-blue-800 font-semibold flex items-center mb-3">
            <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
            Alumnes amb rebuig social
          </h4>
          <div class="space-y-2">
            <div 
              v-for="(student, index) in rejectedStudents" 
              :key="student.fullName" 
              class="bg-white p-3 rounded-lg shadow-sm border border-blue-100 hover:shadow-md transition-all risk-student-appear"
              :style="{ animationDelay: `${0.4 + index * 0.2}s` }"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold">
                    {{ student.fullName.substring(0, 2).toUpperCase() }}
                  </div>
                  <div class="ml-3">
                    <div class="font-semibold text-blue-800">{{ student.fullName }}</div>
                    <div class="text-xs text-blue-600">Puntuació: <span class="font-bold">{{ student.tags['Rebutjat'] }}</span></div>
                  </div>
                </div>
                <div class="flex space-x-2">
                  <div class="text-xs font-medium px-2 py-1 rounded-full bg-blue-200 text-blue-800 animate-pulse">
                    Integració Social
                  </div>
                  <button 
                    @click="$emit('view-student', student)"
                    class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-1 rounded flex items-center transition-colors"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Veure
                  </button>
                </div>
              </div>
              <div class="mt-2 text-xs text-gray-600">
                <strong>Recomanació:</strong> Activitats d'integració i desenvolupament d'habilitats socials.
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-6 p-4 bg-indigo-50 border border-indigo-200 rounded-lg risk-category-appear" style="animation-delay: 0.6s">
          <h4 class="text-indigo-800 font-semibold mb-2 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Passes a seguir:
          </h4>
          <ul class="list-disc pl-5 text-indigo-700 space-y-1">
            <li class="pulsing-item">Programar sessions individuals amb aquests alumnes.</li>
            <li class="pulsing-item" style="animation-delay: 0.2s">Implementar activitats d'integració i cohesió grupal.</li>
            <li class="pulsing-item" style="animation-delay: 0.4s">Fer un seguiment més detallat del seu comportament.</li>
            <li class="pulsing-item" style="animation-delay: 0.6s">Consultar amb l'equip d'orientació per a més recursos.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, onMounted, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  aggressiveStudents: {
    type: Array,
    default: () => []
  },
  victimStudents: {
    type: Array,
    default: () => []
  },
  rejectedStudents: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['update:modelValue', 'create-report', 'view-student']);

const animation = ref(true);
const isVisible = ref(true);

const closeModal = () => {
  isVisible.value = false;
  // Pequeño retraso para permitir que la animación de salida se complete
  setTimeout(() => {
    emit('update:modelValue', false);
    // Resetear para la próxima vez que se abra
    setTimeout(() => {
      isVisible.value = true;
    }, 100);
  }, 300);
};

// Observar cambios en modelValue para reiniciar animaciones
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    isVisible.value = true;
  }
});

onMounted(() => {
  // Añadir clase al body para prevenir el scroll cuando el modal esté abierto
  if (props.modelValue) {
    document.body.classList.add('overflow-hidden');
  }
  
  // Remover la clase cuando el componente se desmonte
  return () => {
    document.body.classList.remove('overflow-hidden');
  };
});
</script>

<style scoped>
.fade-enter {
  animation: fadeIn 0.3s ease-out;
}

.fade-leave {
  animation: fadeOut 0.3s ease-in forwards;
}

.slide-up-enter {
  animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-down-leave {
  animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.risk-category-appear {
  animation: fadeInUp 0.6s ease-out both;
}

.risk-student-appear {
  animation: fadeInRight 0.5s ease-out both;
}

.animate-bounce-delayed {
  animation: bounce 1s infinite;
}

.animate-bounce-delayed-2 {
  animation: bounce 1s infinite;
  animation-delay: 0.2s;
}

.animate-bounce-delayed-3 {
  animation: bounce 1s infinite;
  animation-delay: 0.4s;
}

.pulsing-item {
  animation: pulseText 2s infinite;
}

@keyframes fadeIn {
  0% { opacity: 0; }
  100% { opacity: 1; }
}

@keyframes fadeOut {
  0% { opacity: 1; }
  100% { opacity: 0; }
}

@keyframes slideUp {
  0% { 
    opacity: 0;
    transform: translateY(30px);
  }
  100% { 
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideDown {
  0% { 
    opacity: 1;
    transform: translateY(0);
  }
  100% { 
    opacity: 0;
    transform: translateY(30px);
  }
}

@keyframes fadeInUp {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInRight {
  0% {
    opacity: 0;
    transform: translateX(-20px);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(-10%);
    animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
  }
  50% {
    transform: translateY(0);
    animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
  }
}

@keyframes pulseText {
  0% {
    opacity: 0.8;
  }
  50% {
    opacity: 1;
  }
  100% {
    opacity: 0.8;
  }
}
</style>
