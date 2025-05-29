<script setup>
import Toast from "@/components/common/Toast.vue";

const route = useRoute();
const formId = route.params.id;
const questions = ref([]);
const responses = ref({});
const authStore = useAuthStore();
const user = authStore.user;
const userId = user.id;
const isLoading = ref(true);
const hasError = ref(false);
const errorMessage = ref("");
const isSubmitting = ref(false);

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
  setTimeout(() => (showToast.value = false), 3000);
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
  fetchFormQuestions();
  console.log("ID del formulario:", formId);
  console.log("Datos del usuario:", user);
});

// Función para obtener preguntas desde el backend
async function fetchFormQuestions() {
  isLoading.value = true;
  hasError.value = false;
  
  try {
    // Usar directamente la ruta que sabemos que funciona
    const apiUrl = `http://localhost:8000/api/forms/${formId}/questions`;
    const response = await fetch(apiUrl);
    
    if (!response.ok) {
      throw new Error(`Error al cargar el formulario: ${response.status} ${response.statusText}`);
    }
    
    const data = await response.json();
    console.log("Datos recibidos de la API:", data);
    
    if (!Array.isArray(data) || data.length === 0) {
      hasError.value = true;
      errorMessage.value = "No se encontraron preguntas para este formulario";
      return;
    }
    
    // Asignar las preguntas
    questions.value = data;
    
    // Inicializar respuestas
    responses.value = {};
    questions.value.forEach(question => {
      responses.value[question.id] = {
        value: question.type === "checkbox" ? [] : "",
        type: question.type,
      };
    });
    
    console.log("Preguntas cargadas:", questions.value.length);
  } catch (error) {
    console.error("Error al cargar las preguntas:", error);
    hasError.value = true;
    errorMessage.value = error.message;
    triggerToast(`Error: ${error.message}`, "error");
  } finally {
    isLoading.value = false;
  }
}

