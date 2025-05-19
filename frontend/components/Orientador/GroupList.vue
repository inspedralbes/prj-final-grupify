<template>
  <div class="card">
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead>
          <tr class="border-b">
            <th class="px-4 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase">Nom del grup</th>
            <th class="px-4 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase">Descripció</th>
            <th class="px-4 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase">Num de Alumnes</th>
            <th class="px-4 py-3 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase">Fitxa</th>
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

<script setup>
import { onMounted, computed } from "vue";
import { useGroupStore } from "@/stores/groupStore";

const groupsStore = useGroupStore();

onMounted(() => {
  groupsStore.fetchGroups();
});

const groups = computed(() => groupsStore.groups);
</script>
