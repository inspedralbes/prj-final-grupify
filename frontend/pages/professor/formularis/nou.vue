<script setup>
import { regenerateQuestion } from "@/components/utils/aiQuestions";
const authStore = useAuthStore();
const user = authStore.user;
const teacherId = ref(user.id);
const questions = ref([]);
const formTitle = ref("");
const formDescription = ref("");
const formContext = ref("");
const showAssignModal = ref(false);
const dateLimit = ref(null);
const timeLimit = ref(null);
const isLoading = ref(false);
const toast = useToast();
const router = useRouter();

// Obtener lista de estudiantes para asignación
const { data: students } = await useFetch('http://localhost:8000/api/students');

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
      q.id === question.id ? { ...newQuestion, id: question.id } : q
    );
  } catch (error) {
    console.error("Error al regenerar la pregunta:", error);
    toast.error("Error al regenerar la pregunta. Intenta de nou.");
  }
};

// Función para validar formulario sin añadir timestamp
const validateForm = () => {
  if (!formTitle.value?.trim()) {
    toast.error("El formulari ha de tenir un títol");
    return false;
  }
  
  if (!formDescription.value?.trim()) {
    toast.error("El formulari ha de tenir una descripció");
    return false;
  }
  
  if (!questions.value.length) {
    toast.error("El formulari ha de tenir almenys una pregunta");
    return false;
  }
  
  return true;
};

const saveForm = async () => {
  if (!validateForm()) return;
  
  isLoading.value = true;
  
  try {
    // Preparar las preguntas con el formato correcto para el backend
    const formattedQuestions = questions.value.map(question => {
      const formattedQuestion = {
        title: question.title,
        type: question.type,
        placeholder: question.placeholder || null,
        context: question.context || null
      };

      // Afegir opcions només si és pregunta de tipus multiple o checkbox
      if (['multiple', 'checkbox'].includes(question.type) && question.options) {
        formattedQuestion.options = question.options.map((option, index) => ({
          text: option.text,
          value: option.value !== undefined ? option.value : index
        }));
      }

      return formattedQuestion;
    });
    
    // Limitar la longitud de la descripción para evitar error de base de datos
    // Asumimos que el límite de la columna es 255 caracteres (típico para VARCHAR)
    const limitedDescription = formDescription.value.substring(0, 250);
    
    // Prepara los datos del formulario según lo esperado por el backend
    const formData = {
      title: formTitle.value,
      description: limitedDescription,
      questions: formattedQuestions,
      teacher_id: teacherId.value,
      is_global: false,
      date_limit: dateLimit.value || new Date().toISOString().split('T')[0], // Usa la fecha actual si no hay fecha límite
      time_limit: timeLimit.value
    };
    
    console.log("Enviant formulari:", formData);
    
    // Envia les dades a l'endpoint de guardat de formularis
    const response = await fetch("http://localhost:8000/api/forms-save", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify(formData),
      credentials: "same-origin"
    });
    
    if (!response.ok) {
      const errorData = await response.json();
      console.error('Errors de validació:', errorData);
      toast.error("Errors en les dades enviades: " + 
        (errorData.message || JSON.stringify(errorData.errors || {}, null, 2)));
      isLoading.value = false;
      return;
    }
    
    const result = await response.json();
    console.log('Resposta del servidor:', result);
    toast.success("Formulari desat amb èxit");
    
    // Redirect after successful save
    setTimeout(() => {
      navigateTo("/professor/formularis");
    }, 1500);
  } catch (error) {
    console.error('Error en desar el formulari:', error);
    toast.error("Hi va haver un error en desar el formulari. Si us plau, torna-ho a intentar.");
  } finally {
    isLoading.value = false;
  }
};

const handleSendForm = () => {
  if (!validateForm()) return;
  showAssignModal.value = true;
};

