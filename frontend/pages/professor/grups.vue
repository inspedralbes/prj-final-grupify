<script setup>
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupStore } from "@/stores/groupStore";
import { useGroupSearch } from "@/composables/useGroupSearch"; 
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
  groupStore.fetchGroups();
});

const students = computed(() => studentsStore.students);
const groups = computed(() => groupStore.groups);
const { searchQuery, filteredGroups } = useGroupSearch(groups); 

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
    isLoading.value = false;
  }
};

const goBack = () => {
  navigateTo("/professor/dashboard");
};

// Función para agregar un estudiante a un grupo
const handleAddStudentToGroup = async (groupId, studentId) => {
  try {
    await groupStore.addStudentToGroup(groupId, studentId);
    successMessage.value = "Alumne afegit al grup amb èxit";
  } catch (error) {
    errorMessage.value = "Hi ha hagut un error al afegir l'alumne al grup";
  }
};

// Función para eliminar un estudiante de un grupo
const handleRemoveStudentFromGroup = async (groupId, studentId) => {
  try {
    await groupStore.removeStudentFromGroup(groupId, studentId);
    successMessage.value = "Alumne eliminat del grup amb èxit";
  } catch (error) {
    errorMessage.value = "Hi ha hagut un error al eliminar l'alumne del grup";
  }
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
  <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow mt-12 mb-12">
    <h1 class="text-2xl font-bold mb-4">Seleccionar Alumnes per al Grup</h1>

    <!-- Campo de búsqueda para grupos -->
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">Cercar Grups</label>
      <input v-model="searchQuery" type="text" placeholder="Cercar per nom del grup" class="input w-full" />
    </div>

    <!-- Lista de grupos filtrados -->
    <div class="mb-6">
      <h2 class="text-lg font-medium mb-2">Grups disponibles:</h2>
      <ul class="divide-y divide-gray-200">
        <li v-for="group in filteredGroups" :key="group.id" class="py-4 flex flex-col space-y-2">
          <div>
            <h2 class="text-lg font-medium">{{ group.name }}</h2>
            <p class="text-gray-600">{{ group.description }}</p>
            <p class="text-gray-600">Alumnes: {{ group.members ? group.members.length : 0 }}</p>
          </div>
          <!-- Mostrar la lista de miembros -->
          <div v-if="group.members && group.members.length > 0">
            <h3 class="text-md font-medium">Alumnes del grup:</h3>
            <ul class="pl-4">
              <li v-for="member in group.members" :key="member.id" class="text-gray-600">
                {{ member.name }} {{ member.last_name }}
                <button @click="handleRemoveStudentFromGroup(group.id, member.id)" class="text-red-500 ml-2">
                  Eliminar
                </button>
              </li>
            </ul>
          </div>
          <div v-else>
            <p class="text-gray-600">No hi ha alumnes en aquest grup.</p>
          </div>
          <!-- Botón para agregar estudiantes al grupo -->
          <div>
            <select v-model="selectedStudents" class="input w-full" multiple 
              >
              <option v-for="student in students" :key="student.id" :value="student.id">
                {{ student.name }} {{ student.last_name }}
              </option>
            </select>
            <button @click="handleAddStudentToGroup(group.id, selectedStudents)" class="btn btn-primary mt-2">
              Afegir Alumne
            </button>
          </div>
        </li>
      </ul>
    </div>

    <!-- Resto del formulario para crear grupos -->
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">Nom del Grup</label>
      <input v-model="groupName" type="text" placeholder="Ingrese el nombre del grupo" class="input w-full" />
    </div>
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">Descripció del Grup</label>
      <textarea v-model="groupDescription" placeholder="Ingrese una descripción (opcional)" class="input w-full"
        rows="4"></textarea>
    </div>
    <div>
      <h2 class="text-lg font-medium mb-2">Selecciona els estudiants:</h2>
      <ul class="divide-y divide-gray-200">
        <li v-for="student in students" :key="student.id" class="py-4 flex items-center justify-between">
          <div>
            <h2 class="text-lg font-medium">
              {{ student.name }} {{ student.last_name }}
            </h2>
          </div>
          <div>
            <input type="checkbox" :value="student.id" :checked="selectedStudents.includes(student.id)"
              class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
              @change="toggleSelection(student.id)" />
          </div>
        </li>
      </ul>
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