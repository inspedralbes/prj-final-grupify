<script setup>
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupStore } from "@/stores/groupStore";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const authStore = useAuthStore();

const studentsStore = useStudentsStore();
const groupStore = useGroupStore();

const selectedStudents = ref([]);
const isLoading = ref(false);
const groupName = ref("");
const groupDescription = ref("");
const errorMessage = ref("");
const successMessage = ref("");

onMounted(() => {
  studentsStore.fetchStudents();
});

const students = computed(() => studentsStore.students);

const toggleSelection = studentId => {
  const index = selectedStudents.value.indexOf(studentId);
  if (index > -1) {
    selectedStudents.value.splice(index, 1);
  } else {
    selectedStudents.value.push(studentId);
  }
};

const handleCreateGroup = async () => {

  if (!authStore.isAuthenticated) {
    errorMessage.value = "Debes iniciar sesión";
    return;
  }

  if (isLoading.value) return;
  isLoading.value = true;

  try {
    const groupData = {
      name: groupName.value,
      description: groupDescription.value,
      number_of_students: selectedStudents.value.length,
    };

    const response = await groupStore.createGroup(groupData);

    if (response.id) {
      await groupStore.addStudentsToGroup(response.id, selectedStudents.value);
      successMessage.value = "Grup creat amb èxit!";
      setTimeout(() => navigateTo("/professor/grups"), 2000);
    }
  } catch (error) {
    errorMessage.value = "Hi ha hagut un error al crear el grup";
  } finally {
    isLoading.value = false;
  }
};

const goBack = () => {
  navigateTo("/professor/grups");
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />

    <div class="max-w-6xl mx-auto px-4 py-8">
      <!-- Header Section with Back Button -->
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Crear Nou Grup</h1>
        <button @click="goBack"
          class="flex items-center gap-2 px-4 py-2 rounded-lg text-[rgb(0,173,238)] hover:bg-[rgba(0,173,238,0.1)] transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Tornar
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Nom del Grup
              </label>
              <input v-model="groupName" type="text" placeholder="Introdueix el nom del grup"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-[rgb(0,173,238)] focus:border-[rgb(0,173,238)]" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Descripció del Grup
              </label>
              <textarea v-model="groupDescription" placeholder="Descripció (opcional)" rows="4"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-[rgb(0,173,238)] focus:border-[rgb(0,173,238)]"></textarea>
            </div>

            <!-- Create Button -->
            <button @click="handleCreateGroup" :disabled="!groupName || isLoading"
              class="w-full px-6 py-3 rounded-lg bg-[rgb(0,173,238)] text-white hover:bg-[rgb(0,153,218)] disabled:opacity-50 transition-colors font-medium">
              {{ isLoading ? "Creant..." : "Crear Grup" }}
            </button>

            <!-- Messages -->
            <div>
              <p v-if="successMessage" class="px-4 py-2 bg-green-50 text-green-700 rounded-lg">
                {{ successMessage }}
              </p>
              <p v-if="errorMessage" class="px-4 py-2 bg-red-50 text-red-700 rounded-lg">
                {{ errorMessage }}
              </p>
            </div>
          </div>
        </div>

        <!-- Students Selection Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Estudiants</h2>
            <span class="px-4 py-1.5 bg-[rgba(0,173,238,0.1)] text-[rgb(0,173,238)] rounded-full text-sm font-medium">
              {{ selectedStudents.length }} seleccionats
            </span>
          </div>

          <div class="space-y-3 max-h-[600px] overflow-y-auto pr-2 -mr-2">
            <div v-for="student in students" :key="student.id" class="relative">
              <button @click="toggleSelection(student.id)" :class="[
                'w-full group flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200',
                selectedStudents.includes(student.id)
                  ? 'bg-[rgba(0,173,238,0.1)] border-[rgb(0,173,238)] shadow-sm'
                  : 'bg-gray-50 hover:bg-[rgba(0,173,238,0.05)]',
              ]">
                <div class="flex items-center gap-3">
                  <div :class="[
                    'w-8 h-8 rounded-full flex items-center justify-center border-2 transition-colors',
                    selectedStudents.includes(student.id)
                      ? 'border-[rgb(0,173,238)] bg-[rgb(0,173,238)]'
                      : 'border-gray-300 group-hover:border-[rgb(0,173,238)]',
                  ]">
                    <svg v-if="selectedStudents.includes(student.id)" xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-medium" :class="[
                      selectedStudents.includes(student.id)
                        ? 'text-[rgb(0,173,238)]'
                        : 'text-gray-700',
                    ]">
                      {{ student.name }} {{ student.last_name }}
                    </h3>
                  </div>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
