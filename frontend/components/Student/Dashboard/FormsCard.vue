<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <h3 class="text-xl font-semibold mb-6 flex items-center">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        class="h-6 w-6 text-primary mr-2"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
        />
      </svg>
      Formularis Pendents
    </h3>
    <div class="space-y-4">
      <div v-if="filteredForms.length === 0" class="text-center text-gray-500">
        No hi ha formularis pendents.
      </div>
      <div v-else>
        <div
          v-for="form in filteredForms"
          :key="form.id"
          class="p-4 bg-gray-50 rounded-lg hover:bg-primary/5 transition-all cursor-pointer mb-4"
        >
          <div class="flex items-center justify-between">
            <div>
              <h4 class="font-semibold text-gray-900">{{ form.title }}</h4>
              <p class="text-sm text-gray-500">{{ form.description }}</p>
            </div>
            <span
              class="px-3 py-1 text-xs font-medium rounded-full"
              :class="form.urgencyColor"
            >
              {{ form.urgency }}
            </span>
          </div>
          <div class="mt-3 flex justify-between items-center">
            <button
              :class="
                form.answered === 1 ? 'bg-green-300' : 'bg-primary text-white'
              "
              :disabled="form.answered === 1"
              class="mt-4 px-4 py-2 rounded"
              @click="handleFormClick(form.id)"
            >
              {{ form.answered === 1 ? "Completado" : "Completar" }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const forms = ref([]);
const userId = computed(() => authStore.user?.id);

const loadFormsByUserId = async () => {
  if (!userId.value) return;
  
  try {
    const response = await $fetch(`http://localhost:8000/api/forms/user/${userId.value}`, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
      }
    });
    
    forms.value = response;
  } catch (error) {
    console.error("Error obteniendo formularios:", error);
  }
};

// Observar cambios en el userId
watch(userId, (newVal) => {
  if (newVal) loadFormsByUserId();
});

// Cargar inicialmente
onMounted(() => {
  if (userId.value) loadFormsByUserId();
});

// Filtrar solo los formularios con answered === 0
const filteredForms = computed(() => {
  return forms.value.filter(form => form.answered === 0);
});

// Manejador del clic en el formulario
const handleFormClick = formId => {
  if (formId === 2) {
    navigateTo(`/alumne/cesc/${formId}`); // Redirige a la ruta del cesc si el formId es 2
  } else if (formId === 3) {
    navigateTo(`/alumne/sociograma`); // Redirige a /sociogram si el formId es 3
  } else {
    navigateTo(`/alumne/forms/${formId}`); // Redirige a la ruta correspondiente para cualquier otro formId
  }
};
</script>
