<script setup>
import { useRouter } from "vue-router";

// Función para navegar al perfil del curso
const router = useRouter();
const viewProfile = (courseId) => {
  if (!courseId) return;
  router.push(`/professor/courseProfile/${courseId}`); //cambiarlo!!!
};

// Definir las props del componente
defineProps({
  course: {
    type: Object,
    required: true,
    default: () => ({}),
  },
});
</script>

<template>
  <tr class="border-b hover:bg-gray-50">
    <td class="py-4">{{ course.courseName }}</td>
    <td class="py-4">{{ course.division.name }}</td>
    <td class="py-4">
      <!-- Mostrará si el curso a respondido el cuestionario -->
      <span
        class="px-3 py-1 rounded-full text-white text-sm font-medium"
        :class="course.active ? 'bg-green-500' : 'bg-red-500'"
      >
        {{ course.active ? "Contestat" : "No contestat" }}
      </span>
    </td>
    <td class="py-4">
      <button class="p-1 hover:text-primary" @click="viewProfile(course.courseId)">
        <EyeIcon class="w-5 h-5" />
      </button>
    </td>
  </tr>
</template>
