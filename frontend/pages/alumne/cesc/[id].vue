<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
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
      `http://localhost:8000/api/forms/${formId}/questions-and-answers`
    );
    if (!response.ok) throw new Error("Error al cargar las preguntas");
    questions.value = await response.json();
  } catch (error) {
    console.error("Error al cargar las preguntas:", error);
    triggerToast("Error al cargar las preguntas.", "error");
  }
}

// Función para cargar usuarios
async function fetchUsers() {
  try {
    const response = await fetch("http://localhost:8000/api/get-students", {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    if (!response.ok) throw new Error("Error al cargar los usuarios");
    userNames.value = await response.json();
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
    // Formatear las respuestas, añadiendo un tipo de respuesta
    const formattedResponses = Object.keys(responses.value).map(questionId => {
      const answer = responses.value[questionId];
      return {
        question_id: parseInt(questionId),
        answer,
        answer_type: determineAnswerType(answer), // Llamamos a una función para determinar el tipo de respuesta
      };
    });

    const response = await fetch(
      `http://localhost:8000/api/forms/${formId}/submit-responses`,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          user_id: userId,
          responses: formattedResponses,
        }),
      }
    );

    if (!response.ok) throw new Error("Error al enviar respuestas");

    triggerToast("Respuestas enviadas correctamente.", "success");
    setTimeout(() => (window.location.href = "/alumne/dashboard"), 1000);
  } catch (error) {
    console.error("Error al enviar las respuestas:", error);
    triggerToast("Error al enviar las respuestas.", "error");
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
    <div
      v-if="currentQuestionIndex < questions.length"
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
        Siguiente pregunta
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
      <h3 class="text-xl font-bold mb-4">¡Quiz completat!</h3>
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
