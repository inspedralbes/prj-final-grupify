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

      <h1 class="flex-grow text-center text-2xl font-bold">Respuestas del Usuario</h1>
    </div>
  
    
    <div v-if="error">
      <p>Error: {{ error }}</p>
    </div>
    <div v-else-if="isLoading">
      <p>Cargando respuestas...</p>
    </div>
    <div v-else>
      <div v-if="answers && answers.answers && answers.answers.length">
        <!-- Mostrar título del formulario -->
        <h2>Formulario: {{ answers.form_title }}</h2>
        <!-- Mostrar nombre y apellido del usuario -->
        <p><strong>Usuario:</strong> {{ answers.user_name }} {{ answers.user_lastname }}</p>

        <ul>
          <li v-for="(answer, index) in answers.answers" :key="index">
            <p><strong>Pregunta:</strong> {{ answer.question.title }}</p> <!-- Cambié 'text' por 'title' -->
            <p><strong>Respuesta:</strong> 
              <span v-if="Array.isArray(answer.answer)">
                {{ answer.answer.join(', ') }}
              </span>
              <span v-else>
                {{ answer.answer }}
              </span>
            </p>
          </li>
        </ul>
      </div>
      <p v-else>No hay respuestas para este usuario.</p>
    </div>
  </div>
</template>

<script setup>
const route = useRoute()
const formId = route.params.formId
const userId = route.params.userId

const answers = ref(null) // Cambié de [] a null porque inicialmente no tenemos datos.
const isLoading = ref(true)
const error = ref(null)

const fetchAnswers = async (formId, userId) => {
  try {
    const response = await fetch(`http://localhost:8000/api/forms/${formId}/users/${userId}/answers`, {
      method: "GET",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${localStorage.getItem("auth_token")}`, // Ajusta según tu implementación de autenticación
      },
    })

    if (!response.ok) {
      throw new Error(`Error al obtener respuestas: ${response.statusText}`)
    }

    const data = await response.json()
    answers.value = data // Asignamos la respuesta completa
  } catch (err) {
    console.error("Error:", err)
    error.value = err.message
  } finally {
    isLoading.value = false
  }
}

// Llamar a la función cuando se monte el componente
onMounted(() => fetchAnswers(formId, userId))
</script>