// Función para enviar respuestas al backend
async function submitResponses() {
  if (isSubmitting.value) {
    console.log("Ya se está enviando el formulario, evitando doble envío");
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    const formattedResponses = Object.keys(responses.value)
      .map(questionId => {
        const response = responses.value[questionId];
        const questionIdAsNumber = parseInt(questionId, 10);

        // Verificar explícitamente cada tipo de respuesta
        if (
          response.value === undefined ||
          response.value === null ||
          (Array.isArray(response.value) && response.value.length === 0) ||
          response.value === ""
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
      
    console.log('Datos formateados para enviar:', formattedResponses);

    // Verificar si hay respuestas para enviar
    if (formattedResponses.length === 0) {
      triggerToast("Por favor, responde alguna pregunta antes de enviar.", "error");
      isSubmitting.value = false;
      return;
    }

    // Obtener la información de curso y división del usuario
    let courseId, divisionId;
    
    console.log("Estructura completa del usuario:", JSON.stringify(user, null, 2));
    
    // Intentamos todas las posibles estructuras de datos
    if (user.courseDivisions && user.courseDivisions.length > 0) {
      courseId = user.courseDivisions[0].course_id;
      divisionId = user.courseDivisions[0].division_id;
      console.log("Información encontrada en user.courseDivisions");
    } else if (user.divisions && user.divisions.length > 0) {
      // Intentamos con la estructura divisions
      const division = user.divisions[0];
      courseId = division.pivot?.course_id || division.course_id;
      divisionId = division.id || division.division_id;
      console.log("Información encontrada en user.divisions");
    } else if (user.course_id && user.division_id) {
      // Si los IDs están directamente en el objeto user
      courseId = user.course_id;
      divisionId = user.division_id;
      console.log("Información encontrada directamente en el usuario");
    }
    
    console.log("Información de usuario para el envío:", {
      userId,
      courseId,
      divisionId
    });

    // Si aún no tenemos los IDs, intentamos buscarlos en la API
    if (!courseId || !divisionId) {
      console.log("Intentando obtener información del usuario desde la API");
      try {
        const userInfoResponse = await fetch(`http://localhost:8000/api/users/${userId}`);
        if (userInfoResponse.ok) {
          const userDataText = await userInfoResponse.text();
          console.log("Respuesta de API de usuario:", userDataText);
          
          // Solo parseamos si es un JSON válido
          if (userDataText && userDataText.trim().startsWith('{')) {
            try {
              const userData = JSON.parse(userDataText);
              console.log("Datos de usuario obtenidos de la API:", userData);
              
              if (userData.courseDivisions && userData.courseDivisions.length > 0) {
                courseId = userData.courseDivisions[0].course_id;
                divisionId = userData.courseDivisions[0].division_id;
              } else if (userData.divisions && userData.divisions.length > 0) {
                const division = userData.divisions[0];
                courseId = division.pivot?.course_id || division.course_id;
                divisionId = division.id || division.division_id;
              } else if (userData.course_id && userData.division_id) {
                courseId = userData.course_id;
                divisionId = userData.division_id;
              }
            } catch (parseError) {
              console.error("Error al parsear la respuesta de usuario:", parseError);
            }
          }
        } else {
          console.error("Error al obtener información de usuario:", userInfoResponse.status);
        }
      } catch (err) {
        console.error("Error al intentar obtener información adicional del usuario:", err);
      }
    }
      
    // Si aún no tenemos la información, podemos intentar con valores por defecto o mostrar error
    if (!courseId || !divisionId) {
      console.log("No se pudo determinar curso o división. Usando valores por defecto para pruebas.");
      // OPCIÓN 1: Usar valores por defecto (solo para pruebas/desarrollo)
      courseId = courseId || 1;  // Valor por defecto como último recurso
      divisionId = divisionId || 1;  // Valor por defecto como último recurso
      
      // OPCIÓN 2: Mostrar error y abortar (recomendado para producción)
      // Descomenta estas líneas y comenta las dos anteriores para producción
      // triggerToast("No se pudo determinar tu curso o división. Por favor, contacta al administrador.", "error");
      // isSubmitting.value = false;
      // return;
    }

    // Usar directamente la URL correcta
    const submitUrl = `http://localhost:8000/api/forms/${formId}/submit-responses`;
    
    let submitted = false;
    let errorMsg = "";
    
    // Datos a enviar
    const postData = {
      user_id: userId,
      course_id: courseId,
      division_id: divisionId,
      responses: formattedResponses,
    };
    
    console.log("Datos a enviar:", JSON.stringify(postData, null, 2));
    
    try {
      console.log(`Enviando a: ${submitUrl}`);
        
        const fetchOptions = {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
          },
          body: JSON.stringify(postData),
        };

        const response = await fetch(submitUrl, fetchOptions);
        console.log(`Respuesta de ${submitUrl}:`, response);
        
        // Verificar si la respuesta está vacía
        const responseText = await response.text();
        console.log("Texto de respuesta:", responseText);

        if (!response.ok) {
          errorMsg = "Error al enviar las respuestas";
          
          // Solo intentamos parsear si el texto tiene contenido JSON válido
          if (responseText && responseText.trim().startsWith('{')) {
            try {
              const errorData = JSON.parse(responseText);
              errorMsg = errorData.message || errorMsg;
            } catch (e) {
              console.error("No se pudo parsear la respuesta de error:", e);
            }
          }
          throw new Error(errorMsg);
        }

        // Procesamos la respuesta como JSON solo si tiene contenido válido
        if (responseText && responseText.trim()) {
          try {
            const responseData = responseText.trim().startsWith('{') ? 
              JSON.parse(responseText) : 
              { message: "Operación completada" };
            console.log("Respuesta procesada:", responseData);
          } catch (e) {
            console.warn("La respuesta no es un JSON válido, pero la operación fue exitosa:", e);
          }
        }

        // Si llegamos aquí, fue exitoso
        submitted = true;
        triggerToast("¡Respostes enviades correctament!", "success");
        
        // Esperamos un poco antes de redirigir
        setTimeout(() => {
          navigateTo("/alumne/dashboard");
        }, 1500);
      } catch (error) {
        console.error(`Error al enviar a ${submitUrl}:`, error);
        triggerToast(`Error: ${error.message}`, "error");
      }
  } catch (error) {
    console.error("Error al enviar las respuestas:", error);
    triggerToast(`Error: ${error.message}`, "error");
  } finally {
    isSubmitting.value = false;
  }
}

// Función para obtener etiquetas para rating
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

      <!-- Estado de carga -->
      <div v-if="isLoading" class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center justify-center">
        <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="mt-4 text-gray-600 font-medium">Carregant formulari...</p>
      </div>
      
      <!-- Error -->
      <div v-else-if="hasError" class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="text-red-500 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Error en carregar les preguntes</h3>
        <p class="text-gray-600 mb-4">{{ errorMessage }}</p>
        <button 
          @click="navigateTo('/alumne/dashboard')" 
          class="px-6 py-3 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
        >
          Tornar al Dashboard
        </button>
      </div>
      
      <!-- Contenido del formulario -->
      <template v-else-if="questions.length > 0">
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
        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="space-y-6">
            <!-- Título y descripción de la pregunta -->
            <div class="border-l-4 border-blue-500 pl-4">
              <h3 class="text-xl font-semibold text-gray-800">
                {{ questions[currentQuestionIndex].title }}
              </h3>
              <p class="mt-2 text-gray-600" v-if="questions[currentQuestionIndex].placeholder">
                {{ questions[currentQuestionIndex].placeholder }}
              </p>
              <p class="mt-2 text-gray-600" v-else-if="questions[currentQuestionIndex].context">
                {{ questions[currentQuestionIndex].context }}
              </p>
            </div>

            <!-- Tipos de preguntas -->
            <!-- Input de texto -->
            <div v-if="questions[currentQuestionIndex].type === 'text'" class="mt-4">
              <input
                v-model="responses[questions[currentQuestionIndex].id].value"
                type="text"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                :placeholder="questions[currentQuestionIndex].placeholder || 'Escriu la teva resposta aquí'"
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
                :placeholder="questions[currentQuestionIndex].placeholder || 'Ingresa un número'"
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
                :disabled="isSubmitting"
                :class="{ 'opacity-70 cursor-wait': isSubmitting }"
              >
                <span v-if="isSubmitting">
                  <span class="inline-block animate-spin mr-2">⟳</span> Enviant...
                </span>
                <span v-else> Enviant respostes</span>
              </button>
              <button
                v-else
                @click="nextQuestion"
                class="px-6 py-3 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
              >
                Següent →
              </button>
            </div>
          </div>
        </div>
      </template>
      
      <!-- Sin preguntas -->
      <div v-else-if="!isLoading && questions.length === 0" class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="text-yellow-500 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">No hi ha preguntes disponibles</h3>
        <p class="text-gray-600 mb-4">Aquest formulari no té preguntes configurades.</p>
        <button 
          @click="navigateTo('/alumne/dashboard')" 
          class="px-6 py-3 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
        >
          Tornar al Dashboard
        </button>
      </div>
    </div>

    <Toast v-if="showToast" :message="toastMessage" :type="toastType" />
  </div>
</template>