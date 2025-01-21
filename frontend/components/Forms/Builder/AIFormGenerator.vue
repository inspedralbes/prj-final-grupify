<script setup>
import { generateFormQuestions } from "@/services/gemini";
import { useScrollToBottom } from "@/components/utils/chat";
import { useFormSuggestions } from "@/components/utils/formSuggestions";

const emit = defineEmits(["generate-questions"]);

const chatHistory = ref([]);
const isGenerating = ref(false);
const chatContainerRef = ref(null);
const suggestionRotationInterval = ref(null);

const { scrollToBottom } = useScrollToBottom(chatContainerRef);
const { getCurrentSuggestions, rotateSuggestions } = useFormSuggestions();

const currentSuggestions = ref(getCurrentSuggestions());

const generateAIResponse = async message => {
  isGenerating.value = true;

  try {
    const response = await generateFormQuestions(message);

    chatHistory.value.push({
      role: "assistant",
      content:
        "He generat un formulari basat en les vostres necessitats. Vols ajustar alguna pregunta?",
      success: true,
    });

    emit("generate-questions", response);
  } catch (error) {
    console.error("Error en la generació:", error);
    chatHistory.value.push({
      role: "assistant",
      content:
        error.message ||
        "Hi ha hagut un error inesperat. Si us plau, intenta de nou.",
      error: true,
    });
  } finally {
    isGenerating.value = false;
    scrollToBottom();
  }
};

const sendMessage = async message => {
  if (!message.trim() || isGenerating.value) return;

  chatHistory.value.push({
    role: "user",
    content: message,
  });

  scrollToBottom();
  await generateAIResponse(message);
};

const selectSuggestion = suggestion => {
  sendMessage(suggestion);
};

const rotateSuggestionsInterval = () => {
  currentSuggestions.value = rotateSuggestions();
};

onMounted(() => {
  chatHistory.value.push({
    role: "assistant",
    content:
      "Hola! Sóc el teu assistent per crear formularis. Descriu el tipus de formulari que necessites i t'ajudaré a generar-ho.",
  });

  // Rotate suggestions every 20 seconds
  suggestionRotationInterval.value = setInterval(
    rotateSuggestionsInterval,
    20000
  );
});

onUnmounted(() => {
  if (suggestionRotationInterval.value) {
    clearInterval(suggestionRotationInterval.value);
  }
});
</script>

<template>
  <div class="bg-white rounded-lg shadow-md flex flex-col h-[600px]">
    <!-- Header -->
    <div class="p-4 border-b">
      <h3 class="text-lg font-semibold text-gray-800">
        Assistent IA per a Formularis
      </h3>
      <p class="text-sm text-gray-500">
        Descriu el tipus de formulari que necessites
      </p>
    </div>

    <!-- Chat History -->
    <div
      ref="chatContainerRef"
      class="flex-1 overflow-y-auto p-4 space-y-4 scroll-smooth"
    >
      <FormsBuilderChatMessage
        v-for="(message, index) in chatHistory"
        :key="index"
        :message="message"
        :is-last="index === chatHistory.length - 1"
      />

      <div
        v-if="isGenerating"
        class="flex items-center space-x-2 text-gray-500 animate-pulse"
      >
        <div class="flex space-x-1">
          <div class="w-2 h-2 rounded-full bg-gray-400 animate-bounce"></div>
          <div
            class="w-2 h-2 rounded-full bg-gray-400 animate-bounce"
            style="animation-delay: 0.2s"
          ></div>
          <div
            class="w-2 h-2 rounded-full bg-gray-400 animate-bounce"
            style="animation-delay: 0.4s"
          ></div>
        </div>
        <span class="text-sm">Generant formulari...</span>
      </div>
    </div>

    <!-- Input Area -->
    <div class="p-4 border-t space-y-3">
      <FormsBuilderChatSuggestions
        :suggestions="currentSuggestions"
        @select="selectSuggestion"
      />

      <FormsBuilderChatInput
        :disabled="isGenerating"
        placeholder="Descriu el formulari que necessites..."
        @send="sendMessage"
      />
    </div>
  </div>
</template>
