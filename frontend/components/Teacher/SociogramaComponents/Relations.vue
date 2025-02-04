<script setup>
import { ref, onMounted, defineProps, computed } from "vue";

const width = 800;
const height = 600;

const props = defineProps({
  relationships: {
    type: Array,
    required: true,
  },
});

const userPositions = ref({});

// Obtener lista de usuarios únicos
const uniqueUsers = computed(() => {
  const usersMap = new Map();
  props.relationships.forEach((rel) => {
    usersMap.set(rel.user.id, rel.user);
    usersMap.set(rel.peer.id, rel.peer);
  });
  return Array.from(usersMap.values());
});

const getUserPosition = (userId, index, total) => {
  const radius = Math.min(width, height) / 2 - 80;
  const angle = (2 * Math.PI * index) / total + Math.PI / 4;
  const centerX = width / 2;
  const centerY = height / 2;

  if (!userPositions.value[userId]) {
    userPositions.value[userId] = {
      x: centerX + radius * Math.cos(angle),
      y: centerY + radius * Math.sin(angle),
    };
  }

  return userPositions.value[userId];
};

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
    <div class="min-h-screen bg-gray-100">
  
      <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">
            Sociograma de Relacions a Classe
          </h1>
  
          <div
            class="relative w-full bg-gray-50 rounded-lg p-4"
            :style="{
              height: `${height}px`,
              width: `${width}px`,
              margin: '0 auto',
            }"
          >
            <!-- Nodes -->
            <div
              v-for="(relationship, index) in relationships"
              :key="relationship.id"
              class="absolute flex items-center justify-center bg-blue-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110"
              :style="[
                getUserPositionStyle(
                  relationship.user.id,
                  index,
                  relationships.length
                ),
                { width: '70px', height: '70px' },
              ]"
            >
              <span class="text-sm font-medium leading-tight p-1">
                {{ relationship.user.name }} {{ relationship.user.last_name }}
              </span>
            </div>
  
            <div
              v-for="(relationship, index) in relationships"
              :key="`peer-${relationship.id}`"
              class="absolute flex items-center justify-center bg-indigo-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-110"
              :style="[
                getUserPositionStyle(
                  relationship.peer.id,
                  index,
                  relationships.length
                ),
                { width: '70px', height: '70px', zIndex: 1 },
              ]"
            >
              <span class="text-sm font-medium leading-tight p-1">
                {{ relationship.peer.name }} {{ relationship.peer.last_name }}
              </span>
            </div>
  
            <!-- Relationship lines -->
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
                    ? '#10B981'
                    : '#EF4444'
                "
                stroke-width="2"
                class="relationship-line"
              />
            </svg>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <style scoped>
  .relationship-line {
    transition: all 0.3s ease;
  }
  
  .relationship-line:hover {
    stroke-width: 4;
    filter: drop-shadow(0 0 2px rgba(0, 0, 0, 0.3));
  }
  </style>
