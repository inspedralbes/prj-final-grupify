<template>
  <div class="space-y-6 p-6 bg-gray-50">
    <!-- Contenedor para el título y el botón de regreso -->
    <div class="flex justify-between items-center bg-[rgb(0,173,238)] text-white p-4 rounded-t-lg">
      <h2 class="text-2xl font-bold">Bitàcora del Grup</h2>
      <router-link to="/alumne/grups"
        class="inline-flex items-center gap-2 px-4 py-2 bg-[rgb(0,173,238)] text-white rounded-lg hover:bg-[rgb(0,153,218)] transition-colors border border-[rgb(0,173,238)] shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
            clip-rule="evenodd" />
        </svg>
        Tornar
      </router-link>
    </div>

    <div class="bg-white rounded-lg shadow-lg p-6 border border-gray-200">
      <div v-if="store.bitacora" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Títol</label>
          <div v-if="!store.isEditing" class="text-lg font-semibold text-[rgb(0,173,238)]">{{ store.bitacora.title }}
          </div>
          <input v-else v-model="store.bitacora.title"
            class="w-full p-2 border rounded-lg focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]"
            type="text" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Descripció</label>
          <div v-if="!store.isEditing" class="text-gray-600 bg-gray-50 p-3 rounded-lg">{{ store.bitacora.description }}
          </div>
          <textarea v-else v-model="store.bitacora.description"
            class="w-full p-2 border rounded-lg focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]"
            rows="4"></textarea>
        </div>
      </div>
      <div v-else class="text-center py-8 text-gray-500">
        No s'ha trobat la bitàcora per aquest grup
      </div>
    </div>

    <div v-if="store.loadingNotes" class="text-center py-4">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[rgb(0,173,238)] mx-auto"></div>
    </div>

    <div v-if="currentGroup && currentGroup.users && currentGroup.users.length > 0"
      class="bg-white rounded-lg shadow-lg">
      <div class="p-4 bg-[rgb(0,173,238)] text-white rounded-t-lg flex justify-between items-center">
        <h3 class="text-xl font-bold">Notes dels Integrants</h3>
        <button @click="store.showCreateNoteModal = true"
          class="px-4 py-2 bg-[rgb(0,173,238)] text-white rounded-lg hover:bg-[rgb(0,153,218)] transition-colors font-semibold flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd"
              d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
              clip-rule="evenodd" />
          </svg>
          Crear Nota
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th v-for="user in currentGroup.users" :key="user.id"
                class="p-4 border text-left font-bold text-[rgb(0,173,238)] min-w-[250px]">
                {{ user.name }} {{ user.last_name }}
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td v-for="user in currentGroup.users" :key="user.id" class="border p-2 align-top">
                <div class="space-y-3">
                  <div v-for="note in getUserNotes(user.id)" :key="note.id"
                    class="bg-gray-50 p-4 rounded-lg hover:shadow-md transition-all duration-200 border border-gray-100">
                    <div class="flex justify-between items-start mb-3">
                      <h4 class="font-semibold text-[rgb(0,173,238)]">{{ note.title }}</h4>
                      <div class="flex gap-2">
                        <button @click="store.openEditModal(note)"
                          class="p-1.5 rounded-md hover:bg-[rgb(0,173,238)] hover:text-white transition-colors group">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>
                        <button @click="store.deleteNote(groupId, note.id)"
                          class="p-1.5 rounded-md hover:bg-red-500 hover:text-white transition-colors group">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
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
    <div v-else-if="!store.loadingNotes" class="text-center py-8 text-gray-500 bg-white rounded-lg shadow">
      No hi ha notes per a aquesta bitàcora.
    </div>

    <!-- Modal para crear una nueva nota -->
    <div v-if="store.showCreateNoteModal"
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
        <h3 class="text-xl font-bold text-[rgb(0,173,238)] mb-4">Crear Nota</h3>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
          <select v-model="store.selectedUserId"
            class="w-full p-2 border rounded-lg focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]">
            <option value="">Seleccionar usuario</option>
            <option v-for="user in currentGroup?.users" :key="user.id" :value="user.id">
              {{ user.name }} {{ user.last_name }}
            </option>
          </select>
        </div>

        <input v-model="store.newNote.title" placeholder="Títol"
          class="w-full p-2 border rounded-lg mb-4 focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]" />
        <textarea v-model="store.newNote.content" placeholder="Contingut"
          class="w-full p-2 border rounded-lg mb-4 focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]"
          rows="4"></textarea>
        <div class="flex justify-end space-x-4">
          <button @click="store.showCreateNoteModal = false"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            Cancelar
          </button>
          <button @click="store.createNote(groupId)"
            class="px-4 py-2 bg-[rgb(0,173,238)] text-white rounded-lg hover:bg-[rgb(0,153,218)] transition-colors"
            :disabled="!store.selectedUserId || !store.newNote.title || !store.newNote.content">
            Crear
          </button>
        </div>
      </div>
    </div>

    <!-- Modal per editar nota -->
    <div v-if="store.showNoteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
        <h3 class="text-xl font-bold text-[rgb(0,173,238)] mb-4">Editar Nota</h3>
        <input v-model="store.newNote.title" placeholder="Títol"
          class="w-full p-2 border rounded-lg mb-4 focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]" />
        <textarea v-model="store.newNote.content" placeholder="Contingut"
          class="w-full p-2 border rounded-lg mb-4 focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]"
          rows="4"></textarea>
        <div class="flex justify-end space-x-4">
          <button @click="store.showNoteModal = false"
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            Cancelar
          </button>
          <button @click="store.editNote(groupId, store.newNote)"
            class="px-4 py-2 bg-[rgb(0,173,238)] text-white rounded-lg hover:bg-[rgb(0,153,218)] transition-colors">
            Guardar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch, watchEffect, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useBitacoraStore } from '@/stores/bitacoraStore'
import { useGroupStore } from '@/stores/groupStore';

const route = useRoute();
const store = useBitacoraStore();
const groupStore = useGroupStore();
const groupId = route.params.id;

const currentGroup = computed(() => {
  return groupStore.groups.find(group => group.id === parseInt(groupId));
});

const getUserNotes = (userId) => {
  return store.notes.filter(note => note.user_id === userId);
};

onMounted(async () => {
  await Promise.all([
    groupStore.fetchGroups(),
    store.fetchBitacora(groupId),
    store.fetchNotes(groupId)
  ]);
});

watch(
  () => groupStore.groups,
  async (newGroups) => {
    const currentGroup = newGroups.find(group => group.id === parseInt(groupId));
    if (currentGroup) {
      await Promise.all([
        store.fetchBitacora(groupId),
        store.fetchNotes(groupId)
      ]);
    }
  },
  { deep: true }
);

watchEffect(async () => {
  const group = groupStore.groups.find(group => group.id === parseInt(groupId));
  if (group) {
    await Promise.all([
      store.fetchBitacora(groupId),
      store.fetchNotes(groupId)
    ]);
  }
});
</script>