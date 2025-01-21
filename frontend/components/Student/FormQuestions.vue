<script setup>
import { useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import Toast from "@/components/common/Toast.vue";

const route = useRoute();
const formId = route.params.id; // Obtenemos el ID del formulario desde la ruta
const questions = ref([]);
const responses = ref({}); // Objeto para almacenar las respuestas del usuario
const authStore = useAuthStore();
const user = authStore.user;
const userId = user.id;

// Control del toast
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("");

// Función para mostrar el toast
function triggerToast(message, type = "success") {
  toastMessage.value = message;
  toastType.value = type;
  showToast.value = true;
  setTimeout(() => (showToast.value = false), 1000);
}

// Cargar preguntas del formulario cuando el componente se monte
onMounted(() => {
  fetchFormWithQuestions();
});

// Función para hacer la solicitud Fetch al backend
async function fetchFormWithQuestions() {
  try {
    const response = await fetch(
      `http://localhost:8000/api/forms/${formId}/questions-and-answers`
    );

    if (!response.ok) {
      throw new Error("Formulario no encontrado");
    }

    const formData = await response.json();
    questions.value = formData;

    // Inicializar las respuestas para cada pregunta con un objeto que contenga "value" y "type"
    questions.value.forEach(question => {
      responses.value[question.id] = {
        value: Array.isArray(question.options) ? [] : "", // Para 'checkbox' y 'multiple', lo inicializamos como array vacío
        type: question.type,
      };
    });
  } catch (error) {
    console.error("Error al cargar las preguntas:", error);
    triggerToast("Error al cargar el formulario.", "error");
  }
}

// Función para manejar el envío de respuestas
async function submitResponses() {
  const formattedResponses = Object.keys(responses.value)
    .map(questionId => {
      const response = responses.value[questionId];

      // Convertir questionId a número si es una cadena
      const questionIdAsNumber = parseInt(questionId, 10);

      // Verificar que el valor de la respuesta no esté vacío para campos requeridos
      if (
        response.value === undefined ||
        response.value === null ||
        (Array.isArray(response.value) && response.value.length === 0)
      ) {
        return null; // Devolver null para esta respuesta si no se completó
      }

      // Verificar que el tipo de respuesta sea correcto
      let answer_type = response.type;
      if (answer_type === "text") {
        answer_type = "string";
      }

      return {
        question_id: questionIdAsNumber, // Asegúrate de enviar un número
        answer: response.value,
        answer_type,
      };
    })
    .filter(response => response !== null); // Filtrar las respuestas vacías

  try {
    const response = await fetch(
      `http://localhost:8000/api/forms/${formId}/submit-responses`,
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          accept: "application/json",
        },
        body: JSON.stringify({
          user_id: userId,
          responses: formattedResponses,
        }),
      }
    );

    if (!response.ok) {
      throw new Error("Error al enviar las respuestas");
    }

    triggerToast("Respuestas enviadas correctamente.", "success");
    setTimeout(() => (window.location.href = "/student/dashboard"), 500);
  } catch (error) {
    console.error("Error al enviar las respuestas:", error);
    triggerToast("Hubo un problema al enviar las respuestas.", "error");
  }
}
</script>

<template>
  <div class="space-y-6 p-6">
    <h2 class="text-2xl font-bold">Formulario: {{ formId }}</h2>

    <!-- Mostrar las preguntas del formulario -->
    <div
      v-for="(question, index) in questions"
      :key="question.id"
      class="space-y-4"
    >
      <div class="bg-white rounded-lg shadow p-4">
        <h3 class="text-lg font-semibold">{{ question.title }}</h3>
        <p class="text-sm text-gray-500">{{ question.placeholder }}</p>

        <!-- Manejo de diferentes tipos de preguntas -->
        <div v-if="question.type === 'text'">
          <input
            v-model="responses[question.id].value"
            type="text"
            class="mt-2 p-2 border rounded w-full"
            :placeholder="question.placeholder"
          />
        </div>

        <div v-if="question.type === 'multiple'">
          <div v-for="option in question.options" :key="option.id" class="mt-2">
            <label class="flex items-center">
              <input
                v-model="responses[question.id].value"
                type="radio"
                :name="'question-' + question.id"
                :value="option.id"
                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-2 focus:ring-blue-500"
              />
              <span class="ml-2 text-gray-700">{{ option.text }}</span>
            </label>
          </div>
        </div>

        <div v-if="question.type === 'checkbox'">
          <div v-for="option in question.options" :key="option.id" class="mt-2">
            <label class="flex items-center">
              <input
                v-model="responses[question.id].value"
                type="checkbox"
                :name="'question-' + question.id"
                :value="option.id"
                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-2 focus:ring-blue-500"
              />
              <span class="ml-2 text-gray-700">{{ option.text }}</span>
            </label>
          </div>
        </div>

        <div v-if="question.type === 'number'">
          <input
            v-model="responses[question.id].value"
            type="number"
            class="mt-2 p-2 border rounded w-full"
            :placeholder="question.placeholder"
          />
        </div>
      </div>
    </div>

    <div>
      <button
        class="mt-4 bg-primary text-white px-4 py-2 rounded"
        @click="submitResponses"
      >
        Enviar respuestas
      </button>
    </div>

    <!-- Toast -->
    <Toast v-if="showToast" :message="toastMessage" :type="toastType" />
  </div>
</template>

<style scoped>
/* Estilos adicionales si es necesario */
</style>
