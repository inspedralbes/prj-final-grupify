<script setup>
const props = defineProps({
  messages: {
    type: Array,
    required: true,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
});

const messagesContainer = ref(null);

const scrollToBottom = () => {
  if (messagesContainer.value) {
    const container = messagesContainer.value;
    container.scrollTop = container.scrollHeight;
  }
};

onMounted(scrollToBottom);
onUpdated(scrollToBottom);

watch(() => props.messages.length, scrollToBottom);
watch(() => props.isLoading, scrollToBottom);
</script>

<template>
  <div ref="messagesContainer" class="messages">
    <div class="messages__wrapper">
      <TeacherAssistantTransitionGroup name="message">
        <TeacherAssistantMessage
          v-for="message in messages"
          :key="message.id"
          :message="message"
          class="message-item"
        />
      </TeacherAssistantTransitionGroup>
      <TeacherAssistantTypingIndicator
        v-if="isLoading"
        class="typing-indicator"
      />
    </div>
  </div>
</template>

<style scoped>
.messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  scroll-behavior: smooth;
  background-color: #f8f9fa;
}

.messages__wrapper {
  max-width: 900px;
  margin: 0 auto;
}

.messages::-webkit-scrollbar {
  width: 6px;
}

.messages::-webkit-scrollbar-track {
  background: transparent;
}

.messages::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.2);
  border-radius: 3px;
}

.messages::-webkit-scrollbar-thumb:hover {
  background-color: rgba(0, 0, 0, 0.3);
}

.message-enter-active,
.message-leave-active {
  transition: all 0.3s ease;
}

.message-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.message-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

.typing-indicator {
  margin-top: 0.5rem;
}

@media (max-width: 768px) {
  .messages {
    padding: 0.5rem;
  }
}
</style>
