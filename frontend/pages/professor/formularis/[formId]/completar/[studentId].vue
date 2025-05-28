<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue';
import Toast from '@/components/common/Toast.vue';
import { useAuthStore } from '@/stores/authStore';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const formId = route.params.formId;
const studentId = route.params.studentId;

// Estado para las preguntas y respuestas
const questions = ref([]);
const responses = ref({});
const studentInfo = ref(null);
const isLoading = ref(true);
const hasError = ref(false);
const errorMessage = ref('');
const isSubmitting = ref(false);

// Control de paginación
const currentQuestionIndex = ref(0);

// Control del toast
const showToast = ref(false);
const toastMessage = ref('');
const toastType = ref('');

// Calcular el progreso
const progressPercentage = computed(() => {
  if (questions.value.length === 0) return 0;
  return ((currentQuestionIndex.value + 1) / questions.value.length) * 100;
});

function triggerToast(message, type = 'success') {
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

onMounted(async () => {
  await loadStudentInfo();
  await fetchFormQuestions();
});

// Cargar información del estudiante
async function loadStudentInfo() {
  try {
    const response = await fetch(`http://localhost:8000/api/users/${studentId}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      }
    });

    if (!response.ok) {
      throw new Error(`Error al cargar los datos del estudiante: ${response.status}`);
    }

    const data = await response.json();
    studentInfo.value = data;
    console.log('Información del estudiante:', studentInfo.value);
  } catch (error) {
    console.error('Error al cargar información del estudiante:', error);
    hasError.value = true;
    errorMessage.value = error.message;
  }
}

// Funció per obtenir preguntes des del backend
async function fetchFormQuestions() {
  isLoading.value = true;
  hasError.value = false;
  
  try {
    const response = await fetch(`http://localhost:8000/api/forms/${formId}/questions`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      }
    });
    
    if (!response.ok) {
      throw new Error(`Error al cargar el formulario: ${response.status}`);
    }
    
    const data = await response.json();
    console.log('Dades rebudes de l\'API:', data);
    
    if (!Array.isArray(data) || data.length === 0) {
      hasError.value = true;
      errorMessage.value = 'No es trobaren preguntes per a aquest formulari';
      return;
    }
    
    // Filtrar preguntes per incloure només les competències
    questions.value = data.filter(question => 
      question.id.toString().startsWith('modificar')
    );
    
    // Inicializar respuestas
    responses.value = {};
    questions.value.forEach(question => {
      responses.value[question.id] = {
        value: question.type === 'checkbox' ? [] : '',
        type: question.type,
      };
    });
    
    console.log('Preguntas cargadas:', questions.value.length);
  } catch (error) {
    console.error('Error al cargar las preguntas:', error);
    hasError.value = true;
    errorMessage.value = error.message;
    triggerToast(`Error: ${error.message}`, 'error');
  } finally {
    isLoading.value = false;
  }
}

