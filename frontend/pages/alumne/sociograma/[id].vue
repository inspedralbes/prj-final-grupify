<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import {
  UserGroupIcon,
  UserIcon,
  StarIcon,
  HeartIcon,
  LightBulbIcon,
  CheckCircleIcon,
} from "@heroicons/vue/24/outline";
import { useStudentsStore } from "@/stores/studentsStore";

const router = useRouter();
const route = useRoute();
const studentsStore = useStudentsStore();
const errorMessage = ref(""); // Estado para el mensaje de error
const successMessage = ref(""); // Estado para el mensaje de éxito
const loggedStudentId = ref(null);
const course = ref(''); // Curso seleccionado
const division = ref(''); // División seleccionada

// Excluir al estudiante logueado de la lista
const students = ref([]);

onMounted(async () => {
  // Obtén el ID del estudiante logueado
  loggedStudentId.value = getLoggedStudentId();
  console.log("ID del estudiante logueado:", loggedStudentId.value);

  if (!loggedStudentId.value) {
    // Si no hay usuario logueado, mostrar error y redirigir o detener ejecución
    errorMessage.value =
      "Por favor, inicie sesión antes de completar el cuestionario.";
    return;
  }

  if (studentsStore.students.length === 0) {
    await studentsStore.fetchStudents();
  }

  // Obtener los datos del estudiante logueado
  const loggedStudent = studentsStore.getStudentById(loggedStudentId.value);
  if (loggedStudent) {
    course.value = loggedStudent.course;
    division.value = loggedStudent.division;
    console.log("Curso y división del estudiante logueado:", course.value, division.value);

    // Filtrar estudiantes que coincidan con el curso y la división del estudiante logueado
    students.value = studentsStore.students.filter(
      student =>
        student.course === course.value &&
        student.division === division.value &&
        student.id !== loggedStudentId.value
    );
    console.log("Estudiantes después del filtro:", students.value);
  } else {
    errorMessage.value = "No se encontraron los datos del estudiante logueado.";
    return;
  }
});

// Definimos las secciones del cuestionario
const sections = [
  {
    title: "Amb qui prefereixes treballar?",
    description:
      "Selecciona 3 companys/es amb qui prefereixes treballar a classe",
    id: 15,
    icon: UserGroupIcon,
    selectionKey: "preferredWorkPartners",
    maxSelections: 3,
  },
  {
    title: "Amb qui prefereixes no treballar?",
    description:
      "Selecciona 3 compañeros/as con los que prefieres evitar trabajar",
    id: 16,
    icon: UserIcon,
    selectionKey: "avoidWorkPartners",
    maxSelections: 3,
  },
  {
    title: "Amb qui has treballat anteriorment?",
    description:
      "Selecciona 3 compañeros/as con los que hayas trabajado anteriormente",
    id: 17,
    icon: UserIcon,
    selectionKey: "habitualWorkPartners",
    maxSelections: 3,
  },

  {
    title: "Qui té habilitats de lideratge",
    description: "Selecciona 2 compañeros/as que consideras buenos líderes",
    id: 18,
    icon: StarIcon,
    selectionKey: "potentialLeaders",
    maxSelections: 2,
  },
  {
    title: "Qui té habilitats de creativitat",
    description: "Selecciona 2 companys/es que consideres més creatius",
    id: 19,
    icon: LightBulbIcon,
    selectionKey: "creativePeople",
    maxSelections: 2,
  },
  {
    title: "Qui té habilitats d'organització",
    description: "Selecciona 2 companys/es que són més organitzats",
    id: 20,
    icon: HeartIcon,
    selectionKey: "organizedPeople",
    maxSelections: 2,
  },
  {
    title: "Amb qui no has treballat anteriorment?",
    description:
      "Selecciona 2 compañeros/as con los que no hayas trabajado anteriormente",
    id: 21,
    icon: UserIcon,
    selectionKey: "inhabitualWorkPartners",
    maxSelections: 2,
  },
];

// Respuestas y variables de estado
const selections = ref({
  preferredWorkPartners: [],
  avoidWorkPartners: [],
  habitualWorkPartners: [],
  potentialLeaders: [],
  creativePeople: [],
  organizedPeople: [],
  inhabitualWorkPartners: [],
});

const currentSection = ref(0);
const showResults = ref(false);

const currentSectionData = computed(() => sections[currentSection.value]);

const handleClassmateSelection = student => {
  const currentSelections =
    selections.value[currentSectionData.value.selectionKey];

  if (currentSelections.includes(student)) {
    selections.value[currentSectionData.value.selectionKey] =
      currentSelections.filter(s => s !== student);
  } else if (
    currentSelections.length < currentSectionData.value.maxSelections
  ) {
    selections.value[currentSectionData.value.selectionKey] = [
      ...currentSelections,
      student,
    ];
  }
};

const isClassmateSelectable = name => {
  if (currentSection.value === 1) {
    return !selections.value.preferredWorkPartners.includes(name);
  }
  return true;
};

const nextSection = () => {
  const currentSelections =
    selections.value[currentSectionData.value.selectionKey];
  if (currentSelections.length === currentSectionData.value.maxSelections) {
    if (currentSection.value < sections.length - 1) {
      currentSection.value++;
    } else {
      showResults.value = true;
    }
  }
};

const prevSection = () => {
  if (currentSection.value > 0) {
    currentSection.value--;
  }
};

