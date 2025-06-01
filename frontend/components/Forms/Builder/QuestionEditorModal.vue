<script setup>
import { XMarkIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
  modelValue: Boolean,
  question: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["update:modelValue", "save"]);

const localQuestion = ref(null);

watch(
  () => props.question,
  newQuestion => {
    if (newQuestion) {
      localQuestion.value = JSON.parse(JSON.stringify(newQuestion));
    }
  },
  { immediate: true }
);

const addOption = () => {
  if (!localQuestion.value.options) {
    localQuestion.value.options = [];
  }
  localQuestion.value.options.push({
    text: "",
    value: localQuestion.value.options.length,
  });
};

const removeOption = index => {
  localQuestion.value.options.splice(index, 1);
  // Reindex values
  localQuestion.value.options = localQuestion.value.options.map((opt, idx) => ({
    ...opt,
    value: idx,
  }));
};

const handleSave = () => {
  emit("save", localQuestion.value);
  emit("update:modelValue", false);
};

const close = () => {
  emit("update:modelValue", false);
};
</script>

<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>

    <!-- Modal -->
    <div
      class="flex min-h-full items-end justify-center p-4 sm:items-center sm:p-0"
    >
      <div
        class="relative transform rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
      >
        <!-- Close button -->
        <button
          class="absolute right-4 top-4 text-gray-400 hover:text-gray-500"
          @click="close"
        >
          <XMarkIcon class="h-6 w-6" />
        </button>

        <!-- Content -->
        <div class="mt-3 sm:mt-5">
          <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
            Editar Pregunta
          </h3>

          <div class="space-y-4">
            <!-- Question Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Pregunta
              </label>
              <input
                v-model="localQuestion.title"
                type="text"
                class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent"
              />
            </div>

            <!-- Options (for multiple/checkbox) -->
            <div v-if="['multiple', 'checkbox'].includes(localQuestion?.type)">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Opcions
              </label>
              <div class="space-y-2">
                <div
                  v-for="(option, index) in localQuestion.options"
                  :key="index"
                  class="flex items-center space-x-2"
                >
                  <input
                    v-model="option.text"
                    type="text"
                    class="flex-1 px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent"
                    :placeholder="`Opción ${index + 1}`"
                  />
                  <button
                    class="text-red-500 hover:text-red-700"
                    @click="removeOption(index)"
                  >
                    ×
                  </button>
                </div>
                <button
                  class="text-primary hover:text-primary/80 text-sm font-medium"
                  @click="addOption"
                >
                  + Afegir opcions
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mt-5 sm:mt-6 flex justify-end space-x-3">
          <button
            class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800"
            @click="close"
          >
            Cancelar
          </button>
          <button
            class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-md hover:bg-primary/90"
            @click="handleSave"
          >
            Desar canvis
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
