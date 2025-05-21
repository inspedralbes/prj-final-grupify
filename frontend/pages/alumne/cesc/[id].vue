<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "~/stores/authStore";
import Toast from "@/components/common/Toast.vue";

const route = useRoute();
const formId = route.params.id;
// console.log(formId);
const questions = ref([]);
const userNames = ref([]);
const responses = ref({});
const currentQuestionIndex = ref(0);
const authStore = useAuthStore();
const userId = authStore.user.id;

// Control del toast
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("");

// Función para mostrar el toast
function triggerToast(message, type = "success") {
  toastMessage.value = message;
  toastType.value = type;
  showToast.value = true;
  setTimeout(() => (showToast.value = false), 3000);
}

// Pregunta actual
const currentQuestion = computed(
  () => questions.value[currentQuestionIndex.value]
);

// Función para cargar preguntas
async function fetchQuestions() {
  try {
    const response = await fetch(
      `https://api.basebrutt.com/api/forms/${formId}/questions-and-answers`
    );
    if (!response.ok) throw new Error("Error al cargar las preguntas");
    const formData = await response.json();
    // La API devuelve el objeto completo del formulario, pero necesitamos solo las preguntas
    questions.value = formData.questions || [];
    console.log("Preguntas cargadas:", questions.value);
  } catch (error) {
    console.error("Error al cargar las preguntas:", error);
    triggerToast("Error al cargar las preguntas.", "error");
  }
}

// Función para cargar usuarios
async function fetchUsers() {
  try {
    // Obtener el curso y división del usuario actual
    const courseId = authStore.user.course_id;
    const divisionId = authStore.user.division_id;

    // Filtrar para obtener solo estudiantes del mismo curso y división
    const response = await fetch(`https://api.basebrutt.com/api/get-students?course_id=${courseId}&division_id=${divisionId}`, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    if (!response.ok) throw new Error("Error al cargar los usuarios");
    const allStudents = await response.json();

    // Excluir al usuario actual de la lista
    userNames.value = allStudents.filter(student => student.id !== userId);
  } catch (error) {
    console.error("Error al cargar los usuarios:", error);
    triggerToast("Error al cargar los usuarios.", "error");
  }
}

// Función para manejar la selección de usuarios
function toggleSelection(userId) {
  if (!currentQuestion.value) return;

  const questionId = currentQuestion.value.id;
  if (!responses.value[questionId]) {
    responses.value[questionId] = [];
  }

  const currentSelections = responses.value[questionId];
  const userIndex = currentSelections.indexOf(userId);

  if (userIndex > -1) {
    currentSelections.splice(userIndex, 1);
  } else if (currentSelections.length < 3) {
    currentSelections.push(userId);
  }
}

// Función para ir a la siguiente pregunta
function goToNextQuestion() {
  if (currentQuestionIndex.value < questions.value.length - 1) {
    currentQuestionIndex.value++;
  }
}

// Función para enviar respuestas
async function submitResponses() {
  try {
    // Formatear las respuestas para el controlador CescRelationship
    // Crear un array de relaciones en el formato que espera el controlador
    const relationships = [];

    // Para cada pregunta respondida
    for (const questionId in responses.value) {
      // Obtener los IDs de los peers seleccionados
      const selectedPeerIds = responses.value[questionId];

      // Para cada peer seleccionado, crear una relación
      for (const peerId of selectedPeerIds) {
        // Determinar el tag_id basado en el ID de la pregunta
        // Mapeamos los IDs de las preguntas a los IDs de los tags según el seeder
        // POPULAR (tag_id: 1): Preguntas 3, 14
        // REBUTJAT (tag_id: 2): Pregunta 4
        // AGRESSIU (tag_id: 3): Preguntas 5, 7, 8, 10
        // PROSOCIAL (tag_id: 4): Preguntas 6, 9
        // VÍCTIMA (tag_id: 5): Preguntas 11, 12, 13

        // Convertir questionId a número para comparaciones
        const qId = parseInt(questionId);

        // Mapeo directo de question_id a tag_id
        const questionTagMap = {
          3: 1, 14: 1,  // POPULAR
          4: 2,         // REBUTJAT
          5: 3, 7: 3, 8: 3, 10: 3,  // AGRESSIU
          6: 4, 9: 4,   // PROSOCIAL
          11: 5, 12: 5, 13: 5  // VÍCTIMA
        };

        // Asignar tag_id según el mapeo, o usar 1 (Popular) como valor predeterminado
        let tagId = questionTagMap[qId] || 1;

        // Añadir logs para depuración
        console.log(`Pregunta ID: ${qId}, Tag ID asignado: ${tagId}`);

        // Añadir la relación al array
        relationships.push({
          peer_id: peerId,
          question_id: parseInt(questionId),
          tag_id: tagId
        });
      }
    }

    // Mostrar las relaciones que se van a enviar para depuración
    console.log("Relaciones a enviar:", JSON.stringify(relationships, null, 2));

    // Enviar los datos al endpoint correcto utilizando el formato adecuado
    const response = await fetch(
      `https://api.basebrutt.com/api/cesc-relationships`,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          user_id: userId,
          form_id: formId, // Enviar el ID del formulario que se está contestando
          relationships: relationships
        }),
      }
    );

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.error || "Error al enviar respuestas del CESC");
    }

    triggerToast("Respuestas del CESC enviadas correctamente.", "success");
    setTimeout(() => (window.location.href = "/alumne/dashboard"), 1000);
  } catch (error) {
    console.error("Error al enviar las respuestas del CESC:", error);
    triggerToast("Error al enviar las respuestas: " + error.message, "error");
  }
}

