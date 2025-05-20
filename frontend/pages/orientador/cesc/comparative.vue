<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavOrientador class="w-full" />

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Vista de tarjetas de categorías -->
      <CescCategoryCards v-if="!showGraph" @category-selected="handleCategorySelected" class="pt-4" />
      
      <!-- Vista de gráficos (solo visible después de seleccionar una categoría) -->
      <div v-if="showGraph" class="grid grid-cols-1 gap-8">        
        <!-- Gráfico de Tags CESC -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="p-1">
            <TagsGraphic 
              :categoria="categoria" 
              @return-to-categories="showGraph = false" 
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import DashboardNavOrientador from '@/components/Orientador/DashboardNavOrientador.vue';
import TagsGraphic from '@/components/Orientador/CescComponents/TagsGraphic.vue';
import CescCategoryCards from '@/components/Orientador/CescComponents/CescCategoryCards.vue';

const route = useRoute();
// Datos del usuario
const userData = ref(null);
const categoria = ref('all');
const showGraph = ref(false);

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

// Función para manejar la selección de categoría desde las tarjetas
const handleCategorySelected = (selectedCategory) => {
  categoria.value = selectedCategory;
  showGraph.value = true;
  // Actualizar URL sin recargar la página
  window.history.pushState({}, '', `/orientador/cesc/comparative${selectedCategory !== 'all' ? `?categoria=${selectedCategory}` : ''}`);
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
    showGraph.value = true;
  }
});
</script>
