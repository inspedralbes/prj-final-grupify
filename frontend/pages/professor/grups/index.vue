<script setup>
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupStore } from "@/stores/groupStore";
import { useGroupSearch } from "@/composables/useGroupSearch";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const studentsStore = useStudentsStore();
const groupStore = useGroupStore();

const selectedStudents = ref([]);
const isLoading = ref(false);
const errorMessage = ref("");
const successMessage = ref("");
const selectedStatus = ref("all");
const selectedDate = ref("all");

const goToGroup = groupId => {
  navigateTo(`/professor/grups/${groupId}`);
};

onMounted(() => {
  studentsStore.fetchStudents();
  groupStore.fetchGroups();
});

const students = computed(() => studentsStore.students);
const groups = computed(() => groupStore.groups);
const { searchQuery, filteredGroups } = useGroupSearch(groups);

const handleDeleteGroup = async groupId => {
  try {
    await groupStore.deleteGroup(groupId);
    successMessage.value = "Grup eliminat correctament";
    setTimeout(() => {
      successMessage.value = "";
    }, 3000);
  } catch (error) {
    errorMessage.value = "Hi ha hagut un error al eliminar el grup";
    setTimeout(() => {
      errorMessage.value = "";
    }, 3000);
  }
};

const handleAddStudentToGroup = async (groupId, studentId) => {
  try {
    await groupStore.addStudentsToGroup(groupId, studentId);
    successMessage.value = "Alumne afegit al grup amb èxit";
    setTimeout(() => {
      successMessage.value = "";
    }, 3000);
  } catch (error) {
    errorMessage.value = "Hi ha hagut un error al afegir l'alumne al grup";
    setTimeout(() => {
      errorMessage.value = "";
    }, 3000);
  }
};

const handleRemoveStudentFromGroup = async (groupId, studentId) => {
  try {
    await groupStore.removeStudentFromGroup(groupId, studentId);
    successMessage.value = "Alumne eliminat del grup amb èxit";
    setTimeout(() => {
      successMessage.value = "";
    }, 3000);
  } catch (error) {
    errorMessage.value = "Hi ha hagut un error al eliminar l'alumne del grup";
    setTimeout(() => {
      errorMessage.value = "";
    }, 3000);
  }
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header Section -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Grups</h1>
          <p class="mt-1 text-sm text-gray-500">
            Gestiona els grups i els seus membres
          </p>
        </div>
        <button
          @click="navigateTo('/professor/nouGrups')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
        >
          <span class="w-5 h-5 mr-2">+</span>
          Nou Grup
        </button>
      </div>

      <!-- Mensajes de éxito/error -->
      <div v-if="successMessage || errorMessage" class="mb-4">
        <p
          v-if="successMessage"
          class="bg-green-100 text-green-800 p-3 rounded-lg"
        >
          {{ successMessage }}
        </p>
        <p v-if="errorMessage" class="bg-red-100 text-red-800 p-3 rounded-lg">
          {{ errorMessage }}
        </p>
      </div>

      <!-- Filters Section -->
      <div class="bg-white rounded-xl shadow-sm mb-6">
        <div
          class="p-4 space-y-4 md:space-y-0 md:flex md:items-center md:space-x-4"
        >
          <div class="flex-1">
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar grups..."
                class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <svg
                class="w-5 h-5 text-gray-400 absolute left-3 top-2.5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </div>
          </div>
          <div class="flex flex-col md:flex-row gap-4">
            <select
              v-model="selectedStatus"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">Tots los estats</option>
              <option value="active">Actius</option>
              <option value="inactive">Inactius</option>
            </select>
            <select
              v-model="selectedDate"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="all">Totes les dates</option>
              <option value="today">Avui</option>
              <option value="week">Aquesta setmana</option>
              <option value="month">Aquest mes</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Títol
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Estat
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Membres
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Data
                </th>
                <th
                  class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Accions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="group in filteredGroups"
                :key="group.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ group.name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ group.description }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                  >
                    actiu
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ group.members?.length || 0 }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  22/1/2025 (A CAMBIAR)
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                  <div class="flex justify-end space-x-3">
                    <button
                      @click="goToGroup(group.id)"
                      class="flex items-center space-x-1 px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                    >
                      <span>Gestionar</span>
                    </button>

                    <button
                      @click="handleDeleteGroup(group.id)"
                      class="p-2 text-red-500 hover:text-red-700 transition-colors"
                    >
                      <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                        />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>
