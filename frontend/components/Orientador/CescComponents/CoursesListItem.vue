<script setup>
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { EyeIcon } from "@heroicons/vue/24/outline";
import { useCoursesStore } from "@/stores/coursesStore";
import { useCescStore } from "@/stores/cescStore";

const router = useRouter();
const cescStore = useCescStore();
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
    path: `/orientador/cesc/cescProfile/${course.classId}`,
  });
};

// Función para analizar con IA
const analyzeWithAI = async (course) => {
  try {
    isLoading.value = true;

    const response = await fetch("https://api.grupify.cat/api/cesc/responses", {
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
      throw new Error("Error en obtenir les respostes del sociograma.");
    }

    const data = await response.json();
    console.log(data);

    cescStore.clearResponses();
    cescStore.setResponses(data);
    cescStore.setCurrentCourseAndDivision(course.courseName, course.courseId, course.division.name, course.division.id);

    chatStore.createNewChat({ name: "Cesc" });

    chatStore.addMessage(chatStore.currentChatId, {
      type: "system",
      content: `Benvingut a la sessió de CESC. \n 
    CURS: ${cescStore.currentCourse.courseName} \n
    DIVISIÓ: ${cescStore.currentDivision.divisionName}
    \n Com puc ajudar-te?\n
      Pots fer preguntes sobre:\n
      - Preferències individuals: Pots preguntar per les respostes d'un alumne específic.\n
      - Relacions entre alumnes: Puc analitzar les respostes per identificar qui es porta bé, qui no, etc.\n
      - Comportaments a l'aula: Puc oferir informació sobre qui difon rumors, ajuda als altres, empeny, etc.\n
      - Grups socials: Puc ajudar-te a identificar els grups d'amics i amigues dins de l'aula.\n
      I més...
    `,
      timestamp: new Date().toISOString(),
    });


    router.push({
      path: "/orientador/assistent",
    });
  } catch (error) {
    console.error("Error durant l'anàlisi amb IA:", error);
    alert("S'ha produït un error en analitzar les dades. Si us plau, torna-ho a provar.");
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
  </tr>
</template>
