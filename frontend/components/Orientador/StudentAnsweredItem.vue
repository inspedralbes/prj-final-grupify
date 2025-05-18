<script setup>
defineProps({
  student: {
    type: Object,
    required: true,
  },
  formId: {
    type: Number,
    required: true,
  },
});

const viewAnswers = (studentId, formId) => {
  navigateTo(`/professor/formularis/${formId}/users/${studentId}/answers`);
};
</script>

<template>
  <tr :key="student.id" class="border-b hover:bg-gray-50">
    <td class="py-4">
      <div class="flex items-center space-x-3">
        <div
          class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold"
        >
          {{
            student.name
              .split(" ")
              .map(n => n[0])
              .join("")
              .toUpperCase()
          }}
        </div>
        <span>{{ student.name }}</span>
        <span>{{ student.last_name }}</span>
      </div>
    </td>
    <td class="py-4">
      <div class="flex items-center">
        <span 
          class="px-2 py-1 rounded-full text-xs font-medium"
          :class="student.answered ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
        >
          {{ student.answered ? 'Contestado' : 'Pendiente' }}
        </span>
      </div>
    </td>
    <td>
      <div class="flex space-x-2">
        <!-- Botón con ícono de ojo -->
        <button
          class="p-1 hover:text-primary flex items-center space-x-1"
          @click="viewAnswers(student.id, formId)"
          :disabled="!student.answered"
          :class="{'opacity-50 cursor-not-allowed': !student.answered, 'text-blue-600 hover:text-blue-800': student.answered}"
        >
          <span>Veure Respostes</span>
          <EyeIcon class="w-5 h-5" />
        </button>
      </div>
    </td>
  </tr>
</template>
