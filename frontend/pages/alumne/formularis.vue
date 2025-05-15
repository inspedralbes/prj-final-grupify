<template>
  <div class="space-y-6">
    <h2 class="text-2xl font-bold">Formularis</h2>
    <div v-if="forms.length === 0" class="text-gray-500">
      No tens formularis assignats.
    </div>
    <div v-else>
      <div
        v-for="form in forms"
        :key="form.id"
        class="bg-white rounded-lg shadow p-4 mb-4"
      >
        <h3 class="text-lg font-semibold">{{ form.title }}</h3>
        <p class="text-sm text-gray-500">{{ form.description }}</p>

        <!-- Condicional para cambiar texto, color y deshabilitar el botón -->
        <button
          :class="
            form.answered === 1 ? 'bg-green-300' : 'bg-primary text-white'
          "
          :disabled="form.answered === 1"
          class="mt-4 px-4 py-2 rounded"
          @click="handleFormClick(form.id)"
        >
          {{ form.answered === 1 ? "Completat" : "Completar" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from "~/stores/authStore";

definePageMeta({
  layout: "alumnes",
});
const router = useRouter();
const authStore = useAuthStore();
const forms = ref([]);
const user = authStore.user; // Asegúrate de que el user_id esté almacenado en localStorage o donde sea pertinente.
const userId = user.id;

// Cargar formularios del usuario al montar el componente
const loadFormsByUserId = async userId => {
  try {
    const response = await fetch(
      `http://localhost:8000/api/forms/user/${userId}`,
      {
        method: "GET",
        headers: {
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
          Accept: "application/json",
        },
      }
    );

    if (!response.ok) {
      throw new Error("Error obteniendo los formularios del usuario.");
    }

    forms.value = await response.json();
  } catch (error) {
    console.error("Error al cargar formularios", error);
  }
};

// Cargar los formularios cuando el componente se monta
onMounted(() => {
  loadFormsByUserId(userId);
});

// Manejador del clic en el formulario
const handleFormClick = formId => {
  // Encontrar el formulario por su ID para acceder a su título
  const form = forms.value.find(f => f.id === formId);
  if (form) {
    const title = form.title.toLowerCase();
    if (title.includes('cesc')) {
      navigateTo(`/alumne/cesc/${formId}`); // Redirige a la ruta del CESC
    } else if (title.includes('sociograma') || title.includes('sociomètric')) {
      navigateTo(`/alumne/sociograma/${formId}`); // Redirige a la ruta del sociograma
    } else {
      navigateTo(`/alumne/forms/${formId}`); // Redirige a la ruta para otros formularios
    }
  } else {
    navigateTo(`/alumne/forms/${formId}`); // Fallback si no se encuentra el formulario
  }
};
</script>
