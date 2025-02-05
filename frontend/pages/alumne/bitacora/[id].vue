<template>
  <div class="space-y-6 p-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold">Bitàcora del Grup</h2>
      <button @click="toggleEdit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80">
        {{ isEditing ? 'Guardar' : 'Editar' }}
      </button>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
      <div v-if="bitacora" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Títol</label>
          <div v-if="!isEditing" class="text-lg">{{ bitacora.title }}</div>
          <input v-else v-model="editedBitacora.title" class="w-full p-2 border rounded-lg" type="text" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descripció</label>
          <div v-if="!isEditing" class="text-gray-600">{{ bitacora.description }}</div>
          <textarea v-else v-model="editedBitacora.description" class="w-full p-2 border rounded-lg" rows="4"></textarea>
        </div>
      </div>
      <div v-else class="text-center py-8 text-gray-500">
        No s'ha trobat la bitàcora per aquest grup
      </div>
    </div>

    <!------- Notes de bitacora -----> 
    <div v-if="loadingNotes" class="text-center text-gray-500">Cargando notes...</div>
    <div v-if="notes.length > 0" class="space-y-4">
      <h3 class="text-xl font-bold">Notes de la Bitàcora</h3>
      <div v-for="note in notes" :key="note.id" class="bg-gray-100 p-4 rounded-lg shadow">
        <h4 class="font-semibold">{{ note.title }}</h4>
        <p class="text-gray-600">{{ note.content }}</p>
        <p class="text-sm text-gray-500">Creada per: {{ note.user.name }} {{ note.user.last_name }}</p>
      </div>
    </div>
    <div v-else-if="!loadingNotes" class="text-center text-gray-500">
      No hi ha notes per a aquesta bitàcora.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
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

const toggleEdit = () => {
  isEditing.value = !isEditing.value;
};

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

onMounted(() => {
  fetchBitacora();
  fetchNotes();
});
</script>
