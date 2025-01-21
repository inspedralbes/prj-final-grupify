<script setup>
import { useChatStore } from "@/stores/chatStore";

const chatStore = useChatStore();

const currentChat = computed(() => {
  return chatStore.chats.find(chat => chat.id === chatStore.currentChatId);
});

const documentCount = computed(() => {
  return currentChat.value?.documents?.length || 0;
});

const showDocuments = ref(false);

const toggleDocuments = () => {
  showDocuments.value = !showDocuments.value;
};
</script>

<template>
  <div>
    <div class="p-4 border-b flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div
          class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center"
        >
          <span class="text-white text-xl">AI</span>
        </div>
        <div>
          <h2 class="font-semibold text-gray-800">Auxiliar docent</h2>
          <div class="flex items-center">
            <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
            <span class="text-sm text-gray-500">Online</span>
          </div>
        </div>
      </div>
      <button
        class="text-sm text-gray-500 hover:text-gray-700 flex items-center space-x-1"
        @click="toggleDocuments"
      >
        <span>{{ documentCount }} documents</span>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-4 w-4"
          :class="{ 'transform rotate-180': showDocuments }"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
            clip-rule="evenodd"
          />
        </svg>
      </button>
    </div>
    <div v-if="showDocuments" class="border-b p-4">
      <TeacherAssistantDocumentList :chat-id="chatStore.currentChatId" />
    </div>
  </div>
</template>
