<template>
  <div class="notes-list space-y-4">
    <div
      v-for="note in notes"
      :key="note.id"
      class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer"
      @click="$emit('select', note)"
    >
      <h3 class="font-semibold text-lg">{{ note.title }}</h3>
      <p class="text-sm text-gray-600 dark:text-gray-400">{{ note.subject }}</p>
      <p class="text-xs text-gray-500 dark:text-gray-500">
        Last modified: {{ new Date(note.lastModified).toLocaleDateString() }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Note } from '~/types';

defineProps<{
  notes: Note[];
}>();

defineEmits<{
  (e: 'select', note: Note): void;
}>();
</script>
