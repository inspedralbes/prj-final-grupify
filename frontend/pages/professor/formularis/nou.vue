<script setup>
import { regenerateQuestion } from "@/components/utils/aiQuestions";

const questions = ref([]);
const formTitle = ref("");
const formDescription = ref("");
const formContext = ref("");
const showAssignModal = ref(false);

const goBack = () => {
  navigateTo("/professor/formularis");
};

const handleGeneratedQuestions = response => {
  formTitle.value = response.title;
  formDescription.value = response.description;
  formContext.value = response.description;
  questions.value = response.questions.map(q => ({
    ...q,
    id: Date.now() + Math.random(),
  }));
};

const handleTemplateSelect = template => {
  formTitle.value = template.title;
  formDescription.value = template.description;
  formContext.value = template.description;
  questions.value = template.questions.map(q => ({
    ...q,
    id: Date.now() + Math.random(),
  }));
};

const handleEditQuestion = ({ question }) => {
  questions.value = questions.value.map(q =>
    q.id === question.id ? question : q
  );
};

const handleRegenerateQuestion = async question => {
  try {
    const newQuestion = await regenerateQuestion(question, formContext.value);
    questions.value = questions.value.map(q =>
      q.id === question.id ? newQuestion : q
    );
  } catch (error) {
    console.error("Error al regenerar la pregunta:", error);
  }
};

const saveForm = () => {
  const form = {
    id: Date.now(),
    title: formTitle.value,
    description: formDescription.value,
    questions: questions.value,
    division: "active",
    responses: 0,
    createdAt: new Date().toISOString(),
  };

  console.log("Guardant formulari:", form);
  alert("Formulari guardat correctament");
  navigateTo("/professor/formularis");
};

const handleSendForm = () => {
  if (!formTitle.value || !questions.value.length) {
    alert("Completa el formulari abans d'enviar");
    return;
  }
  showAssignModal.value = true;
};

const handleFormAssigned = assignments => {
  console.log("Formulari assignat:", assignments);
  alert("Formulari enviat correctament als alumnes seleccionats");
  navigateTo("/professor/formularis");
};
</script>

<template>
  <div class="min-h-screen bg-background">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <button
              class="mr-4 text-gray-600 hover:text-gray-900"
              @click="goBack"
            >
              ← Tornar
            </button>
            <h1 class="text-2xl font-bold text-gray-900">Crear formulari</h1>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="space-y-8">
          <!-- AI Generator -->
          <FormsBuilderAIFormGenerator
            @generate-questions="handleGeneratedQuestions"
          />

          <!-- Templates -->
          <FormsBuilderTemplateList @select="handleTemplateSelect" />
        </div>

        <!-- Form Preview -->
        <div class="sticky top-8">
          <FormsBuilderPreview
            :questions="questions"
            :title="formTitle"
            :description="formDescription"
            :context="formContext"
            @edit-question="handleEditQuestion"
            @regenerate-question="handleRegenerateQuestion"
            @save="saveForm"
            @send="handleSendForm"
          />
        </div>
      </div>
    </main>

    <!-- Assignment Modal -->
    <FormsAssignFormModal
      v-model="showAssignModal"
      :form="{
        id: Date.now(),
        title: formTitle,
        description: formDescription,
      }"
      :students="students"
      @assigned="handleFormAssigned"
    />
  </div>
</template>
