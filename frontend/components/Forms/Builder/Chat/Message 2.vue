<script setup>
import { CheckCircleIcon } from "@heroicons/vue/24/outline";
import { useTypingEffect } from "@/components/utils/chat";

const props = defineProps({
  message: {
    type: Object,
    required: true,
  },
  isLast: Boolean,
});

const { content, isTyping, typeMessage } = useTypingEffect();

onMounted(() => {
  if (props.message.role === "assistant") {
    typeMessage(props.message.content);
  } else {
    content.value = props.message.content;
    isTyping.value = false;
  }
});
</script>

<template>
  <div
    class="message-container"
    :class="[
      message.role === 'user' ? 'justify-end' : 'justify-start',
      'mb-4 animate-fade-in',
    ]"
  >
    <div
      class="max-w-[80%] rounded-lg p-4"
      :class="[
        message.role === 'user'
          ? 'bg-primary text-white ml-auto'
          : 'bg-gray-100 text-gray-800',
      ]"
    >
      <p class="message-text" :class="{ typing: isTyping }">
        {{ content }}<span v-if="isTyping" class="typing-cursor">|</span>
      </p>

      <div
        v-if="message.role === 'assistant' && message.questions"
        class="mt-4 space-y-2 animate-fade-in"
      >
        <div
          v-for="(question, index) in message.questions"
          :key="index"
          class="bg-white p-3 rounded border border-gray-200 shadow-sm hover:border-primary transition-colors duration-200"
        >
          <div class="flex items-start gap-2">
            <CheckCircleIcon
              class="w-5 h-5 text-primary flex-shrink-0 mt-0.5"
            />
            <div>
              <p class="font-medium">{{ question.title }}</p>
              <p class="text-sm text-gray-500">{{ question.type }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.message-container {
  display: flex;
  opacity: 0;
  animation: fadeIn 0.3s ease-out forwards;
}

.message-text {
  line-height: 1.5;
}

.typing-cursor {
  animation: blink 1s step-end infinite;
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

@keyframes blink {
  from,
  to {
    opacity: 1;
  }
  50% {
    opacity: 0;
  }
}
</style>