// Función para enviar respuestas al backend
async function submitResponses() {
  if (isSubmitting.value) {
    console.log('Ja s\'està enviant el formulari, evitant doble enviament');
    return;
  }
  
  isSubmitting.value = true;
  
  try {
    const formattedResponses = Object.keys(responses.value)
      .map(questionId => {
        const response = responses.value[questionId];
        
        // Verificar si hay respuesta
        if (
          response.value === undefined ||
          response.value === null ||
          (Array.isArray(response.value) && response.value.length === 0) ||
          response.value === ''
        ) {
          return null;
        }

        let answer_type = response.type;
        if (answer_type === 'text') {
          answer_type = 'string';
        }

        return {
          question_id: questionId,
          answer: response.value,
          answer_type,
        };
      })
      .filter(response => response !== null);
    
    console.log('Dades formatejades per enviar:', formattedResponses);

    // Verificar si hi ha respostes per enviar
    if (formattedResponses.length === 0) {
      triggerToast('Si us plau, respon alguna pregunta abans d\'enviar.', 'error');
      isSubmitting.value = false;
      return;
    }

    // Obtener la información de curso y división del estudiante
    let courseId, divisionId;
    
    if (studentInfo.value) {
      if (studentInfo.value.courseDivisions && studentInfo.value.courseDivisions.length > 0) {
        courseId = studentInfo.value.courseDivisions[0].course_id;
        divisionId = studentInfo.value.courseDivisions[0].division_id;
      } else if (studentInfo.value.divisions && studentInfo.value.divisions.length > 0) {
        const division = studentInfo.value.divisions[0];
        courseId = division.pivot?.course_id || division.course_id;
        divisionId = division.id || division.division_id;
      } else if (studentInfo.value.course_id && studentInfo.value.division_id) {
        courseId = studentInfo.value.course_id;
        divisionId = studentInfo.value.division_id;
      }
    }
    
    // Si aún no tenemos la información, intentamos obtenerla de otra manera
    if (!courseId || !divisionId) {
      console.log('No se pudo determinar curso o división. Usando valores por defecto para pruebas.');
      courseId = courseId || 1;
      divisionId = divisionId || 1;
    }

    // URL para enviar las respuestas
    const submitUrl = `http://localhost:8000/api/forms/${formId}/submit-responses`;
    
    // Datos a enviar
    const postData = {
      user_id: studentId,
      course_id: courseId,
      division_id: divisionId,
      responses: formattedResponses,
      completed_by_teacher: true, // Indiquem que va ser completat per un professor
      teacher_id: authStore.user.id
    };
    
    console.log('Dades a enviar:', JSON.stringify(postData, null, 2));
    
    const fetchOptions = {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify(postData),
    };

    const response = await fetch(submitUrl, fetchOptions);
    console.log(`Respuesta de ${submitUrl}:`, response);
    
    // Verificar si la respuesta está vacía
    const responseText = await response.text();
    console.log('Texto de respuesta:', responseText);

    if (!response.ok) {
      let errorMsg = 'Error al enviar las respuestas';
      
      // Solo intentamos parsear si el texto tiene contenido JSON válido
      if (responseText && responseText.trim().startsWith('{')) {
        try {
          const errorData = JSON.parse(responseText);
          errorMsg = errorData.message || errorMsg;
        } catch (e) {
          console.error('No se pudo parsear la respuesta de error:', e);
        }
      }
      throw new Error(errorMsg);
    }

    // Si llegamos aquí, fue exitoso
    triggerToast('¡Respuestas enviadas correctamente!', 'success');
    
    // Esperamos un poco antes de redirigir
    setTimeout(() => {
      router.push(`/professor/studentProfile/${studentId}`);
    }, 1500);
  } catch (error) {
    console.error('Error al enviar las respuestas:', error);
    triggerToast(`Error: ${error.message}`, 'error');
  } finally {
    isSubmitting.value = false;
  }
}

// Función para obtener etiquetas para rating
function getRatingLabel(n) {
  const labels = {
    1: 'Mai',
    2: 'Poques vegades',
    3: 'Algunes vegades',
    4: 'Gairebé sempre',
    5: 'Sempre'
  };
  return labels[n] || '';
}
</script>

<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <DashboardNavTeacher />
    
    <div class="max-w-3xl mx-auto px-4 mt-8">
      <!-- Cabecera del formulario -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Formulari d'autoavaluació</h2>
            <p class="text-gray-600">Completant com a professor per a l'alumne:</p>
            <p v-if="studentInfo" class="font-semibold text-primary mt-1">
              {{ studentInfo.name }} {{ studentInfo.last_name }}
            </p>
          </div>
          <button 
            @click="router.push(`/professor/studentProfile/${studentId}`)"
            class="mt-4 md:mt-0 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
          >
            Tornar al perfil
          </button>
        </div>
      </div>

      <!-- Estado de carga -->
      <div v-if="isLoading" class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center justify-center">
        <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        <p class="mt-4 text-gray-600 font-medium">Cargando formulario...</p>
      </div>
      
      <!-- Error -->
      <div v-else-if="hasError" class="bg-white rounded-xl shadow-lg p-8 text-center">
        <div class="text-red-500 mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Error al cargar las preguntas</h3>
        <p class="text-gray-600 mb-4">{{ errorMessage }}</p>
        <button 
          @click="router.push(`/professor/studentProfile/${studentId}`)" 
          class="px-6 py-3 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
        >
          Volver al perfil
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
                :placeholder="questions[currentQuestionIndex].placeholder || 'Escribe tu respuesta aquí'"
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
                <span v-else>Enviar respostes</span>
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
        <h3 class="text-xl font-semibold text-gray-800 mb-2">No hay preguntas disponibles</h3>
        <p class="text-gray-600 mb-4">Este formulario no tiene preguntas configuradas o no se encontraron las competencias necesarias.</p>
        <button 
          @click="router.push(`/professor/studentProfile/${studentId}`)" 
          class="px-6 py-3 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
        >
          Volver al perfil
        </button>
      </div>
    </div>

    <Toast v-if="showToast" :message="toastMessage" :type="toastType" />
  </div>
</template>