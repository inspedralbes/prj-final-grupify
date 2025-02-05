<template>
    <div class="space-y-6 p-6">
      <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Bitàcora del Grup</h2>
        <button
          @click="isEditing = !isEditing"
          class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/80"
        >
          {{ isEditing ? 'Guardar' : 'Editar' }}
        </button>
      </div>
  
      <div class="bg-white rounded-lg shadow p-6">
        <div v-if="bitacora" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Títol</label>
            <div v-if="!isEditing" class="text-lg">{{ bitacora.title }}</div>
            <input
              v-else
              v-model="editedBitacora.title"
              class="w-full p-2 border rounded-lg"
              type="text"
            />
          </div>
  
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Descripció</label>
            <div v-if="!isEditing" class="text-gray-600">{{ bitacora.description }}</div>
            <textarea
              v-else
              v-model="editedBitacora.description"
              class="w-full p-2 border rounded-lg"
              rows="4"
            ></textarea>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No s'ha trobat la bitàcora per aquest grup
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  const route = useRoute();
  const groupId = route.params.id;
  const isEditing = ref(false);
  const bitacora = ref(null);
  const editedBitacora = ref({
    title: '',
    description: ''
  });
  
  async function fetchBitacora() {
  try {
    const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}`);
    const text = await response.text();

    bitacora.value = JSON.parse(text);
    editedBitacora.value = { ...bitacora.value };
  } catch (error) {
    console.error("Error fetching bitacora:", error);
  }
}

  onMounted(() => {
    fetchBitacora();
  });
  </script>