<template>
  <div class="space-y-6 p-6 bg-gray-50">
    <div class="flex justify-between items-center bg-[rgb(0,173,238)] text-white p-4 rounded-t-lg">
      <h2 class="text-2xl font-bold">Bitàcora del Grup</h2>
      <button @click="store.toggleEdit()"
        class="px-4 py-2 bg-white text-[rgb(0,173,238)] rounded-lg hover:bg-gray-100 transition-colors">
        {{ store.isEditing ? 'Guardar' : 'Editar' }}
      </button>
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

    <div v-if="store.notes && store.notes.length > 0" class="bg-white rounded-lg shadow-lg">
      <div class="p-4 bg-[rgb(0,173,238)] text-white rounded-t-lg flex justify-between items-center">
        <h3 class="text-xl font-bold">Notes dels Integrants</h3>
      </div>

      <!-- Excel-like layout -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th v-for="(userNotes, userName) in store.groupedNotes" :key="userName"
                class="p-4 border text-left font-bold text-[rgb(0,173,238)] min-w-[250px]">
                <div class="flex justify-between items-center">
                  <span>{{ userName }}</span>
                  <button @click="store.showCreateNoteModal = true"
                    class="px-4 py-2 bg-[rgb(0,173,238)] text-white rounded-lg hover:bg-[rgb(0,153,218)] transition-colors">
                    Crear Nota
                  </button>
                </div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td v-for="(userNotes, userName) in store.groupedNotes" :key="userName" class="border p-2 align-top">
                <div class="space-y-3">
                  <div v-for="note in userNotes" :key="note.id"
                    class="bg-gray-50 p-3 rounded-lg hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-2">
                      <h4 class="font-semibold text-[rgb(0,173,238)]">{{ note.title }}</h4>
                      <div class="space-x-2">
                        <button @click="store.openEditModal(note)"
                          class="text-sm text-[rgb(0,173,238)] hover:underline">
                          Editar
                        </button>
                        <button @click="store.deleteNote(groupId, note.id)"
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
    <div v-else-if="!store.loadingNotes" class="text-center py-8 text-gray-500 bg-white rounded-lg shadow">
      No hi ha notes per a aquesta bitàcora.
    </div>

    <!-- Modal para crear una nueva nota -->
    <div v-if="store.showCreateNoteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full">
        <h3 class="text-xl font-bold text-[rgb(0,173,238)] mb-4">Crear Nota</h3>

        <!-- Agregar selector de usuario -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
          <select v-model="store.selectedUserId"
            class="w-full p-2 border rounded-lg focus:border-[rgb(0,173,238)] focus:ring-1 focus:ring-[rgb(0,173,238)]">
            <option value="">Seleccionar usuario</option>
            <option v-for="(userNotes, userName) in store.groupedNotes" :key="userName" :value="userNotes[0]?.user?.id">
              {{ userName }}
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

    <!-- Modal para editar nota -->
    <div v-if="store.showNoteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50">
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
import { onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useBitacoraStore } from '@/stores/bitacoraStore'
import { useGroupStore } from '@/stores/groupStore';

const route = useRoute();
const store = useBitacoraStore();
const groupStore = useGroupStore();
const groupId = route.params.id;

onMounted(async () => {
  await store.fetchBitacora(groupId)
  await store.fetchNotes(groupId)
})

// Observar cambios en el grupo
watch(
  () => groupStore.groups.find(group => group.id === parseInt(groupId)),
  async (newGroup) => {
    if (newGroup) {
      await store.fetchBitacora(groupId);
      await store.fetchNotes(groupId);
    }
  },
  { deep: true }
);

// Observar cambios en las notas
watchEffect(async () => {
  const group = groupStore.groups.find(group => group.id === parseInt(groupId));
  if (group) {
    await store.fetchBitacora(groupId); // Actualiza la bitácora cuando cambia el grupo
    await store.fetchNotes(groupId); // Actualiza las notas cuando cambian los integrantes
  }
});
</script>