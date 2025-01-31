<script setup>
import { useKnowledgeStore } from "@/stores/knowledgeStore";
import { useChatStore } from "@/stores/chatStore";

const props = defineProps({
  chatId: {
    type: Number,
    required: true,
  },
});

const knowledgeStore = useKnowledgeStore();
const chatStore = useChatStore();

const chatDocuments = computed(() => {
  const chat = chatStore.chats.find(c => c.id === props.chatId);
  if (!chat) return [];

  return chat.documents
    .map(docId => knowledgeStore.getDocumentById(docId))
    .filter(Boolean);
});

const handleDelete = documentId => {
  if (confirm("¿Estás seguro de que quieres eliminar este documento?")) {
    chatStore.removeDocumentFromChat(props.chatId, documentId);
    knowledgeStore.deleteDocument(documentId);
  }
};

const formatFileSize = content => {
  const bytes = new Blob([content]).size;
  if (bytes < 1024) return bytes + " B";
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
  return (bytes / (1024 * 1024)).toFixed(1) + " MB";
};

const formatDate = timestamp => {
  return new Date(timestamp).toLocaleDateString();
};
</script>

<template>
  <div class="space-y-2">
    <div
      v-if="chatDocuments.length === 0"
      class="text-center text-gray-500 text-sm py-2"
    >
      Encara no s'ha penjat cap document
    </div>

    <div
      v-for="doc in chatDocuments"
      v-else
      :key="doc.id"
      class="flex items-center justify-between p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
    >
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-white rounded-lg">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 text-gray-400"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="flex flex-col">
          <span class="text-sm font-medium text-gray-700"
            >Document {{ formatDate(doc.timestamp) }}</span
          >
          <span class="text-xs text-gray-500">{{
            formatFileSize(doc.content)
          }}</span>
        </div>
      </div>

      <button
        class="p-1 text-gray-400 hover:text-red-500 transition-colors"
        @click="handleDelete(doc.id)"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
            clip-rule="evenodd"
          />
        </svg>
      </button>
    </div>
  </div>
</template>
