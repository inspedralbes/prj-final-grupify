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

const answeredCount = computed(() => {
  return students.filter(student => student.answered).length;
});

const totalStudents = computed(() => {
  return students.length;
});

const completionPercentage = computed(() => {
  if (totalStudents.value === 0) return 0;
  return Math.round((answeredCount.value / totalStudents.value) * 100);
});
</script>

<template>
  <div class="card">
    <!-- Status summary -->
    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
      <div class="flex justify-between items-center mb-2">
        <h3 class="font-medium text-gray-800">Estat de Respostes</h3>
        <span class="text-sm font-bold">{{ completionPercentage }}%</span>
      </div>
      <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div 
          class="bg-blue-600 h-2.5 rounded-full" 
          :style="`width: ${completionPercentage}%`"
        ></div>
      </div>
      <div class="mt-2 text-sm text-gray-600">
        {{ answeredCount }} de {{ totalStudents }} estudiants han contestat
      </div>
    </div>
    
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b">
            <th class="text-left py-3">Nom</th>
            <th class="text-left py-3">Estat</th>
            <th class="text-left py-3">Accions</th>
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
