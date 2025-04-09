<script setup>
import { SparklesIcon } from "@heroicons/vue/24/outline";

defineProps({
  suggestions: {
    type: Array,
    required: true,
  },
});

defineEmits(["select"]);
</script>

<template>
  <div class="space-y-2">
    <p class="text-xs text-gray-500 flex items-center gap-1.5">
      <SparklesIcon class="w-3.5 h-3.5" />
      Suggeriments
    </p>
    <div class="flex flex-wrap gap-1.5">
      <button
        v-for="(suggestion, index) in suggestions"
        :key="index"
        class="px-2.5 py-1 text-xs rounded-full bg-gray-50 text-gray-600 hover:bg-primary/5 hover:text-primary transition-colors duration-200"
        @click="$emit('select', suggestion)"
      >
        {{ suggestion }}
      </button>
    </div>
  </div>
</template>
