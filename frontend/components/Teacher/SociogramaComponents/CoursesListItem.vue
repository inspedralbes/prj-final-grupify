<script setup>
import { onMounted } from "vue";
import { useRouter } from "vue-router";
import { EyeIcon } from "@heroicons/vue/24/outline";
import { useCoursesStore } from "@/stores/coursesStore";

const router = useRouter();
const coursesStore = useCoursesStore();

defineProps({
  course: {
    type: Object,
    required: true,
    default: () => ({}),
  },
});


const viewProfile = (course) => {
  if (!course) return;
  router.push({
    path: `/professor/sociograma/sociogramaProlife/${course.classId}`,
  });
};


const checkFormCompletion = async (course) => {
  console.log("Llamando a checkFormCompletion con el curso:", course);

  if (!course) {
    console.error("No se ha pasado un curso válido");
    return;
  }

  try {
    const response = await fetch(
      `http://localhost:8000/api/check-form-completion/${course.courseId}/${course.division.id}/3`
    );
    
    console.log("Respuesta de la API:", response);

    if (!response.ok) {
      throw new Error('Error al obtener los datos');
    }

    const data = await response.json();
    console.log("Datos de la respuesta:", data);

    // Si all_answered es true, asignamos sociograma_available a true
    if (data.all_answered) {
      course.sociograma_available = true;
    } else {
      course.sociograma_available = false;
    }

  } catch (error) {
    console.error("Error al verificar si el formulario ha sido contestado:", error);
    course.sociograma_available = false;
  }
};

// Ejecutamos `checkFormCompletion` para cada curso cuando el componente se monte
onMounted(() => {
  if (Array.isArray(coursesStore.courses)) {
    coursesStore.courses.forEach((course) => {
      checkFormCompletion(course);
    });
  }
});
</script>

<template>
  <tr class="border-b hover:bg-gray-50">
    <td class="py-4">{{ course.courseName }}</td>
    <td class="py-4">{{ course.division.name }}</td>
    <td class="py-4">
      <!-- Mostrará si el formulario ha sido contestado -->
      <span
        class="px-3 py-1 rounded-full text-white text-sm font-medium"
        :class="course.sociograma_available ? 'bg-green-500' : 'bg-red-500'"
      >
        {{ course.sociograma_available ? "Contestat" : "No contestat" }}
      </span>
    </td>
    <td class="py-4">
      <button class="p-1 hover:text-primary" @click="viewProfile(course)">
        <EyeIcon class="w-5 h-5" />
      </button>
    </td>
  </tr>
</template>
