<template>
  <div
    v-if="visible"
    :class="[
      'fixed bottom-4 left-1/2 transform -translate-x-1/2',
      toastTypeClass,
      'px-4 py-2 rounded-lg shadow-lg text-white',
    ]"
  >
    {{ message }}
  </div>
</template>

<script setup>
import { ref, watch, defineProps } from "vue";

const props = defineProps({
  message: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: "success", // 'success', 'error', 'info', etc.
  },
});

const visible = ref(true);
const toastTypeClass = ref("");

watch(
  () => props.type,
  newType => {
    if (newType === "error") {
      toastTypeClass.value = "bg-red-500";
    } else if (newType === "info") {
      toastTypeClass.value = "bg-blue-500";
    } else {
      toastTypeClass.value = "bg-green-500";
    }
  },
  { immediate: true }
);

setTimeout(() => {
  visible.value = false;
}, 3000); // Desaparece después de 3 segundos
</script>

<style scoped>
/* Puedes personalizar el estilo aquí */
</style>
