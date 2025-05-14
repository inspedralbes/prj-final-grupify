<script setup>
import Toast from "@/components/common/Toast.vue";

const route = useRoute();
const formId = route.params.id;
const questions = ref([]);
const responses = ref({});
const authStore = useAuthStore();
const user = authStore.user;
const userId = user.id;

// Control de paginación
const currentQuestionIndex = ref(0);

// Control del toast
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("");

// Calcular el progreso
const progressPercentage = computed(() => {
  if (questions.value.length === 0) return 0;
  return (currentQuestionIndex.value + 1) / questions.value.length * 100;
});

function triggerToast(message, type = "success") {
  toastMessage.value = message;
  toastType.value = type;
  showToast.value = true;
  setTimeout(() => (showToast.value = false), 1000);
}

function nextQuestion() {
  if (currentQuestionIndex.value < questions.value.length - 1) {
    currentQuestionIndex.value++;
  }
}

function previousQuestion() {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--;
  }
}

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
    1: "Mai",
    2: "Poques vegades",
    3: "Algunes vegades",
    4: "Gairebé sempre",
    5: "Sempre"
  };
  return labels[n] || "";
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4">
      <!-- Cabecera del formulario -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Formulari d'autoavaluació</h2>
        <p class="text-gray-600">Completeu totes les preguntes per continuar</p>
      </div>

      <!-- Barra de progreso -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700">
            Pregunta {{ currentQuestionIndex + 1 }} de {{ questions.length }}
          </span>
          <span class="text-sm font-medium text-blue-600">
            {{ Math.round(progressPercentage) }}% completat
          </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
          <div
            class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
            :style="{ width: `${progressPercentage}%` }"
          ></div>
        </div>
      </div>

      <!-- Contenedor de la pregunta -->
      <div v-if="questions.length > 0" class="bg-white rounded-xl shadow-lg p-6">
        <div class="space-y-6">
          <!-- Título y descripción de la pregunta -->
          <div class="border-l-4 border-blue-500 pl-4">
            <h3 class="text-xl font-semibold text-gray-800">
              {{ questions[currentQuestionIndex].title }}
            </h3>
            <p class="mt-2 text-gray-600">
              {{ questions[currentQuestionIndex].placeholder }}
            </p>
          </div>

          <!-- Tipos de preguntas -->
          <!-- Input de texto -->
          <div v-if="questions[currentQuestionIndex].type === 'text'" class="mt-4">
            <input
              v-model="responses[questions[currentQuestionIndex].id].value"
              type="text"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
              :placeholder="questions[currentQuestionIndex].placeholder"
            />
          </div>

          <!-- Opción múltiple -->
          <div v-if="questions[currentQuestionIndex].type === 'multiple'" class="space-y-3">
            <div
              v-for="option in questions[currentQuestionIndex].options"
              :key="option.id"
              class="relative"
            >
              <label
                class="block p-4 rounded-lg border-2 transition-all duration-200 cursor-pointer"
                :class="{
                  'border-blue-500 bg-blue-50': responses[questions[currentQuestionIndex].id].value === option.id,
                  'border-gray-200 hover:border-gray-300': responses[questions[currentQuestionIndex].id].value !== option.id
                }"
              >
                <input
                  v-model="responses[questions[currentQuestionIndex].id].value"
                  type="radio"
                  :name="'question-' + questions[currentQuestionIndex].id"
                  :value="option.id"
                  class="absolute opacity-0"
                />
                <span class="text-gray-700">{{ option.text }}</span>
              </label>
            </div>
          </div>

          <!-- Checkbox -->
          <div v-if="questions[currentQuestionIndex].type === 'checkbox'" class="space-y-3">
            <div
              v-for="option in questions[currentQuestionIndex].options"
              :key="option.id"
              class="relative"
            >
              <label
                class="block p-4 rounded-lg border-2 transition-all duration-200 cursor-pointer"
                :class="{
                  'border-blue-500 bg-blue-50': responses[questions[currentQuestionIndex].id].value.includes(option.id),
                  'border-gray-200 hover:border-gray-300': !responses[questions[currentQuestionIndex].id].value.includes(option.id)
                }"
              >
                <input
                  v-model="responses[questions[currentQuestionIndex].id].value"
                  type="checkbox"
                  :value="option.id"
                  class="absolute opacity-0"
                />
                <span class="text-gray-700">{{ option.text }}</span>
              </label>
            </div>
          </div>

          <!-- Input numérico -->
          <div v-if="questions[currentQuestionIndex].type === 'number'" class="mt-4">
            <input
              v-model="responses[questions[currentQuestionIndex].id].value"
              type="number"
              class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
              :placeholder="questions[currentQuestionIndex].placeholder"
            />
          </div>

          <!-- Rating -->
          <div v-if="questions[currentQuestionIndex].type === 'rating'" class="mt-4">
            <div class="grid grid-cols-5 gap-3">
              <label
                v-for="n in 5"
                :key="n"
                class="cursor-pointer text-center"
              >
                <input
                  type="radio"
                  v-model="responses[questions[currentQuestionIndex].id].value"
                  :value="n"
                  :name="'question-' + questions[currentQuestionIndex].id"
                  class="hidden"
                />
                <div
                  class="p-4 rounded-lg transition-all duration-200"
                  :class="{
                    'bg-blue-500 text-white shadow-lg transform scale-105': responses[questions[currentQuestionIndex].id].value === n,
                    'bg-gray-100 hover:bg-gray-200': responses[questions[currentQuestionIndex].id].value !== n
                  }"
                >
                  <div class="text-xl font-bold mb-1">{{ n }}</div>
                  <div class="text-xs">{{ getRatingLabel(n) }}</div>
                </div>
              </label>
            </div>
          </div>

          <!-- Botones de navegación -->
          <div class="flex justify-between mt-8 pt-6 border-t">
            <button
              @click="previousQuestion"
              class="px-6 py-3 rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors duration-200"
              :disabled="currentQuestionIndex === 0"
              :class="{ 'opacity-50 cursor-not-allowed': currentQuestionIndex === 0 }"
            >
              ← Anterior
            </button>

            <button
              v-if="currentQuestionIndex === questions.length - 1"
              @click="submitResponses"
              class="px-6 py-3 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
            >
              Enviar respuestas
            </button>
            <button
              v-else
              @click="nextQuestion"
              class="px-6 py-3 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
            >
              Siguiente →
            </button>
          </div>
        </div>
      </div>
    </div>

    <Toast v-if="showToast" :message="toastMessage" :type="toastType" />
  </div>
</template>