const getLoggedStudentId = () => {
  const loggedUser = JSON.parse(localStorage.getItem("user")); // Obtener los datos completos del usuario
  return loggedUser ? loggedUser.id : null; // Retornar el ID del usuario logueado
};

const handleFinish = async () => {
  try {
    const studentId = getLoggedStudentId();

    if (!studentId) {
      throw new Error("Estudiante no identificado.");
    }

    // Obtener el ID del formulario de la URL
    const formId = route.params.id;

    const relationshipTypes = {
      preferredWorkPartners: "positive",
      avoidWorkPartners: "negative",
      habitualWorkPartners: "positive",
      potentialLeaders: "positive",
      creativePeople: "positive",
      organizedPeople: "positive",
      inhabitualWorkPartners: "positive",
    };

    // Preparar las respuestas para enviar al backend
    const answers = [];

    sections.forEach(section => {
      const currentSelections = selections.value[section.selectionKey];

      currentSelections.forEach(student => {
        answers.push({
          peer_id: student.id, // ID del compañero seleccionado
          question_id: section.id, // Índice basado en la sección
          relationship_type: relationshipTypes[section.selectionKey], // Tipo de relación
        });
      });
    });

    // Enviar los datos al backend
    const response = await fetch(
      "https://api.basebrutt.com/api/sociogram-relationships",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          user_id: studentId, // ID del usuario logueado
          form_id: formId,    // ID del formulario que se está contestando
          relationships: answers, // Relaciones enviadas
        }),
      }
    );

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.error || "Error al enviar las respuestas.");
    }

    // Mostrar mensaje de éxito y redirigir
    successMessage.value = "Respostes enviades correctament.";
    setTimeout(() => {
      successMessage.value = ""; // Desaparecer el mensaje después de 3 segundos
      navigateTo("/alumne/formularis");
    }, 3000);

    const completedForms = JSON.parse(
      localStorage.getItem("completedForms") || "[]"
    );
    completedForms.push("sociogram");
    localStorage.setItem("completedForms", JSON.stringify(completedForms));
  } catch (error) {
    console.error("Error en enviar les respostes:", error);
    errorMessage.value = "Hi va haver un error en enviar les respostes.";
  }
};
</script>

<template>
  <div class="max-w-4xl mx-auto mt-12 mb-12">
    <!-- Mensaje de Error -->
    <div
      v-if="errorMessage"
      class="bg-red-100 text-red-700 border-l-4 border-red-500 p-4 rounded-lg"
    >
      <p class="font-semibold">{{ errorMessage }}</p>
    </div>

    <!-- Mensaje de éxito después de enviar -->
    <div
      v-if="successMessage"
      class="bg-green-100 text-green-700 border-l-4 border-green-500 p-4 rounded-lg mb-4"
    >
      <p class="font-semibold">{{ successMessage }}</p>
    </div>

    <div v-if="!showResults" class="bg-white rounded-lg shadow-lg p-6">
      <h1 class="text-2xl font-bold mb-4 text-center">
        Qüestionari Sociomètric
      </h1>

      <div class="mb-6">
        <h2 class="text-xl font-semibold flex items-center gap-3">
          <component
            :is="currentSectionData.icon"
            class="w-6 h-6 text-primary"
          />
          {{ currentSectionData.title }}
        </h2>
        <p class="text-gray-600 mt-2">{{ currentSectionData.description }}</p>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <button
          v-for="student in students"
          :key="student.id"
          :disabled="!isClassmateSelectable(student)"
          :class="[
            'p-3 rounded-lg transition-all text-sm',
            selections[currentSectionData.selectionKey].includes(student)
              ? 'bg-blue-100 border-2 border-blue-500'
              : isClassmateSelectable(student)
                ? 'bg-gray-100 hover:bg-gray-200'
                : 'bg-red-50 text-red-600 cursor-not-allowed opacity-50',
          ]"
          @click="handleClassmateSelection(student)"
        >
          {{ student.name }} {{ student.last_name }}
          <CheckCircleIcon
            v-if="selections[currentSectionData.selectionKey].includes(student)"
            class="inline ml-2 w-4 h-4 text-blue-600"
          />
        </button>
      </div>

      <div class="flex justify-between mt-6">
        <button
          v-if="currentSection > 0"
          class="bg-gray-200 px-4 py-2 rounded-lg hover:bg-gray-300"
          @click="prevSection"
        >
          Anterior
        </button>
        <button
          class="bg-primary text-white px-4 py-2 rounded-lg ml-auto hover:bg-primary/90"
          :disabled="
            selections[currentSectionData.selectionKey].length !==
            currentSectionData.maxSelections
          "
          @click="nextSection"
        >
          {{
            currentSection === sections.length - 1
              ? "Veure resultats"
              : "Següent"
          }}
        </button>
      </div>
    </div>

    <div v-else class="space-y-6">
      <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-center">
          Resum de les respostes
        </h2>
        <div class="space-y-6">
          <div
            v-for="section in sections"
            :key="section.title"
            class="border-b pb-4"
          >
            <div class="flex items-center gap-2 mb-2">
              <component :is="section.icon" class="w-5 h-5 text-primary" />
              <h3 class="font-semibold">{{ section.title }}</h3>
            </div>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="student in selections[section.selectionKey]"
                :key="student.id"
                class="bg-gray-100 px-3 py-1 rounded-full text-sm"
              >
                {{ student.name }} {{ student.last_name }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="flex justify-center">
        <button
          class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary/90 font-semibold"
          @click="handleFinish"
        >
          Finalitzar
        </button>
      </div>
    </div>
  </div>
</template>
