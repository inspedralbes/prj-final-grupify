<script setup>
const emit = defineEmits(["send", "file-processed"]);
const props = defineProps({
  isLoading: Boolean,
});

const input = ref("");
const showUploadZone = ref(false);

const handleSubmit = () => {
  if (!input.value.trim() || props.isLoading) return;
  emit("send", input.value);
  input.value = "";
};

const handleFileProcessed = content => {
  emit("file-processed", content);
  showUploadZone.value = false;
};

const toggleUploadZone = () => {
  showUploadZone.value = !showUploadZone.value;
};

const handleSuggestionSelect = suggestion => {
  input.value = suggestion;
};
</script>

<template>
  <div class="space-y-4">
    <TeacherAssistantPromptSuggestions @select="handleSuggestionSelect" />

    <div v-if="showUploadZone" class="px-4">
      <TeacherAssistantFileUploadZone @file-processed="handleFileProcessed" />
    </div>

    <div class="flex items-center space-x-2 px-4">
      <button
        class="p-2 rounded-full hover:bg-gray-100 transition-colors"
        :class="{ 'text-primary bg-primary/10': showUploadZone }"
        title="Upload file"
        @click="toggleUploadZone"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z"
            clip-rule="evenodd"
          />
        </svg>
      </button>

      <input
        v-model="input"
        type="text"
        placeholder="Escriu el teu missatge..."
        class="flex-1 p-4 rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
        @keyup.enter="handleSubmit"
      />

      <button
        :disabled="isLoading"
        class="p-4 rounded-full bg-primary text-white hover:bg-opacity-90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        @click="handleSubmit"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"
          />
        </svg>
      </button>
    </div>
  </div>
</template>
