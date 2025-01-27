<script setup>
import { EyeIcon } from "@heroicons/vue/24/outline";
import { useRouter } from "vue-router";

const router = useRouter();

// Función que navega al perfil del curso
const viewProfile = (courseId) => {
  if (!courseId) return;
  router.push(`/professor/courseProfile/${courseId}`);
};

// Definir las props del componente
defineProps({
  course: {
    type: Object,
    required: true,
    default: () => ({}), // Valor por defecto es objeto vacío en caso de que no llegue nada.
  },
});
</script>

<template>
  <template v-if="course?.divisions?.length"> <!-- Verifica si existen divisiones -->
    <tr v-for="division in course.divisions" :key="division.id" class="border-b hover:bg-gray-50">
      <td class="py-4">
        <div class="flex items-center space-x-3">
          <!-- Mostrar nombre del curso (lo repetimos por cada división) -->
          <span>{{ course.name }}</span>
        </div>
      </td>
      <td class="py-4">{{ division.name }}</td>
      <td class="py-4">
        <span
          class="px-3 py-1 rounded-full text-white text-sm font-medium"
          :class="course.active ? 'bg-green-500' : 'bg-red-500'"
        >
          {{ course.active ? "Contestat" : "No contestat" }}
        </span>
      </td>
      <td class="py-4">
        <div class="flex space-x-2">
          <button
            class="p-1 hover:text-primary"
            @click.stop="viewProfile(course.id)" 
          >
            <EyeIcon class="w-5 h-5" />
          </button>
        </div>
      </td>
    </tr>
  </template>
  <template v-else>
    <!-- Si no hay divisiones, mostrar un mensaje opcional -->
    <tr>
      <td colspan="4" class="text-center text-gray-500 py-4">No divisiones disponibles.</td>
    </tr>
  </template>
</template>
