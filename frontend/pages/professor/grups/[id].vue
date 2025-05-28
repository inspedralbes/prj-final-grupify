<script setup>
import { ref, onMounted, computed } from 'vue';
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupStore } from "@/stores/groupStore";
import { useCommentStore } from '@/stores/commentStore';
import { useBitacoraStore } from "@/stores/bitacoraStore";
import { useAuthStore } from "@/stores/authStore";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const route = useRoute();
const studentsStore = useStudentsStore();
const groupStore = useGroupStore();
//Comentarios
const commentsStore = useCommentStore();
//Bitacora y BitacoraNotas
const bitacoraStore = useBitacoraStore();
const authStore = useAuthStore();
const bitacoraEntries = ref([]);

const selectedStudent = ref("");
const isAdding = ref(false);
const isRemoving = ref(false);
const isLoadingusers = ref(true);
const isLoadingComments = ref(true);
const errorMessage = ref("");
const successMessage = ref("");
const newComment = ref("");
const logDate = ref(new Date().toISOString().split("T")[0]);
const logEntries = ref({});
const isBitacoraActive = ref(false);
const isCommentsOpen = ref(false);

// Estado para el modal de confirmación
const showDeleteModal = ref(false);
const itemToDelete = ref(null); // Puede ser un miembro o un comentario
const modalTitle = ref("");
const deleteType = ref(""); // 'user' o 'comment'

// Función para abrir el modal de confirmación
const confirmDelete = (type, item, title) => {
  deleteType.value = type;
  itemToDelete.value = item;
  modalTitle.value = title;
  showDeleteModal.value = true;
};

// Función para manejar la eliminación
const handleDelete = async () => {
  try {
    if (deleteType.value === 'user') {
      await handleRemoveStudent(itemToDelete.value);
    } else if (deleteType.value === 'comment') {
      await handleDeleteComment(itemToDelete.value);
    }
    successMessage.value = "Elemento eliminado correctamente";
  } catch (error) {
    errorMessage.value = "Hi va haver un error en eliminar l'element";
  } finally {
    showDeleteModal.value = false;
    setTimeout(() => {
      successMessage.value = "";
      errorMessage.value = "";
    }, 3000);
  }
};

onMounted(async () => {
  try {
    await Promise.all([
      studentsStore.fetchStudents(),
      groupStore.fetchGroups(),
      commentsStore.fetchComments(route.params.id),
      bitacoraStore.fetchBitacora(route.params.id), // Add this line
      bitacoraStore.fetchNotes(route.params.id)     // Add this line
    ]);
    bitacoraEntries.value = bitacoraStore.notes;
  } catch (error) {
    console.error("Error loading data:", error);
  } finally {
    isLoadingusers.value = false;
    isLoadingComments.value = false;
  }
});

//Para guardar la BITACORA
const handleSaveBitacoraEntry = async (userId) => {
  try {
    if (!logEntries.value[userId]) return;

    await bitacoraStore.createNote(route.params.id, {
      user_id: userId,
      title: `Entry for ${logDate.value}`,
      content: logEntries.value[userId]
    });

    // Refresh notes after saving
    await bitacoraStore.fetchNotes(route.params.id);
    // Clear the input
    logEntries.value[userId] = '';

    successMessage.value = "Entrada guardada correctamente";
  } catch (error) {
    errorMessage.value = "Error en guardar l'entrada";
  }
};

const students = computed(() => studentsStore.students);
const group = computed(() =>
  groupStore.groups.find(g => g.id === parseInt(route.params.id))
);

const availableStudents = computed(() => {
  if (!group.value || !group.value.users) return students.value;
  const userIds = group.value.users.map(m => m.id);
  return students.value.filter(student => !userIds.includes(student.id));
});

const handleAddComment = async () => {
  if (!newComment.value.trim()) {
    errorMessage.value = "El comentari no pot estar buit.";
    return;
  }

  try {
    // Usar l'ID de l'usuari autenticat, que hauria de ser un professor, tutor o orientador
    const commentData = {
      teacher_id: authStore.user?.id, // Usar l'ID de l'usuari autenticat
      content: newComment.value,
    };

    await commentsStore.addCommentToGroup(group.value.id, commentData);
    successMessage.value = "Comentari afegit amb éxit";
    newComment.value = ""; // Limpia el campo después de añadir
  } catch (error) {
    console.error("Error adding comment:", error);
    errorMessage.value = "Error al afegir el comentari";
  } finally {
    setTimeout(() => {
      successMessage.value = "";
      errorMessage.value = "";
    }, 3000);
  }
};

const handleDeleteComment = async (commentId) => {
  try {
    await commentsStore.deleteCommentFromGroup(group.value.id, commentId);
    successMessage.value = "Comentari eliminat correctament";
  } catch (error) {
    errorMessage.value = "Error al eliminar el comentari";
  }
};

