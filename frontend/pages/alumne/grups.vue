<template>
  <div class="space-y-6">
    <h2 class="text-2xl font-bold">Grups</h2>

    <div
      v-for="group in groupStore.groups"
      :key="group.id"
      class="bg-white rounded-lg shadow p-6"
    >
      <!-- Encabezado del grupo -->
      <div class="flex justify-between items-start mb-4">
        <div>
          <h3 class="text-lg font-semibold">{{ group.name }}</h3>
          <p class="text-gray-600">{{ group.description }}</p>
        </div>
        <span
          class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
        >
          {{ group.status || "Actiu" }}
        </span>
      </div>

      <!-- Botón para expandir/ocultar miembros -->
      <div class="mb-4">
        <button
          @click="toggleMembers(group.id)"
          class="flex items-center text-primary hover:text-primary/80"
        >
          <span
            >{{
              expandedGroups.includes(group.id) ? "Ocultar" : "Veure"
            }}
            integrants</span
          >
        </button>
      </div>

      <!-- Miembros del grupo (solo se muestran cuando está expandido) -->
      <div v-if="expandedGroups.includes(group.id)" class="space-y-3">
        <div
          v-for="member in group.members"
          :key="member.id"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center space-x-3">
            <div
              class="w-8 h-8 bg-primary/20 rounded-full flex items-center justify-center text-sm font-medium text-primary"
            >
              {{ member.initials }}
            </div>
            <span>{{ member.name }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "alumnes",
});

import { ref, onMounted } from "vue";
import { useGroupStore } from "@/stores/groupStore"; // Importar el store de Pinia

const groupStore = useGroupStore();

// Definir una propiedad reactiva para controlar la expansión
const expandedGroups = ref([]);

// Función para alternar la visibilidad de los integrantes del grupo
const toggleMembers = groupId => {
  if (expandedGroups.value.includes(groupId)) {
    expandedGroups.value = expandedGroups.value.filter(id => id !== groupId); // Ocultar los integrantes
  } else {
    expandedGroups.value.push(groupId); // Mostrar los integrantes
  }
};

onMounted(() => {
  groupStore.fetchGroups(); // Llamar a la acción que obtiene los grupos
});
</script>
