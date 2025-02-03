<script setup>
import { useGroupStore } from "@/stores/groupStore"; // Correcta importación del store

// Usar store para grupos
const groupsStore = useGroupStore();

// Llamar a la API al montar el componente
onMounted(() => {
  groupsStore.fetchGroups(); // Cargar los grupos
});

// Obtener los grupos desde el store
const groups = computed(() => groupsStore.groups); // Usar la propiedad 'groups' del store
</script>

<template>
  <div class="card">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b">
            <th class="text-left py-3">Nom del grup</th>
            <th class="text-left py-3">Descripció</th>
            <th class="text-left py-3">Num de Alumnes</th>
            <th class="text-left py-3">Fitxa</th>
          </tr>
        </thead>
        <tbody>
          <TeacherGroupListItem
            v-for="group in groups"
            :key="group.id"
            :group="group"
          />
        </tbody>
      </table>
    </div>
    <div v-if="groups.length === 0" class="text-center py-8 text-gray-500">
      No s'han trobat grups amb els filtres seleccionats
    </div>
  </div>
</template>
