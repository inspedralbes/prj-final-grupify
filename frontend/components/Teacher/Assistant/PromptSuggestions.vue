<script setup>
import { ref } from "vue";

const suggestions = [
  "Com puc identificar situacions de bullying a la meva classe?",
  "Quines estratègies hi ha per millorar la integració del grup?",
  "Com puc ajudar un alumne que està aïllat?",
  "Necessito idees per a activitats de treball en equip",
];

const emit = defineEmits(["select"]);

const handleSelect = suggestion => {
  emit("select", suggestion);
};
</script>

<template>
  <div class="px-4 py-2 space-x-2 flex flex-wrap gap-2">
    <button
      v-for="suggestion in suggestions"
      :key="suggestion"
      class="text-xs px-3 py-1 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors"
      @click="handleSelect(suggestion)"
    >
      {{ suggestion }}
    </button>
  </div>
</template>
