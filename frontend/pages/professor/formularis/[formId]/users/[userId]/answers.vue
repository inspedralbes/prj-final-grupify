<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <DashboardNavTeacher class="shadow-md z-10" />

    <div class="container mx-auto px-4 py-8 flex-grow">
      <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-4xl mx-auto">
        <div class="h-1" style="background-color: rgb(0, 173, 238)"></div>

        <div class="p-8">
          <div class="flex justify-between items-center mb-8">
            <button @click="navigateBack" class="text-gray-500 hover:text-gray-700 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7 7-7" />
              </svg>
            </button>

            <h1 class="text-2xl font-semibold text-center flex-grow" style="color: rgb(0, 173, 238)">
              {{ answers?.form_title || "Form Responses" }}
            </h1>
          </div>

          <!-- Estado de carga -->
          <div v-if="isLoading" class="text-center py-12">
            <div class="animate-pulse h-4 bg-gray-200 w-1/2 mx-auto"></div>
          </div>

          <!-- Mensaje de error -->
          <div v-else-if="error" class="text-center text-red-500">
            {{ error }}
          </div>

          <!-- Mostrar respuestas -->
          <div v-else-if="answers">
            <!-- Datos del estudiante -->
            <div class="mb-8">
              <h2 class="text-lg font-medium mb-4 pb-2 border-b" style="color: rgb(0, 173, 238)">
                Dades de l'estudiant
              </h2>
              <!-- Información del estudiante con avatar si está disponible -->
              <div class="flex items-center space-x-3">
                <div v-if="answers.user_image" class="w-12 h-12 rounded-full overflow-hidden">
                  <img :src="answers.user_image" alt="Avatar" class="w-full h-full object-cover"
                    @error="onImageError" />
                </div>
                <div v-else
                  class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                  {{answers.user_name ? answers.user_name.split(" ").map(n => n[0]).join("").toUpperCase() : 'U'}}
                </div>
                <p class="text-gray-700 capitalize">
                  {{ answers.user_name }} {{ answers.user_lastname }}
                </p>
              </div>
            </div>

            <!-- Relaciones sociométricas (para formularios 3 y 2) -->
            <div v-if="
              answers.relationships &&
              Object.keys(answers.relationships).length
            ">
              <h2 class="text-lg font-medium mb-4 pb-2 border-b" style="color: rgb(0, 173, 238)">
                Relacions sociomètriques {{ formId === '2' ? 'CESC' : '' }}
              </h2>
              <div v-for="(relationship, questionId) in answers.relationships" :key="questionId" class="mb-6">
                <h3 class="font-medium mb-3 text-gray-600">
                  {{ relationship.question_title }}
                </h3>
                <div class="grid md:grid-cols-2 gap-3">
                  <div v-for="(peer, peerIndex) in relationship.peers" :key="peerIndex"
                    class="bg-gray-100 rounded-lg p-3">
                    <div class="flex justify-between items-center">
                      <span class="text-gray-800">
                        {{ peer.name }} {{ peer.last_name }}
                      </span>
                      <!-- Para sociogramas estándar (formId 3) -->
                      <span v-if="formId === '3'" :class="peer.relationship_type === 'positive'
                          ? 'bg-green-100 text-green-700'
                          : 'bg-red-100 text-red-700'
                        " class="px-2 py-1 rounded-full text-xs">
                        {{
                          peer.relationship_type === "positive"
                            ? "Positive"
                            : "Negative"
                        }}
                      </span>
                      <!-- Para CESC (formId 2) -->
                      <span v-else-if="formId === '2'" :class="getCescTagClass(peer.tag_id)"
                        class="px-2 py-1 rounded-full text-xs">
                        {{ peer.tag_name || getCescTagName(peer.tag_id) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Respuestas del formulario -->
            <div v-if="answers.answers && answers.answers.length">
              <h2 class="text-lg font-medium mb-4 pb-2 border-b" style="color: rgb(0, 173, 238)">
                Respostes
              </h2>

              <!-- Diseño específico para el formulario con ID 4 -->
              <template v-if="formId === '4'">
                <div v-for="(answer, index) in answers.answers" :key="index" class="mb-6 p-4 bg-gray-50 rounded-lg">
                  <h3 class="font-medium mb-2 text-gray-700">
                    {{ answer.question.title }}
                  </h3>
                  <p class="text-gray-600 mb-3">
                    <span v-if="Array.isArray(answer.answer)">
                      {{ answer.answer.join(", ") }}
                    </span>
                    <span v-else>{{ answer.answer }}</span>
                  </p>

                  <!-- Contenedor de la competencia y puntuación -->
                  <div class="mt-8">
                    <div class="flex justify-between items-center mb-2">
                      <!-- Competencia -->
                      <div v-if="answer.competence" class="text-lg font-bold" style="color: rgb(0, 173, 238)">
                        {{ answer.competence }}
                      </div>

                      <!-- Puntuación (X/5) -->
                      <div class="text-sm font-bold" :style="{ color: getSkillColor(answer.answer).text }">
                        ({{ answer.answer }}/5)
                      </div>
                    </div>

                    <!-- Barra de progreso -->
                    <div class="h-2 w-full bg-gray-200 rounded-full overflow-hidden shadow-sm">
                      <div class="h-full rounded-full transition-all duration-300"
                        :class="getSkillColor(answer.answer).background"
                        :style="{ width: `${getSkillPercentage(answer.answer)}%` }"></div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Diseño para otros formularios -->
              <template v-else>
                <div v-for="(answer, index) in answers.answers" :key="index" class="mb-4 p-4 bg-gray-100 rounded-lg">
                  <h3 class="font-medium mb-2 text-gray-700">
                    {{ answer.question.title }}
                    <span v-if="answer.competence" class="text-sm text-gray-500">
                      ({{ answer.competence }})
                    </span>
                  </h3>
                  <p class="text-gray-600">
                    <span v-if="Array.isArray(answer.answer)">
                      {{ answer.answer.join(", ") }}
                    </span>
                    <span v-else>{{ answer.answer }}</span>
                  </p>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "~/stores/authStore";
import DashboardNavTeacher from "~/components/Teacher/DashboardNavTeacher.vue";

const route = useRoute();
const router = useRouter();
const formId = route.params.formId;
const userId = route.params.userId;
const authStore = useAuthStore();

// Definir competencias
const competences = [
  { id: 22, name: "Responsabilitat" },
  { id: 23, name: "Treball en equip" },
  { id: 24, name: "Gestió del temps" },
  { id: 25, name: "Comunicació" },
  { id: 26, name: "Adaptabilitat" },
  { id: 27, name: "Lideratge" },
  { id: 28, name: "Creativitat" },
  { id: 29, name: "Proactivitat" },
];

// Estados del componente
const answers = ref({}); // Inicializado como objeto vacío
const isLoading = ref(true);
const error = ref(null);

// Navegar hacia atrás
const navigateBack = () => {
  router.push(`/professor/formularis/respostes/${formId}`);
};

// Calcular el porcentaje de la respuesta
const getSkillPercentage = (answer) => {
  if (typeof answer === "number") {
    return Math.min(100, Math.max(0, answer * 20)); // Escalar a un porcentaje (ejemplo: 4 -> 80%)
  }
  return 0; // Si no es un número, mostrar 0%
};

// Asignar un color en función del valor de la respuesta
const getSkillColor = (answer) => {
  if (typeof answer === "number") {
    if (answer >= 4) return { background: "bg-gradient-to-r from-green-400 to-green-600", text: "text-green-600" };
    if (answer >= 2) return { background: "bg-gradient-to-r from-yellow-400 to-yellow-600", text: "text-yellow-600" };
    return { background: "bg-gradient-to-r from-red-400 to-red-600", text: "text-red-600" };
  }
  return { background: "bg-gradient-to-r from-gray-400 to-gray-600", text: "text-gray-600" }; // Color por defecto
};

// Obtener el nombre de la etiqueta CESC según el tag_id
const getCescTagName = (tagId) => {
  const tagNames = {
    1: "Popular",
    2: "Rebutjat",
    3: "Agressiu",
    4: "Prosocial",
    5: "Víctima"
  };
  return tagNames[tagId] || "Desconegut";
};

// Obtener la clase CSS para la etiqueta CESC según el tag_id
const getCescTagClass = (tagId) => {
  const tagClasses = {
    1: "bg-blue-100 text-blue-700", // Popular
    2: "bg-orange-100 text-orange-700", // Rebutjat
    3: "bg-red-100 text-red-700", // Agressiu
    4: "bg-green-100 text-green-700", // Prosocial
    5: "bg-purple-100 text-purple-700" // Víctima
  };
  return tagClasses[tagId] || "bg-gray-100 text-gray-700";
};

// Obtener respuestas del formulario
const fetchAnswers = async (formId, userId) => {
  try {
    const response = await fetch(
      `https://api.basebrutt.com/api/forms/${formId}/users/${userId}/answers`,
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    );

    if (!response.ok) {
      throw new Error(`Error al obtener respuestas: ${response.statusText}`);
    }

    const data = await response.json();
    console.log("Data from API:", data); // Depuración

    // Asignar la respuesta y agregar competencias
    answers.value = data;
    if (answers.value.answers) {
      answers.value.answers = answers.value.answers.map((answer) => {
        const competence = competences.find((comp) => comp.id === answer.question.id);
        return {
          ...answer,
          competence: competence ? competence.name : null, // Asignar null si no hay competencia
        };
      });
    }
  } catch (err) {
    console.error("Error:", err);
    error.value = err.message;
  } finally {
    isLoading.value = false;
  }
};

// Obtener relaciones sociométricas CESC
const fetchAnswersCesc = async (formId, userId) => {
  try {
    const response = await fetch(
      `https://api.basebrutt.com/api/forms/${formId}/users/${userId}/relationships-cesc`,
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    );

    if (!response.ok) {
      throw new Error(`Error al obtener relaciones CESC: ${response.statusText}`);
    }

    const data = await response.json();
    console.log("CESC Data from API:", data); // Depuración

    // Transformar las relaciones de objeto a array para que se pueda recorrer en el template
    if (data.relationships && typeof data.relationships === 'object') {
      const relationshipsArray = Object.values(data.relationships);
      data.relationships = relationshipsArray;
    }

    answers.value = data;
  } catch (err) {
    console.error("Error:", err);
    error.value = err.message;
  } finally {
    isLoading.value = false;
  }
};

// Obtener relaciones sociométricas
const fetchAnswersSociogram = async (formId, userId) => {
  try {
    isLoading.value = true;
    const response = await fetch(
      `https://api.basebrutt.com/api/forms/${formId}/users/${userId}/relationships`,
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${authStore.token}`,
        },
      }
    );

    if (!response.ok) {
      throw new Error(`Error al obtener relaciones: ${response.statusText || response.status}`);
    }

    const data = await response.json();
    console.log("Sociogram Data from API:", data); // Depuración

    // Verificar la estructura de datos y formatear si es necesario
    if (data && data.relationships) {
      // Si relationships ya es un objeto, convertirlo a array para facilitar su uso en la vista
      if (typeof data.relationships === 'object' && !Array.isArray(data.relationships)) {
        data.relationships = Object.values(data.relationships);
      }
    }

    answers.value = data;
  } catch (err) {
    console.error("Error:", err);
    error.value = err.message || "Error al cargar las respuestas";

    // Si hay problemas con el formato o estructura, intentar usar la petición de respuestas normal
    if (err.message && err.message.includes("TypeError")) {
      try {
        await fetchAnswers(formId, userId);
      } catch (backupErr) {
        console.error("Error en la solicitud de respaldo:", backupErr);
      }
    }
  } finally {
    isLoading.value = false;
  }
};

// Función para manejar errores de carga de imágenes
const onImageError = (event) => {
  // Ocultar la imagen con error y mostrar iniciales en su lugar
  event.target.style.display = 'none';
  const parent = event.target.parentElement;
  if (parent) {
    parent.classList.add('bg-primary/10', 'flex', 'items-center', 'justify-center', 'text-primary', 'font-bold');
    // Mostrar iniciales basadas en el nombre
    if (answers.value.user_name) {
      parent.textContent = answers.value.user_name.split(" ").map(n => n[0]).join("").toUpperCase();
    } else {
      parent.textContent = 'U'; // Default si no hay nombre
    }
  }
};

// Cargar datos al montar el componente
onMounted(() => {
  if (formId === "3") {
    fetchAnswersSociogram(formId, userId); // Formulario sociométrico
  } else if (formId === "2") {
    fetchAnswersCesc(formId, userId); // Formulario CESC
  } else {
    fetchAnswers(formId, userId); // Formulario estándar
  }
});
</script>