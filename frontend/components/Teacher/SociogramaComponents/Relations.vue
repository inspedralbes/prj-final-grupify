<script setup>
import { ref, onMounted, defineProps, computed } from "vue";

const width = 800;
const height = 600;

const props = defineProps({
  relationships: {
    type: Array,
    required: true,
    default: () => [],
  },
});

// console.log("esto recibo en relations", props.relationships);
const userPositions = ref({});

// Obtener lista de usuarios únicos
const uniqueUsers = computed(() => {
  const usersMap = new Map();
  props.relationships.forEach(rel => {
    if (rel.user_id && rel.user_name && rel.user_last_name) {
      usersMap.set(rel.user_id, {
        id: rel.user_id,
        name: rel.user_name,
        last_name: rel.user_last_name,
      });
    }
    if (rel.peer_id && rel.peer_name && rel.peer_last_name) {
      usersMap.set(rel.peer_id, {
        id: rel.peer_id,
        name: rel.peer_name,
        last_name: rel.peer_last_name,
      });
    }
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
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div
      class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl p-8 border border-gray-100"
    >
      <div
        class="relative w-full bg-white rounded-xl p-6 border border-gray-100"
        :style="{
          height: `${height}px`,
          width: `${width}px`,
          margin: '0 auto',
        }"
      >
        <!-- Nodes -->
        <div
          v-for="(user, index) in uniqueUsers"
          :key="user.id"
          class="absolute flex items-center justify-center text-white rounded-full shadow-md transition-all duration-300 cursor-pointer group"
          :style="[
            getUserPositionStyle(user.id, index, uniqueUsers.length),
            { width: '80px', height: '80px', zIndex: 1 },
          ]"
        >
          <div
            class="absolute w-full h-full rounded-full bg-gradient-to-br from-blue-500 to-blue-700 group-hover:from-blue-600 group-hover:to-blue-800 transition-all duration-300 opacity-90 group-hover:opacity-100 group-hover:scale-110"
          ></div>
          <span
            class="relative text-sm font-medium leading-tight p-2 text-center"
          >
            {{ user.name }} {{ user.last_name }}
          </span>
        </div>

        <!-- Relationship lines -->
        <svg class="absolute top-0 left-0 w-full h-full" style="z-index: 0">
          <defs>
            <marker
              id="arrowhead-positive"
              markerWidth="10"
              markerHeight="10"
              refX="8"
              refY="5"
              orient="auto"
            >
              <polygon points="0 0, 10 5, 0 10" class="fill-emerald-500" />
            </marker>
            <marker
              id="arrowhead-negative"
              markerWidth="10"
              markerHeight="10"
              refX="8"
              refY="5"
              orient="auto"
            >
              <polygon points="0 0, 10 5, 0 10" class="fill-rose-500" />
            </marker>
          </defs>
          <line
            v-for="relationship in relationships"
            :key="relationship.id"
            :x1="getUserPosition(relationship.user_id).x"
            :y1="getUserPosition(relationship.user_id).y"
            :x2="getUserPosition(relationship.peer_id).x"
            :y2="getUserPosition(relationship.peer_id).y"
            :stroke="
              relationship.relationship_type === 'positive'
                ? '#10B981'
                : '#EF4444'
            "
            :marker-end="
              relationship.relationship_type === 'positive'
                ? 'url(#arrowhead-positive)'
                : 'url(#arrowhead-negative)'
            "
            stroke-width="2.5"
            class="relationship-line transition-all duration-300 hover:stroke-[3px]"
            :class="{
              'opacity-80 hover:opacity-100': true,
              'stroke-emerald-500':
                relationship.relationship_type === 'positive',
              'stroke-rose-500': relationship.relationship_type === 'negative',
            }"
          />
        </svg>
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
