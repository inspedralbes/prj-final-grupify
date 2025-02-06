<script setup>
import Toast from "@/components/common/Toast.vue";

const route = useRoute();
const formId = route.params.id; // Obtener el ID del formulario desde la ruta
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

// Función para obtener preguntas desde el backend
async function fetchFormWithQuestions() {
  try {
    const response = await fetch(
      `http://localhost:8000/api/forms/${formId}/questions-and-answers`
    );

    if (!response.ok) {
      throw new Error("Formulario no encontrado");
    }

    const formData = await response.json();
    
    console.log("Datos recibidos de la API:", formData); // Debug

    questions.value = []; // Limpiar preguntas antes de asignarlas
    questions.value = formData;

    // Inicializar respuestas
    responses.value = {};
    questions.value.forEach(question => {
      responses.value[question.id] = {
        value: question.type === "checkbox" ? [] : "", // Para checkboxes, array vacío
        type: question.type,
      };
    });
  } catch (error) {
    console.error("Error al cargar las preguntas:", error);
    triggerToast("Error al cargar el formulario.", "error");
  }
}

// Función para enviar respuestas al backend
async function submitResponses() {
  const formattedResponses = Object.keys(responses.value)
    .map(questionId => {
      const response = responses.value[questionId];

      const questionIdAsNumber = parseInt(questionId, 10);

      if (
        response.value === undefined ||
        response.value === null ||
        (Array.isArray(response.value) && response.value.length === 0)
      ) {
        return null;
      }

      let answer_type = response.type;
      if (answer_type === "text") {
        answer_type = "string";
      }

      return {
        question_id: questionIdAsNumber,
        answer: response.value,
        answer_type,
      };
    })
    .filter(response => response !== null);
    console.log('Datos enviados al backend:', formattedResponses);
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
    setTimeout(() => navigateTo("/alumne/dashboard"), 500);
  } catch (error) {
    console.error("Error al enviar las respuestas:", error);
    triggerToast("Hubo un problema al enviar las respuestas.", "error");
  }
}

// Función para obtener etiquetas académicas en rating
function getRatingLabel(n) {
  const labels = {
    1: "Deficiente",
    2: "Insuficiente",
    3: "Regular",
    4: "Notable",
    5: "Excelente"
  };
  return labels[n] || "";
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

        <!-- Pregunta tipo "text" -->
        <div v-if="question.type === 'text'">
          <input
            v-model="responses[question.id].value"
            type="text"
            class="mt-2 p-2 border rounded w-full"
            :placeholder="question.placeholder"
          />
        </div>

        <!-- Pregunta tipo "multiple" -->
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

        <!-- Pregunta tipo "checkbox" -->
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

        <!-- Pregunta tipo "number" -->
        <div v-if="question.type === 'number'">
          <input
            v-model="responses[question.id].value"
            type="number"
            class="mt-2 p-2 border rounded w-full"
            :placeholder="question.placeholder"
          />
        </div>

        <!-- Pregunta tipo "rating" con números del 1 al 5 -->
        <div v-if="question.type === 'rating'">
          <div class="grid grid-cols-5 gap-2 mt-2 text-sm text-center">
            <label
              v-for="n in 5"
              :key="n"
              class="cursor-pointer border rounded-lg px-3 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200"
              :class="{ 'bg-blue-500 text-white': responses[question.id].value === n }"
            >
              <input
                type="radio"
                v-model="responses[question.id].value"
                :value="n"
                :name="'question-' + question.id"
                class="hidden"
              />
              <div class="font-semibold">{{ n }}</div>
              <div class="text-xs text-gray-500">{{ getRatingLabel(n) }}</div>
            </label>
          </div>
        </div>

      </div>
    </div>

    <button class="mt-4 bg-primary text-white px-4 py-2 rounded" @click="submitResponses">
      Enviar respuestas
    </button>

    <Toast v-if="showToast" :message="toastMessage" :type="toastType" />
  </div>
</template>
