<script setup>
import { useChatStore } from "@/stores/chatStore";
import { useChat } from "@/composables/useChat";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const chatStore = useChatStore();
const { sendMessage, isLoading, processFile } = useChat();

onMounted(() => {
  chatStore.loadFromLocalStorage();
});

const handleSend = async message => {
  if (!chatStore.currentChatId) {
    chatStore.createNewChat();
  }
  await sendMessage(message, chatStore.currentChatId);
};

const handleFileContent = async content => {
  if (!chatStore.currentChatId) {
    chatStore.createNewChat();
  }
  await processFile(content, chatStore.currentChatId);
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Barra de navegación -->
    <DashboardNavTeacher />

    <div class="flex flex-1">
      <!-- Sidebar -->
      <div class="w-80 h-full flex-shrink-0">
        <TeacherAssistantChatSidebar />
      </div>

      <!-- Área principal de chat -->
      <div class="flex-1 h-screen flex flex-col bg-white">
        <TeacherAssistantChatHeader />
        <TeacherAssistantMessageList
          :messages="chatStore.currentChat?.messages || []"
          :is-loading="isLoading"
        />
        <div class="border-t border-gray-100 py-4">
          <TeacherAssistantMessageInput
            :is-loading="isLoading"
            @send="handleSend"
            @file-processed="handleFileContent"
          />
        </div>
      </div>
    </div>
  </div>
</template>
