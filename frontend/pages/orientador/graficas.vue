<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher class="w-full" />

    <!-- Cabecera con información -->
    <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white py-8 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold">
              Gráficas Comparativas
            </h1>
            <p class="mt-2 text-blue-100">
              Análisis visual de datos CESC entre diferentes clases
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Selector de categoría -->
      <div class="mb-6 bg-white rounded-lg shadow-md p-4">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Categoría CESC</h2>
        <div class="flex flex-wrap gap-3">
          <button 
            @click="setCategoria('all')" 
            :class="['px-4 py-2 rounded-lg transition-colors', 
                    categoria === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
            Todos
          </button>
          <button 
            @click="setCategoria('social')" 
            :class="['px-4 py-2 rounded-lg transition-colors', 
                    categoria === 'social' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
            SOCIAL (A)
          </button>
          <button 
            @click="setCategoria('violento')" 
            :class="['px-4 py-2 rounded-lg transition-colors', 
                    categoria === 'violento' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
            VIOLENTO (B)
          </button>
          <button 
            @click="setCategoria('afectado')" 
            :class="['px-4 py-2 rounded-lg transition-colors', 
                    categoria === 'afectado' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
            AFECTADO (C)
          </button>
        </div>
      </div>
      
      <!-- Sección de gráficos -->
      <div class="grid grid-cols-1 gap-8">
        <!-- Título de la categoría seleccionada -->
        <div v-if="categoria !== 'all'" class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-2xl font-bold" :class="{
            'text-green-600': categoria === 'social',
            'text-red-600': categoria === 'violento',
            'text-yellow-600': categoria === 'afectado'
          }">
            Comparativa CESC: {{ categoriaTitulo }}
          </h2>
          <p class="text-gray-600 mt-2">
            <template v-if="categoria === 'social'">
              Comparativa dels tags CESC: Popular (1) i Prosocial (4)
            </template>
            <template v-else-if="categoria === 'violento'">
              Comparativa dels tags CESC: Agressiu (3)
            </template>
            <template v-else-if="categoria === 'afectado'">
              Comparativa dels tags CESC: Rebutjat (2) i Víctima (5)
            </template>
          </p>
        </div>
        
        <!-- Gráfico de Tags CESC -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="p-1">
            <TagsGraphic :categoria="categoria" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue';
import TagsGraphic from '@/components/Teacher/CescComponents/TagsGraphic.vue';

const route = useRoute();
// Datos del usuario
const userData = ref(null);
const categoria = ref('all');

// Título formateado de la categoría
const categoriaTitulo = computed(() => {
  switch (categoria.value) {
    case 'social':
      return 'SOCIAL (A)';
    case 'violento':
      return 'VIOLENTO (B)';
    case 'afectado':
      return 'AFECTADO (C)';
    default:
      return 'Todos los tags';
  }
});

// Función para cambiar la categoría
const setCategoria = (cat) => {
  categoria.value = cat;
  // Actualizar URL sin recargar la página
  window.history.pushState({}, '', `/orientador/graficas${cat !== 'all' ? `?categoria=${cat}` : ''}`);
};

// Cargar datos del usuario desde localStorage y determinar categoría desde URL
onMounted(() => {
  const storedUser = localStorage.getItem('user');
  if (storedUser) {
    userData.value = JSON.parse(storedUser);
  }
  
  // Obtener categoría de la URL si existe
  if (route.query.categoria) {
    categoria.value = route.query.categoria;
  }
});
</script>
