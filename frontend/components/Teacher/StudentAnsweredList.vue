<script setup>
import StudentAnsweredItem from "@/components/Teacher/StudentAnsweredItem.vue";
defineProps({
  students: {
    type: Array,
    required: true,
  },
  formId: {
    type: Number,
    required: true,
  },
});
</script>

<template>
  <div class="card">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b">
            <th class="text-left py-3">Nom</th>
            <th class="text-left py-3">Respostes</th>
          </tr>
        </thead>
        <tbody>
          <StudentAnsweredItem
            v-for="student in students"
            :key="student.id"
            :student="student"
            :formId="formId"
          />
        </tbody>
      </table>
    </div>
    <div v-if="students.length === 0" class="text-center py-8 text-gray-500">
      No s'han trobat estudiants amb els filtres seleccionats
    </div>
  </div>
</template>
