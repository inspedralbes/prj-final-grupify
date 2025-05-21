<script setup>
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { EyeIcon } from "@heroicons/vue/24/outline";
import { useCoursesStore } from "@/stores/coursesStore";
import { useSociogramStore } from "@/stores/sociogramStore";

const router = useRouter();
const sociogramStore = useSociogramStore();
const isLoading = ref(false);
const chatStore = useChatStore();


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
    path: `/orientador/sociograma/sociogramaProlife/${course.classId}`,
  });
};

// Función para analizar con IA
const analyzeWithAI = async (course) => {
  try {
    // Mostrar un indicador de carga mientras se procesa la solicitud
    isLoading.value = true;

    // Realizar la solicitud a la API
    const response = await fetch("https://api.basebrutt.com/api/sociogram/responses", {
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

    // Procesar los datos recibidos
    const data = await response.json();

    console.log(data);

    sociogramStore.clearResponses(); // limpio lo datos que ya estaban en el store 

    // Guardar los datos formateados en sociogramStore
    sociogramStore.setResponses(data);
    sociogramStore.setCurrentCourseAndDivision(course.courseName, course.courseId, course.division.name, course.division.id);
    chatStore.createNewChat({ name: "Sociograma" });

    chatStore.addMessage(chatStore.currentChatId, {
      type: "system",
      content: `Benvingut a la sessió de sociograma. \n 
    CURSO: ${sociogramStore.currentCourse.courseName} \n
    DIVISION: ${sociogramStore.currentDivision.divisionName}
    \n Com puc ajudar-te?\n
      Pot fer preguntes sobre:\n
      - Sobre les preferències individuals\n
      - Sobre les relacions entre alumnes\n
      - Sobre el sociograma en general\n
      I més...
    `,
      timestamp: new Date().toISOString(),
    });

    // Redirigir al chat solo después de que todo esté listo
    router.push({
      path: "/orientador/assistent",
    });
  } catch (error) {
    console.error("Error durante el análisis con IA:", error);
    alert("Ocurrió un error al analizar los datos. Por favor, inténtalo de nuevo.");
  } finally {
    // Ocultar el indicador de carga
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
      <button @click="analyzeWithAI(course)">Analitzar IA</button>
    </td>
  </tr>
</template>