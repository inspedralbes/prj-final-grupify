<script setup>
import { ref } from "vue";
import { PaperAirplaneIcon } from "@heroicons/vue/24/outline";

const emit = defineEmits(["send"]);
const message = ref("");
const inputRef = ref(null);

const sendMessage = () => {
  if (!message.value.trim() || props.disabled) return;

  emit("send", message.value);
  message.value = "";
  inputRef.value?.focus();
};

const props = defineProps({
  disabled: Boolean,
  placeholder: {
    type: String,
    default: "Escribe tu mensaje...",
  },
});
</script>

<template>
  <div class="relative">
    <input
      ref="inputRef"
      v-model="message"
      type="text"
      :placeholder="placeholder"
      class="w-full px-4 py-3 pr-12 rounded-lg border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent transition-shadow duration-200"
      :class="{ 'opacity-50 cursor-not-allowed': disabled }"
      :disabled="disabled"
      @keyup.enter="sendMessage"
    />
    <button
      class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-primary hover:bg-primary/10 rounded-full transition-colors duration-200"
      :disabled="disabled || !message.trim()"
      :class="{ 'opacity-50 cursor-not-allowed': disabled || !message.trim() }"
      @click="sendMessage"
    >
      <PaperAirplaneIcon class="w-5 h-5" />
    </button>
  </div>
</template>
