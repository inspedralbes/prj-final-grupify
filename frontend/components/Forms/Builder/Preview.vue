<script setup>
import { useTypingEffect } from "@/components/utils/chat";

const props = defineProps({
  questions: {
    type: Array,
    required: true,
  },
  title: String,
  description: String,
  context: String,
  teacher_id: Number,
  is_global: Boolean,
});

const emit = defineEmits([
  "edit-question",
  "regenerate-question",
  "save",
  "send",
]);
const localQuestions = ref([...props.questions]); // Copia local de las preguntas
const visibleQuestions = ref([]);
const loadingQuestionId = ref(null);
const showEditorModal = ref(false);
const selectedQuestion = ref(null);
const dateLimit = ref(null);
const timeLimit = ref(null);
const { content: titleContent, typeMessage: typeTitle } = useTypingEffect(50);
const { content: descriptionContent, typeMessage: typeDescription } =
  useTypingEffect(30);

// Sincronizar cambios en props con la copia local
watch(
  () => props.questions,
  newQuestions => {
    localQuestions.value = [...newQuestions];
  },
  { immediate: true }
);

watch(
  () => props.title,
  async newTitle => {
    if (newTitle) {
      await typeTitle(newTitle);
    }
  },
  { immediate: true }
);

watch(
  () => props.description,
  async newDescription => {
    if (newDescription) {
      await typeDescription(newDescription);
    }
  },
  { immediate: true }
);

watch(
  () => localQuestions.value,
  async newQuestions => {
    visibleQuestions.value = [];
    for (let i = 0; i < newQuestions.length; i++) {
      await new Promise(resolve => setTimeout(resolve, 500));
      visibleQuestions.value.push(newQuestions[i]);
    }
  },
  { immediate: true }
);

const handleEditQuestion = question => {
  selectedQuestion.value = question;
  showEditorModal.value = true;
};

const handleSaveQuestion = editedQuestion => {
  const index = localQuestions.value.findIndex(q => q.id === editedQuestion.id);
  if (index !== -1) {
    localQuestions.value.splice(index, 1, editedQuestion);
  }
  emit("edit-question", { question: editedQuestion });
};

const handleRegenerateQuestion = async question => {
  loadingQuestionId.value = question.id;
  await emit("regenerate-question", question);
  loadingQuestionId.value = null;
};

const handleSave = async () => {
  // Validar datos mínimos requeridos
  if (!props.title || !props.title.trim()) {
    showToastMessage("El formulari ha de tenir un títol", "error-toast");
    return;
  }

  if (!props.description || !props.description.trim()) {
    showToastMessage("El formulari ha de tenir una descripció", "error-toast");
    return;
  }

  if (!localQuestions.value || localQuestions.value.length === 0) {
    showToastMessage("El formulari ha de tenir almenys una pregunta", "error-toast");
    return;
  }

  if (!dateLimit.value) {
    showToastMessage("Cal definir una data límit per al formulari", "error-toast");
    return;
  }

  // En lugar de enviar directamente la solicitud al backend,
  // solo emitimos el evento para que el componente padre lo maneje
  emit("save", {
    dateLimit: dateLimit.value,
    timeLimit: timeLimit.value
  });
};

const handleDownload = () => {
  const formData = {
    title: props.title,
    description: props.description,
    questions: localQuestions.value,
  };

  const json = JSON.stringify(formData, null, 2); // Convertir a JSON con formato legible
  const blob = new Blob([json], { type: "application/json" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = "formulario.json"; // Nombre del archivo a descargar
  link.click();
};

// Mostrar Toast
const showToast = ref(false);
const toastMessage = ref("");
const toastType = ref("success-toast");

function showToastMessage(message, type = "success-toast") {
  toastMessage.value = message;
  toastType.value = type;
  showToast.value = true;

  // Usar un timeout para cerrar el toast después de 3 segundos
  setTimeout(() => {
    showToast.value = false;
  }, 1000);
}
</script>

<template>
  <div>
    <!-- Toast mensaje -->
    <div v-if="showToast" :class="toastType" class="toast">
      {{ toastMessage }}
    </div>

    <div class="space-y-6">
      <div v-if="title" class="card animate-fade-in">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold mb-2">{{ titleContent }}</h2>
        </div>
        <p class="text-gray-600">{{ descriptionContent }}</p>
      </div>

      <div v-for="(question, index) in visibleQuestions" :key="index"
        class="border rounded-lg p-6 bg-white animate-slide-up">
        <div class="flex justify-between items-start mb-4">
          <h3 class="text-lg font-medium">{{ question.title }}</h3>
          <FormsBuilderQuestionActions :question="question" :is-loading="loadingQuestionId === question.id"
            @edit="handleEditQuestion" @regenerate="handleRegenerateQuestion" />
        </div>

        <div class="space-y-4">
          <template v-if="['multiple', 'checkbox'].includes(question.type)">
            <div v-for="option in question.options" :key="option.value" class="flex items-center space-x-2">
              <input :type="question.type === 'multiple' ? 'radio' : 'checkbox'" :name="`question-${index}`"
                :value="option.value" class="w-4 h-4 text-primary focus:ring-primary" />
              <label class="text-gray-700">{{ option.text }}</label>
            </div>
          </template>

          <template v-else-if="question.type === 'text'">
            <textarea rows="3"
              class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent"
              placeholder="Tu respuesta..."></textarea>
          </template>

          <template v-else-if="question.type === 'number'">
            <input type="number"
              class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent"
              placeholder="0" />
          </template>
        </div>
      </div>

      <FormsBuilderQuestionEditorModal v-model="showEditorModal" :question="selectedQuestion"
        @save="handleSaveQuestion" />
    </div>
  </div>
</template>

<style scoped>
/* Estilos para el Toast */
.toast {
  position: fixed;
  top: 20px;
  /* Coloca el toast en la parte superior */
  left: 50%;
  transform: translateX(-50%);
  background-color: #28a745;
  /* Verde para éxito */
  color: white;
  padding: 15px;
  border-radius: 8px;
  font-size: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  opacity: 0;
  animation: toast-animation 0.5s forwards;
  z-index: 9999;
  /* Asegura que se muestre encima de todo */
}

@keyframes toast-animation {
  0% {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }

  100% {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

/* Toast para errores */
.error-toast {
  background-color: #dc3545;
  /* Rojo para error */
}

/* Toast para mensajes generales */
.success-toast {
  background-color: #28a745;
  /* Verde para éxito */
}
</style>
