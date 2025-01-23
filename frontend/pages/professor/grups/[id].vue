<script setup>
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupStore } from "@/stores/groupStore";
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue'

const route = useRoute();
const studentsStore = useStudentsStore();
const groupStore = useGroupStore();

const selectedStudent = ref("");
const isLoading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");

onMounted(async () => {
  await Promise.all([
    studentsStore.fetchStudents(),
    groupStore.fetchGroups()
  ]);
});

const students = computed(() => studentsStore.students);
const group = computed(() => 
  groupStore.groups.find(g => g.id === parseInt(route.params.id))
);

const availableStudents = computed(() => {
  const memberIds = group.value?.members?.map(m => m.id) || [];
  return students.value.filter(student => !memberIds.includes(student.id));
});

const handleAddStudent = async () => {
  if (!selectedStudent.value) return;
  
  try {
    isLoading.value = true;
    await groupStore.addStudentsToGroup(group.value.id, [parseInt(selectedStudent.value)]);
    successMessage.value = "Alumne afegit al grup amb èxit";
    selectedStudent.value = "";
    await groupStore.fetchGroups(); // Recargar los grupos para actualizar la vista
  } catch (error) {
    errorMessage.value = "Hi ha hagut un error al afegir l'alumne al grup";
  } finally {
    isLoading.value = false;
    setTimeout(() => {
      successMessage.value = "";
      errorMessage.value = "";
    }, 3000);
  }
};

const handleRemoveStudent = async (studentId) => {
  try {
    isLoading.value = true;
    await groupStore.removeStudentFromGroup(group.value.id, studentId);
    successMessage.value = "Alumne eliminat del grup amb èxit";
    await groupStore.fetchGroups(); // Recargar los grupos para actualizar la vista
  } catch (error) {
    errorMessage.value = "Hi ha hagut un error al eliminar l'alumne del grup";
  } finally {
    isLoading.value = false;
    setTimeout(() => {
      successMessage.value = "";
      errorMessage.value = "";
    }, 3000);
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />
    
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ group?.name }}</h1>
          <p class="mt-1 text-sm text-gray-500">{{ group?.description }}</p>
        </div>
        <NuxtLink
          to="/professor/grups"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
        >
          Tornar
        </NuxtLink>
      </div>

      <!-- Sección para añadir nuevos alumnos -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Afegir nou alumne</h2>
        <div class="flex gap-4">
          <select
            v-model="selectedStudent"
            class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-blue-500"
            :disabled="isLoading"
          >
            <option value="">Selecciona un alumne</option>
            <option
              v-for="student in availableStudents"
              :key="student.id"
              :value="student.id"
            >
              {{ student.name }} {{ student.last_name }}
            </option>
          </select>
          <button
            @click="handleAddStudent"
            :disabled="!selectedStudent || isLoading"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            {{ isLoading ? 'Afegint...' : 'Afegir' }}
          </button>
        </div>
      </div>

      <!-- Mensajes de estado -->
      <div v-if="successMessage || errorMessage" class="mb-6">
        <p v-if="successMessage" class="bg-green-100 text-green-800 p-3 rounded-lg">{{ successMessage }}</p>
        <p v-if="errorMessage" class="bg-red-100 text-red-800 p-3 rounded-lg">{{ errorMessage }}</p>
      </div>

      <!-- Listado de miembros -->
      <div class="bg-white rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-6">Membres del grup</h2>
        <div class="space-y-3">
          <div
            v-for="member in group?.members"
            :key="member.id"
            class="bg-gray-50 rounded-lg p-4 flex items-center justify-between"
          >
            <span class="text-sm font-medium text-gray-900">
              {{ member.name }} {{ member.last_name }}
            </span>
            <button
              @click="handleRemoveStudent(member.id)"
              :disabled="isLoading"
              class="text-sm text-red-600 hover:text-red-800 disabled:opacity-50"
            >
              {{ isLoading ? 'Eliminant...' : 'Eliminar' }}
            </button>
          </div>
          <p v-if="!group?.members?.length" class="text-gray-500 text-center py-4">
            No hi ha membres en aquest grup
          </p>
        </div>
      </div>
    </main>
  </div>
</template>