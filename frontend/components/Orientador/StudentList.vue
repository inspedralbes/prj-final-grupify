<template>
  <div class="card">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b">
            <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">Nom</th>
            <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">Estat</th>
            <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">Curs</th>
            <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">Classe</th>
            <th class="text-right py-3 px-4 text-xs text-gray-500 uppercase">Fitxa</th>
          </tr>
        </thead>
        <tbody>
          <TeacherStudentListItem
            v-for="student in students"
            :key="student.id"
            :student="student"
          />
        </tbody>
      </table>
    </div>
    <div v-if="students.length === 0" class="text-center py-6 text-gray-500">
      No s'han trobat estudiants amb els filtres seleccionats
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useStudentsStore } from "@/stores/studentsStore";

const studentsStore = useStudentsStore();

onMounted(() => {
  studentsStore.fetchStudents();
});

defineProps({
  students: {
    type: Array,
    required: true,
  },
});
</script>
