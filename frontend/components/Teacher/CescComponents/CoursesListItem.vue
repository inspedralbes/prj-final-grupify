<script setup>
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { EyeIcon } from "@heroicons/vue/24/outline";
import { useCoursesStore } from "@/stores/coursesStore";
import { useCescStore } from "@/stores/cescStore";

const router = useRouter();
const cescStore = useCescStore();
const isLoading = ref(false);

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
    path: `/professor/cesc/cescProfile/${course.classId}`,
  });
};

// Función para analizar con IA
const analyzeWithAI = async (course) => {
  try {
    isLoading.value = true;
    
    const response = await fetch("http://localhost:8000/api/cesc/responses", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        course_id: course.courseId,
        division_id: course.division.id,
      }),
    });

    if (!response.ok) {
      throw new Error("Error al obtener las respuestas del sociograma.");
    }

    const data = await response.json();
    console.log(data);

    cescStore.clearResponses(); 
    cescStore.setResponses(data);
    cescStore.setCurrentCourseAndDivision(course.courseName, course.courseId, course.division.name, course.division.id);
    
    router.push({
      path: "/professor/assistent",
    });
  } catch (error) {
    console.error("Error durante el análisis con IA:", error);
    alert("Ocurrió un error al analizar los datos. Por favor, inténtalo de nuevo.");
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <tr class="border-b hover:bg-gray-50">
    <td class="py-4">{{ course.courseName }}</td>
    <td class="py-4">{{ course.division.name }}</td>
    <td>
      <button class="p-1 hover:text-primary" @click="viewProfile(course)">
        <EyeIcon class="w-5 h-5" />
      </button>
    </td>
    <td>
      <button @click="analyzeWithAI(course)">Analizar IA</button>
    </td>
  </tr>
</template>
