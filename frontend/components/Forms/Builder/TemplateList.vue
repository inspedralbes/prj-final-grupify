<script setup>
import { ref, computed } from "vue";
import { DocumentDuplicateIcon, PencilIcon } from "@heroicons/vue/24/outline";
import { useFormTemplatesStore } from "@/stores/formTemplates";

const emit = defineEmits(["select"]);
const formTemplatesStore = useFormTemplatesStore();
const selectedCategory = ref("all");

const categories = {
  all: "Totes les plantilles",
  ...formTemplatesStore.categories,
};

const filteredTemplates = computed(() => {
  if (selectedCategory.value === "all") {
    return formTemplatesStore.templates;
  }
  return formTemplatesStore.getTemplatesByCategory(selectedCategory.value);
});
</script>

<template>
  <div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-semibold text-gray-800">
        Plantilles de Formularis
      </h3>
      <select
        v-model="selectedCategory"
        class="px-3 py-1.5 text-sm border rounded-lg text-gray-600"
      >
        <option v-for="(name, key) in categories" :key="key" :value="key">
          {{ name }}
        </option>
      </select>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="template in filteredTemplates"
        :key="template.id"
        class="border rounded-lg p-4 hover:border-primary/30 hover:bg-primary/5 transition-colors duration-200"
      >
        <div class="flex justify-between items-start mb-2">
          <h4 class="font-medium text-gray-800">{{ template.title }}</h4>
          <div class="flex gap-2">
            <button
              class="p-1.5 text-gray-400 hover:text-primary rounded-full hover:bg-primary/10 transition-colors"
              title="Usar plantilla"
              @click="emit('select', { ...template })"
            >
              <DocumentDuplicateIcon class="w-4 h-4" />
            </button>
            <button
              class="p-1.5 text-gray-400 hover:text-primary rounded-full hover:bg-primary/10 transition-colors"
              title="Editar plantilla"
              @click="emit('select', { ...template, edit: true })"
            >
              <PencilIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
        <p class="text-sm text-gray-500 mb-2">{{ template.description }}</p>
        <div class="text-xs text-gray-400">
          {{ template.questions.length }} preguntes
        </div>
      </div>
    </div>
  </div>
</template>