const handleAddStudent = async () => {
  if (!selectedStudent.value) return;

  try {
    isAdding.value = true;
    const studentToAdd = students.value.find(s => s.id === parseInt(selectedStudent.value));

    group.value.users.push(studentToAdd);

    await groupStore.addStudentsToGroup(group.value.id, [parseInt(selectedStudent.value)]);

    successMessage.value = "Alumne afegit al grup amb èxit";
    selectedStudent.value = "";
  } catch (error) {
    group.value.users = group.value.users.filter(m => m.id !== parseInt(selectedStudent.value));
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
    studentToRemove = group.value.users.find(m => m.id === studentId);
    group.value.users = group.value.users.filter(m => m.id !== studentId);

    await groupStore.removeStudentFromGroup(group.value.id, studentId);

    successMessage.value = "Alumne eliminat del grup amb èxit";
  } catch (error) {
    if (studentToRemove) {
      group.value.users.push(studentToRemove);
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
    users: group.value.users,
    comments: commentsStore.comments,
    logEntries: logEntries.value,
    logDate: logDate.value
  });
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />

    <!-- Pop Up de confirmación -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
      <!-- Fondo difuminado -->
      <div class="fixed inset-0 bg-white/50 backdrop-blur-sm" @click.self="showDeleteModal = false"></div>

      <!-- Contenido del modal -->
      <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-xl border border-gray-200">
        <h3 class="text-lg font-semibold mb-4 text-gray-900">Confirmar eliminació</h3>
        <p class="mb-6 text-gray-600">
          Estàs segur que vols eliminar {{ deleteType === 'user' ? "l'alumne" : "el comentari" }}
          <strong>"{{ modalTitle }}"</strong>?
        </p>
        <div class="flex justify-end space-x-3">
          <button @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors font-medium">
            Cancel·lar
          </button>
          <button @click="handleDelete"
            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
            Confirmar
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-8">
      <!-- Header Section with Back Button -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">{{ group?.name }}</h1>
          <p class="mt-2 text-gray-600">{{ group?.description }}</p>
        </div>
        <NuxtLink to="/professor/grups"
          class="flex items-center gap-2 px-4 py-2 rounded-lg text-[rgb(0,173,238)] hover:bg-[rgba(0,173,238,0.1)] transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Tornar
        </NuxtLink>
      </div>

      <!-- Secció d'Estudiants -->
      <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Gestió d'Estudiants</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Add Student Section -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-6">
              Afegir nou alumne
            </h3>
            <div class="space-y-4">
              <select v-model="selectedStudent"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-[rgb(0,173,238)] focus:border-[rgb(0,173,238)]"
                :disabled="isAdding">
                <option value="">Selecciona un alumne</option>
                <option v-for="student in availableStudents" :key="student.id" :value="student.id">
                  {{ student.name }} {{ student.last_name }}
                </option>
              </select>

              <button @click="handleAddStudent" :disabled="!selectedStudent || isAdding"
                class="w-full px-6 py-3 rounded-lg bg-[rgb(0,173,238)] text-white hover:bg-[rgb(0,153,218)] disabled:opacity-50 transition-colors font-medium">
                <span v-if="isAdding" class="flex items-center justify-center gap-2">
                  <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                  </svg>
                </span>
                <span v-else>Afegir alumne</span>
              </button>
            </div>

            <!-- Missatge -->
            <div class="mt-4">
              <p v-if="successMessage" class="px-4 py-3 bg-green-50 text-green-700 rounded-lg">
                {{ successMessage }}
              </p>
              <p v-if="errorMessage" class="px-4 py-3 bg-red-50 text-red-700 rounded-lg">
                {{ errorMessage }}
              </p>
            </div>
          </div>

          <!-- users List Section -->
          <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-semibold text-gray-800">
                Membres del grup
              </h3>
              <span class="px-4 py-1.5 bg-[rgba(0,173,238,0.1)] text-[rgb(0,173,238)] rounded-full text-sm font-medium">
                {{ group?.users?.length || 0 }} membres
              </span>
            </div>

            <!-- Missatge de càrrega -->
            <div v-if="isLoadingusers" class="py-8 text-center text-gray-500">
              <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <p>Carregant membres del grup...</p>
            </div>

            <!-- Llista de membres -->
            <div v-else class="space-y-3 max-h-[500px] overflow-y-auto pr-2 -mr-2">
              <div v-for="user in group?.users" :key="user.id"
                class="group relative bg-gray-50 rounded-lg p-4 flex items-center justify-between hover:bg-[rgba(0,173,238,0.05)] transition-colors">
                <div class="flex items-center gap-3">
                  <!-- Mostrar imatge de perfil o inicial -->
                  <div class="relative">
                    <div v-if="user.image" class="w-8 h-8 rounded-full overflow-hidden">
                      <img :src="user.image" class="w-full h-full object-cover" :alt="`Foto de ${user.name}`" />
                    </div>
                    <div v-else
                      class="w-8 h-8 rounded-full bg-[rgba(0,173,238,0.1)] flex items-center justify-center text-[rgb(0,173,238)]">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <!-- Indicador de estado online (si es necesario) -->
                    <div class="absolute bottom-0 right-0 w-2 h-2 rounded-full border-2 border-white"
                      :class="studentsStore.isStudentOnline(user.id) ? 'bg-green-500' : 'bg-gray-400'"></div>
                  </div>
                  <span class="font-medium text-gray-700">
                    {{ user.name }} {{ user.last_name }}
                  </span>
                </div>
                <button @click="confirmDelete('user', user.id, `${user.name} ${user.last_name}`)" :disabled="isRemoving"
                  class="p-2 text-red-500 hover:text-red-700 transition-colors duration-200 disabled:opacity-50">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
              <div v-if="!group?.users?.length" class="py-8 text-center text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 text-gray-400" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <p>No hi ha membres en aquest grup</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sección de Comentarios (Bitácora) -->
      <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Comentaris & Bitácora</h2>
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Comentaris del Grup</h3>
            <button @click="isCommentsOpen = !isCommentsOpen" :class="{
              'bg-[rgb(0,173,238)]': isCommentsOpen,
              'bg-gray-200': !isCommentsOpen
            }" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none">
              <span :class="{
                'translate-x-6': isCommentsOpen,
                'translate-x-1': !isCommentsOpen
              }" class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"></span>
            </button>
          </div>

          <!-- Contenido de la bitácora de comentarios -->
          <div v-if="isCommentsOpen">
            <!-- Add Comment Section -->
            <div class="mb-6">
              <textarea v-model="newComment"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-[rgb(0,173,238)] focus:border-[rgb(0,173,238)]"
                placeholder="Escriu un comentari"></textarea>
              <button @click="handleAddComment(newComment)"
                class="w-full mt-4 px-6 py-3 rounded-lg bg-[rgb(0,173,238)] text-white hover:bg-[rgb(0,153,218)] transition-colors">
                Afegir Comentari
              </button>
            </div>

            <!-- Comments List -->
            <div>
              <h3 class="text-xl font-semibold text-gray-800 mb-6">
                Comentaris
              </h3>
              <div v-if="isLoadingComments" class="py-8 text-center text-gray-500">
                <p>Cargando comentaris...</p>
              </div>
              <div v-else>
                <ul>
                  <li v-for="comment in commentsStore.comments" :key="comment.id"
                    class="p-4 bg-gray-100 rounded-lg mb-2 flex justify-between items-center">
                    <p class="text-gray-700">{{ comment.content }}</p>

                    <button @click="confirmDelete('comment', comment.id, comment.content)" :disabled="isRemoving"
                      class="text-red-500 hover:text-red-700 transition-colors">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bitácora -->
      <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-xl font-semibold text-gray-800">Bitácora del Grup</h2>
          <div class="flex items-center">
            <button @click="isBitacoraActive = !isBitacoraActive" :class="{
              'bg-[rgb(0,173,238)]': isBitacoraActive,
              'bg-gray-200': !isBitacoraActive
            }" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none">
              <span :class="{
                'translate-x-6': isBitacoraActive,
                'translate-x-1': !isBitacoraActive
              }" class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"></span>
            </button>
          </div>
        </div>
        <!--- Mostrar las notas de la Bitacora -->
        <div v-if="isBitacoraActive" class="overflow-x-auto">
          <table class="w-full border-collapse">
            <thead>
              <tr class="bg-[rgb(0,173,238)] text-white">
                <th class="border px-4 py-2">Notes</th>
                <th v-for="user in group?.users" :key="user.id" class="border px-4 py-2">
                  {{ user.name }} {{ user.last_name }}
                </th>
              </tr>
            </thead>
            <tbody>
              <!-- Notas existentes -->
              <tr>
                <td class="border px-4 py-2 text-center">
                  Notes
                </td>
                <td v-for="user in group?.users" :key="user.id" class="border px-4 py-2">
                  <div class="space-y-2">
                    <div v-for="note in bitacoraStore.notes.filter(note => note.user_id === user.id)" :key="note.id"
                      class="p-2 bg-gray-50 rounded mb-2">
                      <p class="text-sm font-bold">{{ note.title }}</p>
                      <p class="text-sm">{{ note.content }}</p>
                      <div class="text-xs text-gray-500 mt-1">
                        {{ new Date(note.created_at).toLocaleDateString() }}
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>