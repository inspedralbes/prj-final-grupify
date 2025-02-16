<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />

    <!-- Modal de confirmación (responsive) -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Fondo difuminado -->
      <div class="fixed inset-0 bg-white/50 backdrop-blur-sm" @click.self="showDeleteModal = false"></div>
      <!-- Contenido del modal -->
      <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl border border-gray-200">
        <h3 class="text-lg font-semibold mb-4 text-gray-900">Confirmar eliminació</h3>
        <p class="mb-6 text-gray-600">
          Estàs segur que vols eliminar el grup <strong>"{{ modalTitle }}"</strong>?
        </p>
        <div class="flex justify-end space-x-3">
          <button @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors font-medium">
            Cancel·lar
          </button>
          <button @click="handleDeleteGroup"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
            Confirmar
          </button>
        </div>
      </div>
    </div>

    <main class="container mx-auto px-4 py-6 sm:py-8">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Grups</h1>
          <p class="mt-1 text-sm text-gray-500">Gestiona els grups i els seus membres</p>
        </div>
        <button @click="navigateTo('/professor/nouGrups')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
          <span class="text-lg font-bold mr-2">+</span>
          Nou Grup
        </button>
      </div>

      <!-- Mensajes de éxito/error -->
      <div v-if="successMessage || errorMessage" class="mb-4">
        <p v-if="successMessage" class="bg-green-100 text-green-800 p-3 rounded-lg">
          {{ successMessage }}
        </p>
        <p v-if="errorMessage" class="bg-red-100 text-red-800 p-3 rounded-lg">
          {{ errorMessage }}
        </p>
      </div>

      <!-- Filters Section -->
      <div class="bg-white rounded-xl shadow-sm mb-6">
        <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-4 sm:space-y-0">
          <!-- Input de búsqueda -->
          <div class="flex-1 relative">
            <input v-model="searchQuery" type="text" placeholder="Buscar grups..."
              class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <!-- Filtros de estado y fecha -->
          <div class="flex flex-col sm:flex-row gap-4">
            <select v-model="selectedStatus"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="all">Tots els estats</option>
              <option value="active">Actius</option>
              <option value="inactive">Inactius</option>
            </select>
            <select v-model="selectedDate"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="all">Totes les dates</option>
              <option value="today">Avui</option>
              <option value="week">Aquesta setmana</option>
              <option value="month">Aquest mes</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table Section (con scroll horizontal en móviles) -->
      <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                Títol
              </th>
              <th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                Estat
              </th>
              <th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                Membres
              </th>
              <th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                Data
              </th>
              <th class="px-4 py-2 text-right text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                Accions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="group in filteredGroups" :key="group.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">
                  {{ group.name }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ group.description }}
                </div>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 inline-flex text-xs sm:text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                  actiu
                </span>
              </td>
              <td class="px-4 py-3 text-xs sm:text-sm text-gray-500">
                {{ group.users?.length || group.number_of_students || 0 }}
              </td>
              <td class="px-4 py-3 text-xs sm:text-sm text-gray-500">
                {{ group.created_at ? new Date(group.created_at).toLocaleDateString('ca-ES', {
                  day: '2-digit',
                  month: '2-digit',
                  year: 'numeric'
                }) : '-' }}
              </td>
              <td class="px-4 py-3 text-right text-xs sm:text-sm font-medium">
                <div class="flex justify-end space-x-3">
                  <button @click="goToGroup(group.id)"
                    class="flex items-center space-x-1 px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                    <span class="hidden sm:inline">Gestionar</span>
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7" />
                    </svg>
                  </button>
                  <button @click="confirmDelete(group.id, group.name)"
                    class="p-2 text-red-500 hover:text-red-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupStore } from "@/stores/groupStore";
import { useGroupSearch } from "@/composables/useGroupSearch";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const studentsStore = useStudentsStore();
const groupStore = useGroupStore();

const errorMessage = ref("");
const successMessage = ref("");
const selectedStatus = ref("all");
const selectedDate = ref("all");
const showDeleteModal = ref(false);
const groupToDelete = ref(null);
const modalTitle = ref("");

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

const confirmDelete = (groupId, groupName) => {
  groupToDelete.value = groupId;
  modalTitle.value = groupName;
  showDeleteModal.value = true;
};

const handleDeleteGroup = async () => {
  try {
    await groupStore.deleteGroup(groupToDelete.value);
    successMessage.value = "Grup eliminat correctament";
    showDeleteModal.value = false;
    setTimeout(() => {
      successMessage.value = "";
    }, 3000);
  } catch (error) {
    errorMessage.value = "Hi ha hagut un error al eliminar el grup";
    showDeleteModal.value = false;
    setTimeout(() => {
      errorMessage.value = "";
    }, 3000);
  }
};
</script>
