<template>
  <tr :key="group.id" class="border-b hover:bg-gray-50">
    <td class="py-3 px-4">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
          {{ group.name.split(" ").map(n => n[0]).join("").toUpperCase() }}
        </div>
        <span class="text-xs sm:text-sm">{{ group.name }}</span>
      </div>
    </td>
    <td class="py-3 px-4 text-center text-xs sm:text-sm">
      {{ group.number_of_students }}
    </td>
    <td class="py-3 px-4 text-xs sm:text-sm">
      {{ group.description || "No description available" }}
    </td>
    <td class="py-3 px-4 text-center">
      <button class="p-1 hover:text-primary" @click.stop="viewGroupProfile(group.id)" title="Veure perfil">
        <EyeIcon class="w-5 h-5" />
      </button>
    </td>
  </tr>
</template>

<script setup>
import { onMounted } from "vue";
import { EyeIcon } from "@heroicons/vue/24/outline";
import { useGroupsStore } from "@/stores/groupsStore";
import { useRouter } from "vue-router";

const groupsStore = useGroupsStore();
const router = useRouter();

onMounted(() => {
  groupsStore.fetchGroups();
});

const viewGroupProfile = groupId => {
  router.push({ name: "GroupProfile", params: { id: groupId } });
};

defineProps({
  group: {
    type: Object,
    required: true,
  },
});
</script>
