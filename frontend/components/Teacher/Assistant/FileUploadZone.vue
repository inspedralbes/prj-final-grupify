<script setup>
import { useKnowledgeStore } from "@/stores/knowledgeStore";
import { useChatStore } from "@/stores/chatStore";

const emit = defineEmits(["file-processed"]);
const dragActive = ref(false);
const isProcessing = ref(false);
const knowledgeStore = useKnowledgeStore();
const chatStore = useChatStore();

const supportedTypes = [
  "text/plain",
  "application/json",
  "text/csv",
  "application/pdf",
  "text/markdown",
];

const handleDrop = async e => {
  e.preventDefault();
  dragActive.value = false;

  const files = e.dataTransfer?.files || e.target.files;
  if (!files?.length) return;

  const file = files[0];
  if (!supportedTypes.includes(file.type)) {
    alert("Pengeu un tipus de fitxer compatible (txt, json, md)");
    return;
  }

  processFile(file);
};

const processFile = async file => {
  isProcessing.value = true;
  try {
    const reader = new FileReader();
    reader.onload = async e => {
      const content = e.target.result;
      emit("file-processed", content);
    };
    reader.readAsText(file);
  } catch (error) {
    console.error("Error amb el fitxer:", error);
    alert("Error en processar el fitxer. Si us plau, torna-ho a provar.");
  } finally {
    isProcessing.value = false;
  }
};

const handleDragOver = e => {
  e.preventDefault();
  dragActive.value = true;
};

const handleDragLeave = () => {
  dragActive.value = false;
};
</script>

<template>
  <div
    class="relative"
    @dragover.prevent="handleDragOver"
    @dragleave.prevent="handleDragLeave"
    @drop.prevent="handleDrop"
  >
    <input
      type="file"
      accept=".txt,.json,.csv,.pdf,.md"
      class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
      @change="handleDrop"
    />
    <div
      class="border-2 border-dashed rounded-lg p-6 text-center transition-all duration-200"
      :class="{
        'border-primary bg-primary/5': dragActive,
        'border-gray-300 hover:border-primary/50': !dragActive,
      }"
    >
      <div class="flex flex-col items-center justify-center space-y-2">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-8 w-8 text-gray-400"
          :class="{ 'text-primary': dragActive }"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
          />
        </svg>
        <div class="text-sm">
          <span class="font-medium text-primary">
            {{
              dragActive
                ? "Deixeu anar el vostre fitxer aquí"
                : "Feu clic per carregar"
            }}
          </span>
          <span class="text-gray-500"> o arrossegar i deixar anar</span>
        </div>
        <p class="text-xs text-gray-500">Fitxers acceptats: TXT, JSON, MD.</p>
      </div>
    </div>
  </div>
</template>
