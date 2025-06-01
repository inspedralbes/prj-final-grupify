<template>
  <div class="flex items-start space-x-2 p-4 max-w-[80%]">
    <div
      class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0"
    >
      <span class="text-primary text-sm">IA</span>
    </div>
    <div
      class="bg-gray-100 rounded-2xl rounded-tl-none p-4 inline-flex space-x-1"
    >
      <span
        class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
        style="animation-delay: 0ms"
      ></span>
      <span
        class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
        style="animation-delay: 150ms"
      ></span>
      <span
        class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"
        style="animation-delay: 300ms"
      ></span>
    </div>
  </div>
</template>

<style scoped>
.animate-bounce {
  animation: bounce 1s infinite;
}

@keyframes bounce {
  0%,
  100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}
</style>
