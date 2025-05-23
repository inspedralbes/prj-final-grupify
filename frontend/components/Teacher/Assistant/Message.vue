<script setup>
import { marked } from "marked";

const props = defineProps({
  message: {
    type: Object,
    required: true,
  },
});

const isUser = computed(() => props.message.type === "user");

const formattedContent = computed(() => {
  if (isUser.value) {
    return props.message.content;
  }
  return marked(props.message.content);
});

const messageTime = computed(() => {
  const date = new Date(props.message.timestamp);
  return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
});
</script>

<template>
  <div v-motion-slide-up :class="['message', { 'message--user': isUser }]">
    <div class="message__container">
      <div v-if="!isUser" class="message__avatar">
        <svg viewBox="0 0 24 24" class="icon">
          <path
            fill="currentColor"
            d="M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12,8.39C13.57,9.4 15.42,10 17.42,10C18.2,10 18.95,9.91 19.67,9.74C19.88,10.45 20,11.21 20,12C20,16.41 16.41,20 12,20C9,20 6.39,18.34 5,15.89L6.75,14V13A1.25,1.25 0 0,1 8,11.75A1.25,1.25 0 0,1 9.25,13V14H12M16,11.75A1.25,1.25 0 0,0 14.75,13A1.25,1.25 0 0,0 16,14.25A1.25,1.25 0 0,0 17.25,13A1.25,1.25 0 0,0 16,11.75Z"
          />
        </svg>
      </div>
      <div class="message__content">
        <div class="message__bubble" v-html="formattedContent"></div>
        <div class="message__time">{{ messageTime }}</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.message {
  padding: 0.5rem 1rem;
  margin: 0.5rem 0;
  animation: fadeIn 0.3s ease-in-out;
}

.message__container {
  display: flex;
  gap: 0.5rem;
  max-width: 80%;
}

.message--user .message__container {
  flex-direction: row-reverse;
  margin-left: auto;
}

.message__avatar {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background-color: #e0e0e0;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.message__avatar .icon {
  width: 1.5rem;
  height: 1.5rem;
  color: #666;
}

.message__content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.message__bubble {
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  background-color: #f5f5f5;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  white-space: pre-wrap;
  line-height: 1.5;
}

.message--user .message__bubble {
  background-color: #0084ff;
  color: white;
}

.message__time {
  font-size: 0.75rem;
  color: #666;
  margin: 0 0.5rem;
}

.message--user .message__time {
  text-align: right;
}

/* Markdown styles */
.message__bubble :deep(p) {
  margin: 0;
}

.message__bubble :deep(pre) {
  background-color: rgba(0, 0, 0, 0.05);
  padding: 0.5rem;
  border-radius: 0.25rem;
  overflow-x: auto;
}

.message--user .message__bubble :deep(pre) {
  background-color: rgba(0, 0, 0, 0.2);
}

.message__bubble :deep(code) {
  font-family: monospace;
  font-size: 0.9em;
}

.message__bubble :deep(a) {
  color: #0066cc;
  text-decoration: none;
}

.message--user .message__bubble :deep(a) {
  color: #fff;
  text-decoration: underline;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
