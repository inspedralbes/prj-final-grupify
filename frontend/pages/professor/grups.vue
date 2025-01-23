<script setup>
import { useStudentsStore } from "@/stores/studentsStore";
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue'

const studentsStore = useStudentsStore();
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
  if (selectedStudents.value.includes(studentId)) {
    selectedStudents.value = selectedStudents.value.filter(id => id !== studentId);
  } else {
    selectedStudents.value.push(studentId);
  }
};

const addStudentsToGroup = async (groupId, studentIds) => {
  await fetch(`http://localhost:8000/api/groups/${groupId}/addStudentsToGroup`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      accept: "application/json",
    },
    body: JSON.stringify({ student_ids: studentIds }),
  });
};

const handleCreateGroup = async () => {
  if (isLoading.value) return;
  isLoading.value = true;
  
  try {
    const selectedStudentsDetails = students.value.filter(student =>
      selectedStudents.value.includes(student.id)
    );

    const groupData = {
      name: groupName.value,
      description: groupDescription.value,
      number_of_students: selectedStudentsDetails.length,
    };

    const response = await fetch("http://localhost:8000/api/groups", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        accept: "application/json",
      },
      body: JSON.stringify(groupData),
    });

    const data = await response.json();
    await addStudentsToGroup(data.id, selectedStudents.value);
    successMessage.value = "Grup creat amb èxit";

    setTimeout(() => {
      navigateTo("/professor/dashboard");
    }, 2000);
  } catch (error) {
    errorMessage.value = "Hi ha hagut un error al crear el grup";
  } finally {
    isLoading.value = false;
  }
};

const goBack = () => {
  navigateTo("/professor/dashboard");
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />
    
    <div class="max-w-4xl mx-auto px-4 py-8">
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
          <h1 class="text-2xl font-bold text-gray-900">Crear Nou Grup</h1>
        </div>

        <div class="p-6 space-y-6">
          <!-- Group Info -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Nom del Grup
              </label>
              <input
                v-model="groupName"
                type="text"
                placeholder="Introdueix el nom del grup"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Descripció del Grup
              </label>
              <textarea
                v-model="groupDescription"
                placeholder="Descripció (opcional)"
                rows="4"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              ></textarea>
            </div>
          </div>

          <!-- Students Selection -->
          <div>
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-lg font-medium text-gray-900">
                Selecciona els estudiants
              </h2>
              <span class="text-sm text-gray-500">
                {{ selectedStudents.length }} seleccionats
              </span>
            </div>

            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <ul class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                <li
                  v-for="student in students"
                  :key="student.id"
                  class="flex items-center px-4 py-3 hover:bg-gray-50 transition-colors"
                >
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900">
                      {{ student.name }} {{ student.last_name }}
                    </h3>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input
                      type="checkbox"
                      :value="student.id"
                      :checked="selectedStudents.includes(student.id)"
                      @change="toggleSelection(student.id)"
                      class="sr-only peer"
                    />
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
          <div class="flex justify-between gap-4">
            <button 
              @click="goBack"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
              Cancelar
            </button>
            <button
              @click="handleCreateGroup"
              :disabled="!groupName || selectedStudents.length === 0 || isLoading"
              class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ isLoading ? 'Creant...' : 'Crear Grup' }}
            </button>
          </div>

          <div class="mt-4">
            <p v-if="successMessage" class="text-sm text-green-600">
              {{ successMessage }}
            </p>
            <p v-if="errorMessage" class="text-sm text-red-600">
              {{ errorMessage }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div
      v-if="isLoading"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 flex flex-col items-center">
        <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        <p class="mt-4 text-gray-600">Creant grup...</p>
      </div>
    </div>
  </div>
</template>