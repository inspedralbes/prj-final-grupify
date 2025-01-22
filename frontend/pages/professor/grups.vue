<script setup>
import { useStudentsStore } from "@/stores/studentsStore";

const studentsStore = useStudentsStore();
const selectedStudents = ref([]);
const isLoading = ref(false); // Estado de carga
const groupName = ref("");
const groupDescription = ref("");
const errorMessage = ref("");
const successMessage = ref("");

// Llamar a la API para obtener los estudiantes
onMounted(() => {
  studentsStore.fetchStudents();
});

// Computed: Lista de estudiantes desde el almacén
const students = computed(() => studentsStore.students);

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
  isLoading.value = true; // Mostrar el loader
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
    isLoading.value = false; // Ocultar el loader
  }
};

const goBack = () => {
  navigateTo("/professor/dashboard");
};
</script>

<template>
  <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow mt-12 mb-12">
    <h1 class="text-2xl font-bold mb-4">Seleccionar Alumnes per al Grup</h1>
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2">Nom del Grup</label>
      <input
        v-model="groupName"
        type="text"
        placeholder="Ingrese el nombre del grupo"
        class="input w-full"
      />
    </div>
    <div class="mb-6">
      <label class="block text-gray-700 font-medium mb-2"
        >Descripció del Grup</label
      >
      <textarea
        v-model="groupDescription"
        placeholder="Ingrese una descripción (opcional)"
        class="input w-full"
        rows="4"
      ></textarea>
    </div>
    <div>
      <h2 class="text-lg font-medium mb-2">Selecciona els estudiants:</h2>
      <ul class="divide-y divide-gray-200">
        <li
          v-for="student in students"
          :key="student.id"
          class="py-4 flex items-center justify-between"
        >
          <div>
            <h2 class="text-lg font-medium">
              {{ student.name }} {{ student.last_name }}
            </h2>
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
    </div>

    <div class="flex justify-between mt-6">
      <button class="btn btn-primary sm:w-auto w-full" @click="goBack">
        Tornar a Grups
      </button>
      <button
        :disabled="!groupName || selectedStudents.length === 0 || isLoading"
        class="btn btn-primary sm:w-auto w-full"
        @click="handleCreateGroup"
      >
        Crear Grup
      </button>
    </div>

    <p v-if="successMessage" class="text-green-500 mt-4">
      {{ successMessage }}
    </p>
    <p v-if="errorMessage" class="text-red-500 mt-4">{{ errorMessage }}</p>

    <!-- Loader superpuesto -->
    <div
      v-if="isLoading"
      class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50"
    >
      <div class="loader"></div>
    </div>
  </div>
</template>

<style scoped>
.loader {
  border: 8px solid #f3f3f3;
  border-top: 8px solid #3498db;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>