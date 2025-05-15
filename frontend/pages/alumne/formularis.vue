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
          Authorization: `Bearer ${authStore.token}`,
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
  if (formId === 2) {
    navigateTo(`/alumne/cesc/${formId}`); // Redirige a la ruta del cesc si el formId es 2
  } else if (formId === 3) {
    navigateTo(`/alumne/sociograma/${formId}`); // Redirige a la ruta correspondiente para cualquier otro formId
  } else {
    navigateTo(`/alumne/forms/${formId}`); // Redirige a la ruta correspondiente para cualquier otro formId
  }
};
</script>
