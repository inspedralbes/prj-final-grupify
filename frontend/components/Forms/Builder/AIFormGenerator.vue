<script setup>
import { generateFormQuestions } from "@/services/openai";
import { useScrollToBottom } from "@/components/utils/chat";
import { useFormSuggestions } from "@/components/utils/formSuggestions";
import { selectFallbackTemplate } from "@/components/utils/formFallbacks";

const emit = defineEmits(["generate-questions"]);

const chatHistory = ref([]);
const isGenerating = ref(false);
const chatContainerRef = ref(null);
const suggestionRotationInterval = ref(null);
const userInput = ref('');
const retryCount = ref(0);
const MAX_RETRIES = 3;

const { scrollToBottom } = useScrollToBottom(chatContainerRef);
const { getCurrentSuggestions, rotateSuggestions } = useFormSuggestions();

const currentSuggestions = ref(getCurrentSuggestions());

const generateAIResponse = async (message) => {
  isGenerating.value = true;
  retryCount.value = 0;
  
  try {
    // Add thinking message
    chatHistory.value.push({
      role: "assistant",
      content: "Estic generant el formulari...",
      isThinking: true
    });
    
    scrollToBottom();
    
    // Try to generate form with retries
    const generateWithRetries = async () => {
      try {
        return await generateFormQuestions(message);
      } catch (error) {
        if (retryCount.value < MAX_RETRIES) {
          retryCount.value++;
          
          // Update the thinking message to show retrying
          const thinkingIndex = chatHistory.value.findIndex(msg => msg.isThinking);
          if (thinkingIndex !== -1) {
            chatHistory.value[thinkingIndex].content = 
              `Estic generant el formulari... (Intent ${retryCount.value}/${MAX_RETRIES})`;
            scrollToBottom();
          }
          
          // Wait a moment before retrying
          await new Promise(resolve => setTimeout(resolve, 1000));
          return await generateWithRetries();
        } else {
          throw error;
        }
      }
    };
    
    const response = await generateWithRetries();

    // Remove thinking message
    chatHistory.value = chatHistory.value.filter(msg => !msg.isThinking);
    
    // Add success message
    chatHistory.value.push({
      role: "assistant",
      content: "He generat un formulari basat en les vostres necessitats. Vols ajustar alguna pregunta?",
      success: true,
    });

    emit("generate-questions", response);
  } catch (error) {
    console.error("Error en la generació:", error);
    
    // Remove thinking message
    chatHistory.value = chatHistory.value.filter(msg => !msg.isThinking);
    
    // Add error message with suggestions
    const errorMessage = error.message || "Hi ha hagut un error inesperat.";
    
    chatHistory.value.push({
      role: "assistant",
      content: errorMessage,
      error: true,
    });
    
    // Añadir un mensaje adicional con sugerencias específicas
    if (message.toLowerCase().includes("comprensió lectora")) {
      chatHistory.value.push({
        role: "assistant",
        content: "Prova amb una descripció més específica com: 'Crea un test de comprensió lectora amb un text breu sobre animals marins per a alumnes de 3r de primària amb 5 preguntes de tipus test.'",
        suggestion: true,
      });
      
      // Intentar usar una plantilla de respaldo después de un breve retraso
      setTimeout(() => {
        try {
          const fallbackTemplate = selectFallbackTemplate(message);
          chatHistory.value.push({
            role: "assistant",
            content: "He preparat un formulari alternatiu que pots utilitzar com a base:",
            info: true,
          });
          
          // Emitir la plantilla de respaldo
          emit("generate-questions", fallbackTemplate);
          
        } catch (fallbackError) {
          console.error("Error al usar plantilla de respaldo:", fallbackError);
        } finally {
          scrollToBottom();
        }
      }, 2000);
    } else if (message.toLowerCase().includes("test") || message.toLowerCase().includes("avaluació")) {
      chatHistory.value.push({
        role: "assistant",
        content: "Prova a especificar el curs, tema i nombre de preguntes. Per exemple: 'Test de matemàtiques sobre fraccions per a 1r d'ESO amb 8 preguntes variades.'",
        suggestion: true,
      });
      
      // Intentar usar una plantilla de respaldo si contiene ciertas palabras clave
      if (message.toLowerCase().includes("matemàtiques") || 
          message.toLowerCase().includes("estudi") || 
          message.toLowerCase().includes("hàbits")) {
        setTimeout(() => {
          try {
            const fallbackTemplate = selectFallbackTemplate(message);
            chatHistory.value.push({
              role: "assistant",
              content: "He preparat un formulari alternatiu que pots utilitzar com a base:",
              info: true,
            });
            
            // Emitir la plantilla de respaldo
            emit("generate-questions", fallbackTemplate);
            
          } catch (fallbackError) {
            console.error("Error al usar plantilla de respaldo:", fallbackError);
          } finally {
            scrollToBottom();
          }
        }, 2000);
      }
    }
  } finally {
    isGenerating.value = false;
    scrollToBottom();
  }
};

const sendMessage = async (message) => {
  // Don't send empty messages or when already generating
  if (!message.trim() || isGenerating.value) return;
  
  // Clear input after sending
  if (message === userInput.value) {
    userInput.value = '';
  }

  // Add user message to chat history
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

const handleKeyDown = (event) => {
  // Send message on Enter (not with Shift)
  if (event.key === 'Enter' && !event.shiftKey && !isGenerating.value) {
    event.preventDefault();
    sendMessage(userInput.value);
  }
};

onMounted(() => {
  // Initial welcome message
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
        Descriu el tipus de formulari que necessites (potenciat per OpenAI)
      </p>
    </div>

    <!-- Chat History -->
    <div
      ref="chatContainerRef"
      class="flex-1 overflow-y-auto p-4 space-y-4 scroll-smooth"
    >
      <div 
        v-for="(message, index) in chatHistory"
        :key="index"
        :class="[
          'p-3 rounded-lg max-w-[85%]',
          message.role === 'user' 
            ? 'ml-auto bg-primary text-white' 
            : 'bg-gray-100 text-gray-800',
          message.error ? 'bg-red-100 text-red-700 border border-red-200' : '',
          message.success ? 'bg-green-100 text-green-700 border border-green-200' : '',
          message.isThinking ? 'bg-blue-50 text-blue-700 border border-blue-100 animate-pulse' : '',
          message.suggestion ? 'bg-yellow-50 text-yellow-700 border border-yellow-200 mt-2' : '',
          message.info ? 'bg-blue-50 text-blue-700 border border-blue-200 mt-2' : '',
        ]"
      >
        {{ message.content }}
      </div>

      <div
        v-if="isGenerating && !chatHistory.some(m => m.isThinking)"
        class="flex items-center space-x-2 text-gray-500 p-2"
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
      <!-- Suggestions -->
      <div class="flex flex-wrap gap-2">
        <button
          v-for="(suggestion, index) in currentSuggestions"
          :key="index"
          @click="selectSuggestion(suggestion)"
          :disabled="isGenerating"
          class="text-xs px-3 py-1.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ suggestion }}
        </button>
      </div>

      <!-- Chat Input -->
      <div class="relative">
        <textarea
          v-model="userInput"
          :disabled="isGenerating"
          @keydown="handleKeyDown"
          placeholder="Descriu el formulari que necessites..."
          class="w-full px-4 py-2 pr-12 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
          rows="2"
        ></textarea>
        
        <button
          @click="sendMessage(userInput)"
          :disabled="isGenerating || !userInput.trim()"
          class="absolute right-2 bottom-2 p-1.5 rounded-full bg-primary text-white hover:bg-primary-dark transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>