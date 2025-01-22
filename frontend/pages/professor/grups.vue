<script setup>
import { ref, computed, onMounted } from "vue";
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupsStore } from "@/stores/groupStore";
import { useGroupSearch } from "@/composables/useGroupSearch"; // Asegúrate de crear este composable

const studentsStore = useStudentsStore();
const groupsStore = useGroupsStore();

// Estados reactivos
const selectedStudents = ref([]);
const groupName = ref("");
const groupDescription = ref("");
const isLoading = ref(false);
const successMessage = ref("");
const errorMessage = ref("");

// Obtener datos iniciales
onMounted(async () => {
  await studentsStore.fetchStudents();
  await groupsStore.fetchGroups();
});

// Computed properties
const students = computed(() => studentsStore.students);
const groups = computed(() => groupsStore.groups);

// Sistema de búsqueda para grupos (modificado)
const { searchQuery, filteredGroups } = useGroupSearch(groups);

// Lógica de selección de estudiantes
const toggleSelection = (studentId) => {
  const index = selectedStudents.value.indexOf(studentId);
  if (index > -1) {
    selectedStudents.value.splice(index, 1);
  } else {
    selectedStudents.value.push(studentId);
  }
};

// Crear grupo
const handleCreateGroup = async () => {
  if (isLoading.value) return;
  isLoading.value = true;

  try {
    const groupData = {
      name: groupName.value,
      description: groupDescription.value,
      number_of_students: selectedStudents.value.length,
    };

    await groupsStore.createGroup(groupData, selectedStudents.value);
    
    successMessage.value = "Grupo creado con éxito.";
    groupName.value = "";
    groupDescription.value = "";
    selectedStudents.value = [];
    
    // Actualizar lista de grupos
    await groupsStore.fetchGroups();
  } catch (error) {
    errorMessage.value = error.message;
  } finally {
    isLoading.value = false;
  }
};

const goBack = () => navigateTo("/professor/dashboard");
</script>

<template>
  <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow mt-12 mb-12">
    <h1 class="text-2xl font-bold mb-4">Seleccionar Alumnes per al Grup</h1>

    <!-- Formulario de creación -->
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">Nom del Grup</label>
      <input v-model="groupName" type="text" placeholder="Ingrese el nombre del grupo" class="input w-full" />
    </div>
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">Descripció del Grup</label>
      <textarea
        v-model="groupDescription"
        placeholder="Ingrese una descripción (opcional)"
        class="input w-full"
        rows="4"
      ></textarea>
    </div>

    <!-- Lista de estudiantes -->
    <h2 class="text-lg font-medium mb-2">Selecciona els estudiants:</h2>
    <ul class="divide-y divide-gray-200">
      <li
        v-for="student in students"
        :key="student.id"
        class="py-4 flex items-center justify-between"
      >
        <div>
          <h2 class="text-lg font-medium">{{ student.name }} {{ student.last_name }}</h2>
        </div>
        <div>
          <input
            type="checkbox"
            :value="student.id"
            :checked="selectedStudents.includes(student.id)"
            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
            @change="toggleSelection(student.id)"
          />
        </div>
      </li>
    </ul>

    <!-- Lista de grupos con buscador -->
    <div class="mt-6">
      <div class="mb-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Buscar grupos..."
          class="input w-full"
        />
      </div>
      <h2 class="text-lg font-medium mb-2">Grups Existents:</h2>
      <ul class="divide-y divide-gray-200">
        <li
          v-for="group in filteredGroups"
          :key="group.id"
          class="py-4 flex items-center justify-between"
        >
          <div>
            <h2 class="text-lg font-medium">{{ group.name }}</h2>
            <p class="text-gray-600">{{ group.description }}</p>
            <p class="text-sm text-gray-500">
              Miembros: {{ group.number_of_students }}
            </p>
          </div>
        </li>
      </ul>
    </div>

    <!-- Botones -->
    <div class="flex justify-between mt-6">
      <button class="btn btn-primary sm:w-auto w-full" @click="goBack">
        Tornar a Grups
      </button>
      <button
        :disabled="!groupName || selectedStudents.length === 0 || isLoading"
        class="btn btn-primary sm:w-auto w-full"
        @click="handleCreateGroup"
      >
        {{ isLoading ? 'Creant...' : 'Crear Grup' }}
      </button>
    </div>

    <!-- Mensajes de estado -->
    <p v-if="successMessage" class="text-green-500 mt-4">{{ successMessage }}</p>
    <p v-if="errorMessage" class="text-red-500 mt-4">{{ errorMessage }}</p>

    <!-- Loader -->
    <div
      v-if="isLoading"
      class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50"
    >
      <div class="loader"></div>
    </div>
  </div>
</template>