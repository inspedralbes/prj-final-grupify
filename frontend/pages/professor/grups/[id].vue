<script setup>
import { ref, onMounted, computed } from 'vue';
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupStore } from "@/stores/groupStore";
import { useCommentStore } from '@/stores/commentStore';
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const route = useRoute();
const studentsStore = useStudentsStore();
const groupStore = useGroupStore();
const commentsStore = useCommentStore();

const selectedStudent = ref("");
const isAdding = ref(false);
const isRemoving = ref(false);
const isLoadingMembers = ref(true); 
const isLoadingComments = ref(true);
const errorMessage = ref("");
const successMessage = ref("");
const newComment = ref("");
//const comments = ref("");
const logDate = ref(new Date().toISOString().split("T")[0]);
const logEntries = ref({}); // Almacena las entradas de la bitácora por integrante
const isBitacoraActive = ref(false); // Estado inicial del botón (apagado)

onMounted(async () => {
  try {
    await Promise.all([studentsStore.fetchStudents(), groupStore.fetchGroups()]);
    await commentsStore.fetchComments(route.params.id); // Cargar los comentarios del grupo
  } catch (error) {
    console.error("Error loading data:", error);
    errorMessage.value = "Error al cargar los datos. Inténtalo de nuevo más tarde.";
  } finally {
    isLoadingMembers.value = false;
    isLoadingComments.value = false; // Deja de cargar comentarios cuando se termine
  }
});


const students = computed(() => studentsStore.students);
const group = computed(() =>
  groupStore.groups.find(g => g.id === parseInt(route.params.id))
);

// Sincroniza el textarea con el store de comentarios
const handleAddComment = async (newComment) => {
  try {
    await commentsStore.addCommentToGroup(group.value.id, newComment);
    successMessage.value = "Comentario añadido con éxito";
    newComment.value = ""; // Limpia el campo después de añadir
  } catch (error) {
    errorMessage.value = "Hubo un error al añadir el comentario";
  }
};

const handleDeleteComment = async (commentId) => {
  try {
    await commentsStore.deleteComment(group.value.id, commentId);
    successMessage.value = "Comentario eliminado con éxito";
  } catch (error) {
    errorMessage.value = "Hubo un error al eliminar el comentario";
  }
};


const availableStudents = computed(() => {
  const memberIds = group.value?.members?.map(m => m.id) || [];
  return students.value.filter(student => !memberIds.includes(student.id));
});

const handleAddStudent = async () => {
  if (!selectedStudent.value) return;

  try {
    isAdding.value = true;
    const studentToAdd = students.value.find(s => s.id === parseInt(selectedStudent.value));
    
    group.value.members.push(studentToAdd);
    
    await groupStore.addStudentsToGroup(group.value.id, [parseInt(selectedStudent.value)]);
    
    successMessage.value = "Alumne afegit al grup amb èxit";
    selectedStudent.value = "";
  } catch (error) {
    group.value.members = group.value.members.filter(m => m.id !== parseInt(selectedStudent.value));
    errorMessage.value = "Hi ha hagut un error al afegir l'alumne al grup";
  } finally {
    isAdding.value = false;
    setTimeout(() => {
      successMessage.value = "";
      errorMessage.value = "";
    }, 3000);
  }
};

const handleRemoveStudent = async (studentId) => {
  let studentToRemove; 

  try {
    isRemoving.value = true; 
    studentToRemove = group.value.members.find(m => m.id === studentId);
    group.value.members = group.value.members.filter(m => m.id !== studentId);

    await groupStore.removeStudentFromGroup(group.value.id, studentId);

    successMessage.value = "Alumne eliminat del grup amb èxit";
  } catch (error) {
    if (studentToRemove) {
      group.value.members.push(studentToRemove); 
    }
    errorMessage.value = "Hi ha hagut un error al eliminar l'alumne del grup";
  } finally {
    isRemoving.value = false; 
    setTimeout(() => {
      successMessage.value = "";
      errorMessage.value = "";
    }, 3000);
  }
};

