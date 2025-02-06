<template>
  <div class="space-y-6 p-6 bg-gray-50">
    <div class="flex justify-between items-center bg-[rgb(0,173,238)] text-white p-4 rounded-t-lg">
      <h2 class="text-2xl font-bold">Bitàcora del Grup</h2>
      <button @click="toggleEdit" class="px-4 py-2 bg-white text-[rgb(0,173,238)] rounded-lg hover:bg-gray-100 transition-colors">
        {{ isEditing ? 'Guardar' : 'Editar' }}
      </button>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">
      <div v-if="bitacora" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Títol</label>
          <div v-if="!isEditing" class="text-lg font-semibold text-[rgb(0,173,238)]">{{ bitacora.title }}</div>
          <input v-else v-model="editedBitacora.title" class="w-full p-2 border rounded-lg focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]" type="text" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descripció</label>
          <div v-if="!isEditing" class="text-gray-600 bg-gray-50 p-3 rounded-lg">{{ bitacora.description }}</div>
          <textarea v-else v-model="editedBitacora.description" class="w-full p-2 border rounded-lg focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]" rows="4"></textarea>
        </div>
      </div>
      <div v-else class="text-center py-8 text-gray-500">
        No s'ha trobat la bitàcora per aquest grup
      </div>
    </div>

    <div v-if="loadingNotes" class="text-center py-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[rgb(0,173,238)] mx-auto"></div>
    </div>

    <div v-if="notes.length > 0" class="bg-white rounded-lg shadow-lg">
      <div class="p-4 bg-[rgb(0,173,238)] text-white rounded-t-lg">
        <h3 class="text-xl font-bold">Notes dels Integrants</h3>
      </div>

      <!-- Excel-like layout -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th v-for="(userNotes, userName) in groupedNotes" :key="userName" 
                  class="p-4 border text-left font-bold text-[rgb(0,173,238)] min-w-[250px]">
                {{ userName }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td v-for="(userNotes, userName) in groupedNotes" :key="userName" 
                  class="border p-2 align-top">
                <div class="space-y-3">
                  <div v-for="note in userNotes" :key="note.id" 
                       class="bg-gray-50 p-3 rounded-lg hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-2">
                      <h4 class="font-semibold text-[rgb(0,173,238)]">{{ note.title }}</h4>
                      <div class="space-x-2">
                        <button @click="openEditModal(note)" 
                                class="text-sm text-[rgb(0,173,238)] hover:underline">
                          Editar
                        </button>
                        <button @click="deleteNote(note.id)" 
                                class="text-sm text-red-500 hover:underline">
                          Eliminar
                        </button>
                      </div>
                    </div>
                    <p class="text-gray-600 text-sm">{{ note.content }}</p>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-else-if="!loadingNotes" class="text-center py-8 text-gray-500 bg-white rounded-lg shadow">
      No hi ha notes per a aquesta bitàcora.
    </div>

    <!-- Modal para editar nota -->
    <div v-if="showNoteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
        <h3 class="text-xl font-bold text-[rgb(0,173,238)] mb-4">Editar Nota</h3>
        <input v-model="newNote.title" 
               placeholder="Títol" 
               class="w-full p-2 border rounded-lg mb-4 focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]" />
        <textarea v-model="newNote.content" 
                  placeholder="Contingut" 
                  class="w-full p-2 border rounded-lg mb-4 focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]" 
                  rows="4"></textarea>
        <div class="flex justify-end space-x-4">
          <button @click="showNoteModal = false" 
                  class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            Cancelar
          </button>
          <button @click="editNote(newNote)" 
                  class="px-4 py-2 bg-[rgb(0,173,238)] text-white rounded-lg hover:bg-[rgb(0,153,218)] transition-colors">
            Guardar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const groupId = route.params.id;

const isEditing = ref(false);
const bitacora = ref(null);
const editedBitacora = ref({
  title: '',
  description: ''
});
const notes = ref([]);
const loadingNotes = ref(true);
const showNoteModal = ref(false);
const newNote = ref({
  title: '',
  content: '',
  id: null
});

const toggleEdit = () => {
  isEditing.value = !isEditing.value;
};

async function createNote() {
  try {
    const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}/notes`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        bitacora_id: groupId,
        user_id: currentUserId,
        title: newNote.value.title,
        content: newNote.value.content
      })
    });

    if (response.ok) {
      const createdNote = await response.json();
      notes.value.push(createdNote.note);
      showNoteModal.value = false;
      newNote.value = { title: '', content: '', id: null };
    }
  } catch (error) {
    console.error("Error creating note:", error);
  }
}

async function editNote(note) {
  try {
    const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}/notes/${note.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        title: note.title,
        content: note.content
      })
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const updatedNote = await response.json();

    const index = notes.value.findIndex(n => n.id === note.id);
    if (index !== -1) {
      notes.value[index] = updatedNote.note;
    }

    showNoteModal.value = false;
  } catch (error) {
    console.error('Error al actualizar la nota:', error);
  }
}

async function deleteNote(noteId) {
  if (!confirm('¿Estás seguro de que quieres eliminar esta nota?')) return;

  try {
    const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}/notes/${noteId}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json'
      }
    });

    if (response.ok) {
      notes.value = notes.value.filter(note => note.id !== noteId);
    }
  } catch (error) {
    console.error('Error:', error);
  }
}

async function fetchBitacora() {
  try {
    const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}`);
    bitacora.value = await response.json();
    editedBitacora.value = { ...bitacora.value };
  } catch (error) {
    console.error("Error fetching bitacora:", error);
  }
}

async function fetchNotes() {
  loadingNotes.value = true;
  try {
    const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}/notes`);
    notes.value = await response.json();
  } catch (error) {
    console.error("Error fetching notes:", error);
  } finally {
    loadingNotes.value = false;
  }
}

const groupedNotes = computed(() => {
  return notes.value.reduce((acc, note) => {
    const userName = note.user ? `${note.user.name} ${note.user.last_name}` : 'Desconocido';
    if (!acc[userName]) {
      acc[userName] = [];
    }
    acc[userName].push(note);
    return acc;
  }, {});
});

const openEditModal = (note) => {
  newNote.value = { 
    title: note.title, 
    content: note.content, 
    id: note.id
  };
  showNoteModal.value = true;
};

onMounted(() => {
  fetchBitacora();
  fetchNotes();
});
</script>