// Función para determinar el tipo de respuesta (por ejemplo, string, number, multiple)
function determineAnswerType(answer) {
  if (Array.isArray(answer)) {
    return "multiple"; // Suponemos que si es un array, es una respuesta múltiple
  } else if (typeof answer === "boolean") {
    return "boolean";
  } else if (typeof answer === "number") {
    return "number";
  } else if (typeof answer === "string") {
    return "string";
  } else {
    return "string"; // Default a string si no se puede determinar otro tipo
  }
}

// Cargar datos al montar el componente
onMounted(async () => {
  await Promise.all([fetchQuestions(), fetchUsers()]);
});
</script>

<template>
  <div class="p-6 space-y-6 max-w-2xl mx-auto">
    <!-- Loading indicator -->
    <div v-if="questions.length === 0" class="text-center py-10">
      <div class="mb-4">
        <svg class="animate-spin h-10 w-10 mx-auto text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>
      <p class="text-gray-600">Cargando formulario...</p>
    </div>

    <div
      v-else-if="questions.length > 0 && currentQuestionIndex < questions.length"
      class="bg-white rounded-lg shadow-lg p-6"
    >
      <h2 class="text-2xl font-bold mb-4">{{ currentQuestion?.title }}</h2>
      <p class="text-gray-600 mb-4">
        {{ currentQuestion?.placeholder || "Selecciona 3 personas" }}
      </p>

      <div class="grid grid-cols-3 gap-4">
        <button
          v-for="user in userNames"
          :key="user.id"
          :disabled="
            responses[currentQuestion?.id]?.length >= 3 &&
            !responses[currentQuestion?.id]?.includes(user.id)
          "
          class="p-3 rounded-lg transition-all duration-200"
          :class="{
            'bg-blue-500 text-white hover:bg-blue-600': responses[
              currentQuestion?.id
            ]?.includes(user.id),
            'bg-gray-100 hover:bg-gray-200': !responses[
              currentQuestion?.id
            ]?.includes(user.id),
            'opacity-50 cursor-not-allowed':
              responses[currentQuestion?.id]?.length >= 3 &&
              !responses[currentQuestion?.id]?.includes(user.id),
            'cursor-pointer':
              responses[currentQuestion?.id]?.length < 3 ||
              responses[currentQuestion?.id]?.includes(user.id),
          }"
          @click="toggleSelection(user.id)"
        >
          {{ user.name }}
        </button>
      </div>

      <div class="mt-4 text-sm text-gray-600">
        Seleccionados: {{ responses[currentQuestion?.id]?.length || 0 }}/3
      </div>

      <button
        v-if="
          currentQuestionIndex < questions.length - 1 &&
          responses[currentQuestion?.id]?.length === 3
        "
        class="mt-6 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
        @click="goToNextQuestion"
      >
        Següent pregunta
      </button>

      <button
        v-else-if="responses[currentQuestion?.id]?.length === 3"
        class="mt-6 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
        @click="submitResponses"
      >
        Finalizar
      </button>
    </div>

    <div v-else class="text-center">
      <h3 class="text-xl font-bold mb-4" v-if="currentQuestionIndex >= questions.length && questions.length > 0">¡Quiz completat!</h3>
      <h3 class="text-xl font-bold mb-4" v-else>No se han encontrado preguntas para este formulario.</h3>
    </div>

    <!-- Toast -->
    <Toast v-if="showToast" :message="toastMessage" :type="toastType" />
  </div>
</template>

<style scoped>
.transition-all {
  transition: all 0.2s ease-in-out;
}
</style>
