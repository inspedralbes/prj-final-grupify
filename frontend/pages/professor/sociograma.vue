<script setup>
// Referencias a los datos
const relationships = ref([]); // Relación API
const width = 800; // Ancho del espacio SVG
const height = 600; // Alto del espacio SVG

// Llamar a la API para obtener las relaciones
const fetchRelationships = async () => {
  try {
    const response = await fetch(
      "http://localhost:8000/api/sociogram-relationships"
    );
    if (!response.ok) throw new Error("Error al obtener las relaciones");
    relationships.value = await response.json();
  } catch (error) {
    console.error("Error al cargar las relaciones:", error);
  }
};

// Llamada a la API al montar el componente
onMounted(() => {
  fetchRelationships();
});

// Generar posiciones para los usuarios en forma de estrella
const userPositions = ref({}); // Mapeo de usuarios con posiciones únicas

const getUserPosition = (userId, index, total) => {
  const radius = Math.min(width, height) / 2 - 80; // Radio del círculo
  const angle = (2 * Math.PI * index) / total + Math.PI / 4; // Modificar el ángulo para estrella (empezar en 45 grados)
  const centerX = width / 2; // Centro del sociograma (x)
  const centerY = height / 2; // Centro del sociograma (y)

  // Calcula las coordenadas x, y con la forma de estrella
  if (!userPositions.value[userId]) {
    const starRadius = radius;
    userPositions.value[userId] = {
      x: centerX + starRadius * Math.cos(angle),
      y: centerY + starRadius * Math.sin(angle),
    };
  }

  return userPositions.value[userId];
};

// Obtener los estilos para posicionar al usuario en el contenedor
const getUserPositionStyle = (userId, index, total) => {
  const position = getUserPosition(userId, index, total);
  return {
    position: "absolute",
    top: `${position.y}px`,
    left: `${position.x}px`,
    transform: "translate(-50%, -50%)",
  };
};
</script>

<template>
  <div class="max-w-4xl mx-auto mt-8 p-4 bg-gray-50 rounded-lg shadow-md">
    <!-- Contenedor principal -->
    <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">
      Sociograma test de relacions a classe
    </h1>
    <div
      class="relative w-full flex justify-center items-center"
      :style="{ height: `${height}px`, width: `${width}px` }"
    >
      <!-- Mostrar nodos de usuarios -->
      <div
        v-for="(relationship, index) in relationships"
        :key="relationship.id"
        class="absolute flex items-center justify-center bg-blue-500 text-white rounded-full shadow-lg"
        :style="[
          getUserPositionStyle(
            relationship.user.id,
            index,
            relationships.length
          ),
          { width: '60px', height: '60px' },
        ]"
      >
        <span class="text-xs text-center font-semibold leading-tight"
          >{{ relationship.user.name }} {{ relationship.user.last_name }}</span
        >
      </div>

      <div
        v-for="(relationship, index) in relationships"
        :key="`peer-${relationship.id}`"
        class="absolute flex items-center justify-center bg-primary text-white rounded-full shadow-lg"
        :style="[
          getUserPositionStyle(
            relationship.peer.id,
            index,
            relationships.length
          ),
          { width: '60px', height: '60px', zIndex: 1 },
        ]"
      >
        <span class="text-xs text-center font-semibold leading-tight"
          >{{ relationship.peer.name }} {{ relationship.peer.last_name }}</span
        >
      </div>

      <!-- Dibujar líneas de relaciones -->
      <svg class="absolute top-0 left-0 w-full h-full" style="z-index: 0">
        <line
          v-for="relationship in relationships"
          :key="relationship.id"
          :x1="getUserPosition(relationship.user.id).x"
          :y1="getUserPosition(relationship.user.id).y"
          :x2="getUserPosition(relationship.peer.id).x"
          :y2="getUserPosition(relationship.peer.id).y"
          :stroke="
            relationship.relationship_type === 'positive'
              ? '#34D399'
              : '#F87171'
          "
          stroke-width="2"
        />
      </svg>
    </div>
    <button
      style="position: absolute; bottom: 10px; right: 10px"
      class="mb-4 px-4 py-2 bg-blue-500 text-white rounded-full shadow-md hover:bg-blue-400"
      @click="navigateTo('/professor/dashboard')"
    >
      Tornar al Dashboard
    </button>
  </div>
</template>

<style scoped>
/* Aplicar transiciones suaves a las líneas */
line {
  transition:
    stroke 0.3s ease,
    stroke-width 0.3s ease;
}
</style>
