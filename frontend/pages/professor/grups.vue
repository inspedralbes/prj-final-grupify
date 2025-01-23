<script setup>
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupStore } from "@/stores/groupStore";
import { useGroupSearch } from "@/composables/useGroupSearch"; 

const studentsStore = useStudentsStore();
const groupStore = useGroupStore();

const selectedStudents = ref([]);
const isLoading = ref(false);
const groupName = ref("");
const groupDescription = ref("");
const errorMessage = ref("");
const successMessage = ref("");

// Llamar a la API para obtener los estudiantes
onMounted(() => {
  studentsStore.fetchStudents();
  groupStore.fetchGroups();
});

// Computed: Lista de estudiantes desde el almacén
const students = computed(() => studentsStore.students);
const groups = computed(() => groupStore.groups);
const { searchQuery, filteredGroups } = useGroupSearch(groups); 

const toggleSelection = studentId => {
  if (selectedStudents.value.includes(studentId)) {
    selectedStudents.value = selectedStudents.value.filter(
      id => id !== studentId
    );
  } else {
    selectedStudents.value.push(studentId);
  }
};

// Función para agregar estudiantes al grupo
const addStudentsToGroup = async (groupId, studentIds) => {
  await fetch(
    `http://localhost:8000/api/groups/${groupId}/addStudentsToGroup`,
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        accept: "application/json",
      },
      body: JSON.stringify({ student_ids: studentIds }),
    }
  );
};

// Crear grupo y enviarlo al backend
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

    <div class="flex justify-between mt-6">
      <button class="btn btn-primary sm:w-auto w-full" @click="goBack">
        Tornar a Grups
      </button>
      <button :disabled="!groupName || selectedStudents.length === 0 || isLoading"
        class="btn btn-primary sm:w-auto w-full" @click="handleCreateGroup">
        Crear Grup
      </button>
    </div>

    <p v-if="successMessage" class="text-green-500 mt-4">
      {{ successMessage }}
    </p>
    <p v-if="errorMessage" class="text-red-500 mt-4">{{ errorMessage }}</p>

    <!-- Loader superpuesto -->
    <div v-if="isLoading" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
      <div class="loader"></div>
    </div>
  </div>
</template>