const saveGroup = () => {
  console.log({
    groupId: group.value.id,
    members: group.value.members,
    comments: commentsStore.comments, // Usar el estado del store para los comentarios
    logEntries: logEntries.value,
    logDate: logDate.value
  });
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />

    <div class="max-w-6xl mx-auto px-4 py-8">
      <!-- Header Section with Back Button -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">{{ group?.name }}</h1>
          <p class="mt-2 text-gray-600">{{ group?.description }}</p>
        </div>
        <NuxtLink
          to="/professor/grups"
          class="flex items-center gap-2 px-4 py-2 rounded-lg text-[rgb(0,173,238)] hover:bg-[rgba(0,173,238,0.1)] transition-colors"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 19l-7-7m0 0l7-7m-7 7h18"
            />
          </svg>
          Tornar
        </NuxtLink>
      </div>

      <!-- Sección de Estudiantes -->
      <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Gestió d'Estudiants</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Add Student Section -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">
              Afegir nou alumne
            </h3>
            <div class="space-y-4">
              <select
                v-model="selectedStudent"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-[rgb(0,173,238)] focus:border-[rgb(0,173,238)]"
                :disabled="isAdding"
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
                :disabled="!selectedStudent || isAdding"
                class="w-full px-6 py-3 rounded-lg bg-[rgb(0,173,238)] text-white hover:bg-[rgb(0,153,218)] disabled:opacity-50 transition-colors font-medium"
              >
                <span v-if="isAdding" class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                </span>
                <span v-else>Afegir alumne</span>
              </button>
            </div>

            <!-- Mensaje -->
            <div class="mt-4">
              <p v-if="successMessage" class="px-4 py-3 bg-green-50 text-green-700 rounded-lg">
                {{ successMessage }}
              </p>
              <p v-if="errorMessage" class="px-4 py-3 bg-red-50 text-red-700 rounded-lg">
                {{ errorMessage }}
              </p>
            </div>
          </div>

          <!-- Members List Section -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-semibold text-gray-800">
                Membres del grup
              </h3>
              <span class="px-4 py-1.5 bg-[rgba(0,173,238,0.1)] text-[rgb(0,173,238)] rounded-full text-sm font-medium">
                {{ group?.members?.length || 0 }} membres
              </span>
            </div>

            <!-- Mensaje de carga -->
            <div v-if="isLoadingMembers" class="py-8 text-center text-gray-500">
              <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <p>Carregant membres del grup...</p>
            </div>

            <!-- Lista de miembros -->
            <div v-else class="space-y-3 max-h-[500px] overflow-y-auto pr-2 -mr-2">
              <div
                v-for="member in group?.members"
                :key="member.id"
                class="group relative bg-gray-50 rounded-lg p-4 flex items-center justify-between hover:bg-[rgba(0,173,238,0.05)] transition-colors"
              >
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-[rgba(0,173,238,0.1)] flex items-center justify-center text-[rgb(0,173,238)]">
                    {{ member.name.charAt(0).toUpperCase() }}
                  </div>
                  <span class="font-medium text-gray-700">
                    {{ member.name }} {{ member.last_name }}
                  </span>
                </div>
                <button
                  @click="handleRemoveStudent(member.id)"
                  :disabled="isRemoving"
                  class="p-2 text-red-500 hover:text-red-700 transition-colors duration-200 disabled:opacity-50"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
              <div v-if="!group?.members?.length" class="py-8 text-center text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p>No hi ha membres en aquest grup</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección de Comentarios -->
      <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Gestió de Comentaris</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Add Comment Section -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">
              Afegir Comentari
            </h3>
            <textarea
              v-model="newComment"
              class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-[rgb(0,173,238)] focus:border-[rgb(0,173,238)]"
              placeholder="Escriu un comentari"
            ></textarea>
            <button
              @click="handleAddComment(newComment)"
              class="w-full mt-4 px-6 py-3 rounded-lg bg-[rgb(0,173,238)] text-white hover:bg-[rgb(0,153,218)] transition-colors"
            >
              Afegir Comentari
            </button>
          </div>

          <!-- Comments List -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">
              Comentaris
            </h3>
            <div v-if="isLoadingComments" class="py-8 text-center text-gray-500">
              <p>Cargando comentarios...</p>
            </div>
            <div v-else>
              <ul>
                <li
                  v-for="comment in commentsStore.comments"
                  :key="comment.id"
                  class="p-4 bg-gray-100 rounded-lg mb-2 flex justify-between items-center"
                >
                  <p class="text-gray-700">{{ comment.content }}</p>
                  <button
                    @click="handleDeleteComment(comment.id)"
                    class="text-red-500 hover:text-red-700 transition-colors"
                  >
                    Eliminar
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Bitácora -->
      <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold text-gray-800">Bitácora del Grup</h2>
          <div class="flex items-center">
            <button
              @click="isBitacoraActive = !isBitacoraActive"
              :class="{
                'bg-[rgb(0,173,238)]': isBitacoraActive,
                'bg-gray-200': !isBitacoraActive
              }"
              class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none"
            >
              <span
                :class="{
                  'translate-x-6': isBitacoraActive,
                  'translate-x-1': !isBitacoraActive
                }"
                class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
              ></span>
            </button>
          </div>
        </div>
        <div v-if="isBitacoraActive" class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-[rgb(0,173,238)] text-white">
                <th class="border px-4 py-2">Data</th>
                <th
                  v-for="(member, index) in group?.members"
                  :key="member.id"
                  class="border px-4 py-2"
                >
                  {{ member.name }} {{ member.last_name }}
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border px-4 py-2 text-center">
                  <input
                    type="date"
                    v-model="logDate"
                    class="w-full p-1 border rounded"
                  />
                </td>
                <td
                  v-for="(member, index) in group?.members"
                  :key="member.id"
                  class="border px-4 py-2 text-center"
                >
                  <input
                    type="text"
                    v-model="logEntries[member.id]"
                    class="w-full p-1 border rounded"
                    placeholder="Comentario..."
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>