const handleFormAssigned = async assignments => {
  isLoading.value = true;
  
  try {
    // First save the form
    // Preparar las preguntas con el formato correcto para el backend
    const formattedQuestions = questions.value.map(question => {
      const formattedQuestion = {
        title: question.title,
        type: question.type,
        placeholder: question.placeholder || null,
        context: question.context || null
      };

      // Afegir opcions només si és pregunta de tipus multiple o checkbox
      if (['multiple', 'checkbox'].includes(question.type) && question.options) {
        formattedQuestion.options = question.options.map((option, index) => ({
          text: option.text,
          value: option.value !== undefined ? option.value : index
        }));
      }

      return formattedQuestion;
    });
    
    // Limitar la longitud de la descripción para evitar error de base de datos
    const limitedDescription = formDescription.value.substring(0, 250);
    
    // Prepara los datos del formulario según lo esperado por el backend
    const formData = {
      title: formTitle.value,
      description: limitedDescription,
      questions: formattedQuestions,
      teacher_id: teacherId.value,
      is_global: false,
      date_limit: dateLimit.value || new Date().toISOString().split('T')[0], // Usa la fecha actual si no hay fecha límite
      time_limit: timeLimit.value
    };
    
    console.log("Guardando formulario para asignación:", formData);
    
    const saveResponse = await fetch("http://localhost:8000/api/forms-save", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify(formData),
      credentials: "same-origin"
    });
    
    if (!saveResponse.ok) {
      const errorData = await saveResponse.json();
      toast.error("Error al desar el formulari: " + 
        (errorData.message || JSON.stringify(errorData.errors || {}, null, 2)));
      isLoading.value = false;
      return;
    }
    
    const savedForm = await saveResponse.json();
    console.log("Formulari guardat:", savedForm);
    
    // Then create assignments
    const assignmentData = {
      form_id: savedForm.id || savedForm.form.id, // Maneja tots dos formats possibles de resposta
      assignments: assignments
    };
    
    console.log("Assignant formulari:", assignmentData);
    
    const assignResponse = await fetch("http://localhost:8000/api/forms/assign-to-course-division", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify(assignmentData),
      credentials: "same-origin"
    });
    
    if (!assignResponse.ok) {
      const errorData = await assignResponse.json();
      toast.error("Error al assignar el formulari: " + 
        (errorData.message || JSON.stringify(errorData.errors || {}, null, 2)));
      isLoading.value = false;
      return;
    }
    
    const assignResult = await assignResponse.json();
    console.log("Resultat d'assignació:", assignResult);
    
    toast.success("Formulari enviat correctament als alumnes seleccionats");
    
    setTimeout(() => {
      navigateTo("/professor/formularis");
    }, 1500);
  } catch (error) {
    console.error("Error al assignar el formulari:", error);
    toast.error("Hi va haver un error en assignar el formulari. Si us plau, torna-ho a intentar.");
  } finally {
    isLoading.value = false;
  }
};

// Toast composable
function useToast() {
  const showToast = ref(false);
  const toastMessage = ref("");
  const toastType = ref("success");
  
  const show = (message, type = "success", duration = 3000) => {
    toastMessage.value = message;
    toastType.value = type;
    showToast.value = true;
    
    setTimeout(() => {
      showToast.value = false;
    }, duration);
  };
  
  return {
    showToast,
    toastMessage,
    toastType,
    show,
    success: (message) => show(message, "success"),
    error: (message) => show(message, "error"),
    info: (message) => show(message, "info"),
    warning: (message) => show(message, "warning"),
  };
}
</script>

<template>
  <div class="min-h-screen bg-background">
    <!-- Toast Component comentado para que no aparezca
    <div 
      v-if="toast.showToast" 
      :class="`toast ${toast.toastType}-toast`"
      class="absolute bottom-4 left-4 z-50 px-4 py-3 rounded-lg shadow-md"
    >
      {{ toast.toastMessage }}
    </div>
    -->

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
          <Forms-Builder-AIFormGenerator
            @generate-questions="handleGeneratedQuestions"
          />

          <!-- Templates -->
          <Forms-Builder-TemplateList @select="handleTemplateSelect" />
        </div>

        <!-- Form Preview -->
        <div class="sticky top-8">
          <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <h3 class="text-lg font-medium mb-3">Configuració del formulari</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Data límit (opcional)
                </label>
                <input 
                  type="date" 
                  v-model="dateLimit"
                  class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                  Temps límit (opcional)
                </label>
                <input 
                  type="time" 
                  v-model="timeLimit"
                  class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-primary focus:border-transparent"
                />
              </div>
            </div>
          </div>

          <Forms-Builder-Preview
            :questions="questions"
            :title="formTitle"
            :description="formDescription"
            :context="formContext"
            :teacher_id="teacherId"
            @edit-question="handleEditQuestion"
            @regenerate-question="handleRegenerateQuestion"
            @save="saveForm"
            @send="handleSendForm"
          />

          <div class="mt-6 flex gap-4 justify-end">
            <button 
              class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-md transition duration-300"
              @click="goBack"
              :disabled="isLoading"
            >
              Cancel·lar
            </button>
            
            <button 
              class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-md transition duration-300 flex items-center gap-2"
              @click="saveForm"
              :disabled="isLoading"
            >
              <svg v-if="isLoading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Guardar Formulari
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Assignment Modal -->
    <Forms-Assign-FormModal
      v-if="showAssignModal"
      v-model="showAssignModal"
      :form="{
        id: Date.now(),
        title: formTitle,
        description: formDescription,
      }"
      :students="students || []"
      :loading="isLoading"
      @assigned="handleFormAssigned"
    />
  </div>
</template>

<style scoped>
.toast {
  max-width: 400px;
  animation: slide-in 0.3s ease-out forwards;
}

@keyframes slide-in {
  0% {
    transform: translateY(100%);
    opacity: 0;
  }
  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

.success-toast {
  background-color: #10B981;
  color: white;
}

.error-toast {
  background-color: #EF4444;
  color: white;
}

.info-toast {
  background-color: #3B82F6;
  color: white;
}

.warning-toast {
  background-color: #F59E0B;
  color: white;
}
</style>