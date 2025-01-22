<script setup>
import { EyeIcon } from "@heroicons/vue/24/outline"; // Icono de ojo para ver el perfil del grupo
import { useGroupsStore } from "@/stores/groupsStore"; // Usar el store de grupos

const groupsStore = useGroupsStore();

// Llamar a la API al montar el componente
onMounted(() => {
  groupsStore.fetchGroups();
});

const router = useRouter();

// Función para redirigir al perfil del grupo
const viewGroupProfile = groupId => {
  router.push({ name: "GroupProfile", params: { id: groupId } });
};

// Declara la prop 'group' en este componente
defineProps({
  group: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <tr :key="group.id" class="border-b hover:bg-gray-50">
    <td class="py-4">
      <div class="flex items-center space-x-3">
        <div
          class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold"
        >
          {{
            group.name
              .split(" ")
              .map(n => n[0])
              .join("")
              .toUpperCase()
          }}
        </div>
        <span>{{ group.name }}</span>
      </div>
    </td>
    <td>{{ group.number_of_students }}</td>
    <td>{{ group.description || 'No description available' }}</td>

    <td>
      <div class="flex space-x-2">
        <!-- Botón con ícono de ojo -->
        <button
          class="p-1 hover:text-primary"
          @click.stop="viewGroupProfile(group.id)"
        >
          <EyeIcon class="w-5 h-5" />
        </button>
      </div>
    </td>
  </tr>
</template>
