<template>
  <div class="p-6">
    <div class="relative flex items-center mb-6">
      <button
        class="absolute left-0 flex items-center space-x-1 text-gray-700 hover:text-gray-900"
        @click="navigateTo(`/professor/formularis/respostes/${formId}`)"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M15 19l-7-7 7-7"
          />
        </svg>
        <span>Tornar</span>
      </button>

      <h1 class="flex-grow text-center text-2xl font-bold">
        Respuestas del Usuario
      </h1>
    </div>
    
    <!-- Manejo de estados de carga y errores -->
    <div v-if="error">
      <p class="text-red-500">Error: {{ error }}</p>
    </div>
    <div v-else-if="isLoading">
      <p class="text-gray-500">Cargando respuestas...</p>
    </div>
    <div v-else>
      <!-- Mostrar respuestas si existen -->
      <div v-if="answers && answers.form_title">
        <h2 class="text-2xl font-bold mb-4">{{ answers.form_title }}</h2>
        <p class="text-gray-700">
          <strong>Usuario:</strong> {{ answers.user_name }} {{ answers.user_lastname }}
        </p>

        <!-- Mostrar relaciones sociométricas agrupadas por pregunta -->
        <div v-if="answers.relationships && Object.keys(answers.relationships).length">
          <h3 class="text-xl font-bold mt-6 mb-4">Relaciones Sociométricas</h3>
          <div v-for="(relationship, questionId) in answers.relationships" :key="questionId">
            <div class="bg-gray-50 p-4 rounded-lg shadow mb-4">
              <p class="text-lg font-semibold"><strong>Pregunta:</strong> {{ relationship.question_title }}</p>
              
              <ul class="mt-2 space-y-2">
                <li v-for="(peer, peerIndex) in relationship.peers" :key="peerIndex" class="text-sm text-gray-600">
                  <strong>Compañero/a:</strong> {{ peer.name }} {{ peer.last_name }}<br>
                  <strong>Tipo de Relación:</strong>
                  <span :class="peer.relationship_type === 'positive' ? 'text-green-500' : 'text-red-500'">
                    {{ peer.relationship_type === 'positive' ? 'Positiva' : 'Negativa' }}
                  </span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Mostrar respuestas normales si existen -->
        <div v-if="answers.answers && answers.answers.length">
          <h2 class="text-2xl font-bold mb-4">Formulario: {{ answers.form_title }}</h2>
          <ul class="mt-4 space-y-4">
            <li v-for="(answer, index) in answers.answers" :key="index" class="bg-gray-50 p-4 rounded-lg shadow">
              <p class="text-lg font-semibold"><strong>Pregunta:</strong> {{ answer.question.title }}</p>
              <p class="text-sm text-gray-600">
                <strong>Respuesta:</strong>
                <span v-if="Array.isArray(answer.answer)">{{ answer.answer.join(", ") }}</span>
                <span v-else>{{ answer.answer }}</span>
              </p>
            </li>
          </ul>
        </div>

        <p v-else class="text-gray-500">No hay respuestas para este usuario.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const route = useRoute();
const formId = route.params.formId;
const userId = route.params.userId;

const answers = ref(null); // Cambié de [] a null porque inicialmente no tenemos datos.
const isLoading = ref(true);
const error = ref(null);

const fetchAnswers = async (formId, userId) => {
  try {
    const response = await fetch(
      `http://localhost:8000/api/forms/${formId}/users/${userId}/answers`,
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
      }
    );

    if (!response.ok) {
      throw new Error(`Error al obtener respuestas: ${response.statusText}`);
    }

    const data = await response.json();
    answers.value = data; // Asignamos la respuesta completa
    // Agrupar las respuestas sociométricas por question_id
    if (data.relationships) {
      answers.value.relationships = data.relationships.reduce((acc, relationship) => {
        const { question_id, question_title, peer, relationship_type } = relationship;
        if (!acc[question_id]) {
          acc[question_id] = {
            question_title,
            peers: [],
          };
        }
        acc[question_id].peers.push({
          name: peer.name,
          last_name: peer.last_name,
          relationship_type,
        });
        return acc;
      }, {});
    }
  } catch (err) {
    console.error("Error:", err);
    error.value = err.message;
  } finally {
    isLoading.value = false;
  }
};

const fetchAnswersSociogram = async (formId, userId) => {
  try {
    const response = await fetch(
      `http://localhost:8000/api/forms/${formId}/users/${userId}/relationships`,
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
      }
    );

    if (!response.ok) {
      throw new Error(`Error al obtener relaciones: ${response.statusText}`);
    }

    const data = await response.json();
    answers.value = data; // Asignamos la respuesta completa
    console.log(answers.value);
  } catch (err) {
    console.error("Error:", err);
    error.value = err.message;
  } finally {
    isLoading.value = false;
  }
};

// Llamar a la función cuando se monte el componente
onMounted(() => {
  if (formId === '3') {
    fetchAnswersSociogram(formId, userId);  // Llamamos a la función sociograma
  } else {
    fetchAnswers(formId, userId);  // Llamamos a la función estándar
  }
});
</script>
