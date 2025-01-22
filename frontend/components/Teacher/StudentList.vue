<script setup>
// Usar store
const studentsStore = useStudentsStore();

// Llamar a la API al montar el componente
onMounted(() => {
  studentsStore.fetchStudents();
});

// Declara la prop 'student' en este componente
defineProps({
  students: {
    type: Object,
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
            <th class="text-left py-3">Curs</th>
            <th class="text-left py-3">Classe</th>
            <th class="text-left py-3">Fitxa</th>
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
    <div v-if="students.length === 0" class="text-center py-8 text-gray-500">
      No s'han trobat estudiants amb els filtres seleccionats
    </div>
  </div>
</template>
