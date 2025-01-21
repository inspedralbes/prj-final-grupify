<script setup>
import { useChatStore } from "@/stores/chatStore";
import { useKnowledgeStore } from "@/stores/knowledgeStore";

const chatStore = useChatStore();
const knowledgeStore = useKnowledgeStore();
const editingChatId = ref(null);
const editingName = ref("");

const handleNewChat = () => {
  chatStore.createNewChat();
};

const startEditing = chat => {
  editingChatId.value = chat.id;
  editingName.value = chat.name;
};

const saveEditedName = chatId => {
  if (editingName.value.trim()) {
    chatStore.renameChat(chatId, editingName.value);
  }
  editingChatId.value = null;
};

const handleDeleteChat = chatId => {
  if (confirm("Confirmes que vols suprimir aquest xat?")) {
    chatStore.deleteChat(chatId);
  }
};

const formatDate = dateString => {
  return new Date(dateString).toLocaleDateString(undefined, {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
};

const getDocumentCount = chat => {
  return chat.documents?.length || 0;
};
</script>

<template>
  <div class="h-full flex flex-col bg-white border-r border-gray-200">
    <div class="p-4">
      <button
        class="w-full px-4 py-3 bg-primary text-white rounded-lg hover:bg-opacity-90 transition-all transform hover:scale-105 flex items-center justify-center space-x-2 shadow-sm"
        @click="handleNewChat"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
            clip-rule="evenodd"
          />
        </svg>
        <span>Nou Chat</span>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto px-3">
      <div
        v-for="chat in chatStore.chats"
        :key="chat.id"
        class="mb-2 rounded-lg hover:bg-gray-50 transition-colors"
        :class="{ 'bg-gray-50': chat.id === chatStore.currentChatId }"
      >
        <div
          class="p-3 cursor-pointer"
          @click="chatStore.currentChatId = chat.id"
        >
          <div class="flex items-center justify-between mb-1">
            <div class="flex-1 min-w-0">
              <div
                v-if="editingChatId === chat.id"
                class="flex items-center space-x-2"
              >
                <input
                  ref="editInput"
                  v-model="editingName"
                  type="text"
                  class="flex-1 px-2 py-1 text-sm border rounded focus:outline-none focus:ring-2 focus:ring-primary"
                  @keyup.enter="saveEditedName(chat.id)"
                  @blur="saveEditedName(chat.id)"
                />
              </div>
              <div v-else class="flex items-center justify-between">
                <h3 class="font-medium text-gray-900 truncate">
                  {{ chat.name }}
                </h3>
              </div>
            </div>
            <div class="flex items-center space-x-2 ml-2">
              <button
                class="p-1 text-gray-400 hover:text-gray-600"
                title="Rename chat"
                @click.stop="startEditing(chat)"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"
                  />
                </svg>
              </button>
              <button
                class="p-1 text-gray-400 hover:text-red-600"
                title="Delete chat"
                @click.stop="handleDeleteChat(chat.id)"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
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
          <div class="flex items-center justify-between text-xs text-gray-500">
            <span>{{ formatDate(chat.createdAt) }}</span>
            <span>{{ getDocumentCount(chat) }} documents</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chat-list {
  max-height: calc(100vh - 5rem);
}
</style>
