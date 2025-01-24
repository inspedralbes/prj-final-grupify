<script setup>
import { EyeIcon } from "@heroicons/vue/24/outline";

const studentsStore = useStudentsStore();
onMounted(() => {
  // Llamar a la API al montar el componente
  studentsStore.fetchStudents();
});

const router = useRouter();

const viewProfile = studentId => {
  router.push(`/professor/studentProfile/${studentId}`);
};
// Declara la prop 'student' en este componente
defineProps({
  student: {
    type: Object,
    required: true,
  },
});
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
    <td>{{ student.course }}</td>
    <td>{{ student.division }}</td>

    <td>
      <div class="flex space-x-2">
        <!-- Botón con ícono de ojo -->
        <button
          class="p-1 hover:text-primary"
          @click.stop="viewProfile(student.id)"
        >
          <EyeIcon class="w-5 h-5" />
        </button>
      </div>
    </td>
    <!-- Estado -->
    <td>
      <span
        class="px-3 py-1 rounded-full text-white text-sm font-medium"
        :class="student.active ? 'bg-green-500' : 'bg-red-500'"
      >
        {{ student.active ? "Actiu" : "Inactiu" }}
      </span>
    </td>
  </tr>
</template>
