<script setup>
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupStore } from "@/stores/groupStore";
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue'

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

const toggleSelection = (studentId) => {
  const index = selectedStudents.value.indexOf(studentId);
  if (index > -1) {
    selectedStudents.value.splice(index, 1);
  } else {
    selectedStudents.value.push(studentId);
  }
};

const handleCreateGroup = async () => {
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
    
    <div class="max-w-4xl mx-auto px-4 py-8">
      <div class="bg-white rounded-xl shadow-sm overflow-hidden">
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
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                  class="flex items-center px-4 py-3 hover:bg-gray-50 cursor-pointer"
                  @click="toggleSelection(student.id)"
                >
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900">
                      {{ student.name }} {{ student.last_name }}
                    </h3>
                  </div>
                  <input 
                    type="checkbox" 
                    :checked="selectedStudents.includes(student.id)"
                    class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                  >
                </li>
              </ul>
            </div>
          </div>

          <!-- Botons -->
          <div class="flex justify-between gap-4 pt-6">
            <button 
              @click="goBack"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
            >
              Tornar
            </button>
            <button 
              @click="handleCreateGroup"
              :disabled="!groupName || isLoading"
              class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
            >
              {{ isLoading ? 'Creant...' : 'Crear Grup' }}
            </button>
          </div>

          <!-- Missatges -->
          <div class="mt-4">
            <p v-if="successMessage" class="text-green-600">{{ successMessage }}</p>
            <p v-if="errorMessage" class="text-red-600">{{ errorMessage }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>