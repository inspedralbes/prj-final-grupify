<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <DashboardNavTeacher class="shadow-md z-10" />

    <div class="container mx-auto px-4 py-8 flex-grow">
      <div
        class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-4xl mx-auto"
      >
        <div class="h-1" style="background-color: rgb(0, 173, 238)"></div>

        <div class="p-8">
          <div class="flex justify-between items-center mb-8">
            <button
              @click="navigateBack"
              class="text-gray-500 hover:text-gray-700 transition-colors"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-6 w-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M10 19l-7-7 7-7"
                />
              </svg>
            </button>

            <h1
              class="text-2xl font-semibold text-center flex-grow"
              style="color: rgb(0, 173, 238)"
            >
              {{ answers?.form_title || "Form Responses" }}
            </h1>
          </div>

          <div v-if="isLoading" class="text-center py-12">
            <div class="animate-pulse h-4 bg-gray-200 w-1/2 mx-auto"></div>
          </div>

          <div v-else-if="error" class="text-center text-red-500">
            {{ error }}
          </div>

          <div v-else-if="answers">
            <div class="mb-8">
              <h2
                class="text-lg font-medium mb-4 pb-2 border-b"
                style="color: rgb(0, 173, 238)"
              >
                Student Details
              </h2>
              <p class="text-gray-700 capitalize">
                {{ answers.user_name }} {{ answers.user_lastname }}
              </p>
            </div>

            <!-- Sociometric Relationships -->
            <div
              v-if="
                answers.relationships &&
                Object.keys(answers.relationships).length
              "
            >
              <h2
                class="text-lg font-medium mb-4 pb-2 border-b"
                style="color: rgb(0, 173, 238)"
              >
                Sociometric Relationships
              </h2>
              <div
                v-for="(relationship, questionId) in answers.relationships"
                :key="questionId"
                class="mb-6"
              >
                <h3 class="font-medium mb-3 text-gray-600">
                  {{ relationship.question_title }}
                </h3>
                <div class="grid md:grid-cols-2 gap-3">
                  <div
                    v-for="(peer, peerIndex) in relationship.peers"
                    :key="peerIndex"
                    class="bg-gray-100 rounded-lg p-3"
                  >
                    <div class="flex justify-between items-center">
                      <span class="text-gray-800">
                        {{ peer.name }} {{ peer.last_name }}
                      </span>
                      <span
                        :class="
                          peer.relationship_type === 'positive'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700'
                        "
                        class="px-2 py-1 rounded-full text-xs"
                      >
                        {{
                          peer.relationship_type === "positive"
                            ? "Positive"
                            : "Negative"
                        }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Answers -->
            <div v-if="answers.answers && answers.answers.length">
              <h2
                class="text-lg font-medium mb-4 pb-2 border-b"
                style="color: rgb(0, 173, 238)"
              >
                Responses
              </h2>
              <div
                v-for="(answer, index) in answers.answers"
                :key="index"
                class="mb-4 p-4 bg-gray-100 rounded-lg"
              >
                <h3 class="font-medium mb-2 text-gray-700">
                  {{ answer.question.title }}
                </h3>
                <p class="text-gray-600">
                  <span v-if="Array.isArray(answer.answer)">
                    {{ answer.answer.join(", ") }}
                  </span>
                  <span v-else>{{ answer.answer }}</span>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import DashboardNavTeacher from "~/components/Teacher/DashboardNavTeacher.vue";
const route = useRoute();
const formId = route.params.formId;
const userId = route.params.userId;

const answers = ref(null); // Cambié de [] a null porque inicialmente no tenemos datos.
const isLoading = ref(true);
const error = ref(null);

const navigateBack = () => {
  navigateTo(`/professor/formularis/respostes/${formId}`);
};

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
      answers.value.relationships = data.relationships.reduce(
        (acc, relationship) => {
          const { question_id, question_title, peer, relationship_type } =
            relationship;
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
        },
        {}
      );
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
    // console.log(answers.value);
  } catch (err) {
    console.error("Error:", err);
    error.value = err.message;
  } finally {
    isLoading.value = false;
  }
};

// Llamar a la función cuando se monte el componente
onMounted(() => {
  if (formId === "3") {
    fetchAnswersSociogram(formId, userId); // Llamamos a la función sociograma
  } else {
    fetchAnswers(formId, userId); // Llamamos a la función estándar
  }
});
</script>
