<script setup>
import { useStudentsStore } from "@/stores/studentsStore";
import { useGroupStore } from "@/stores/groupStore";
import { useCoursesStore } from "@/stores/coursesStore";
import { useAuthStore } from "@/stores/authStore";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

const authStore = useAuthStore();
const studentsStore = useStudentsStore();
const groupStore = useGroupStore();
const coursesStore = useCoursesStore();

const selectedStudents = ref([]);
const isLoading = ref(false);
const groupName = ref("");
const groupDescription = ref("");
const errorMessage = ref("");
const successMessage = ref("");

// Variables para el filtrado por curso y división
const selectedCourseId = ref(null);
const selectedDivisionId = ref(null);
const hasMultipleCourses = ref(false);

onMounted(async () => {
  // Asegurarnos de inicializar el authStore si es necesario
  if (authStore.isAuthenticated && !authStore.user) {
    await authStore.checkAuth();
  }
  
  // Primero cargamos los cursos disponibles
  await coursesStore.fetchCourses();
  
  // Verificamos si el profesor tiene curso y división asignados
  if (authStore.user?.course_divisions && authStore.user.course_divisions.length > 0) {
    hasMultipleCourses.value = authStore.user.course_divisions.length > 1;
    
    // Por defecto seleccionamos el primer curso/división
    const firstAssignment = authStore.user.course_divisions[0];
    selectedCourseId.value = firstAssignment.course_id;
    selectedDivisionId.value = firstAssignment.division_id;
  } 
  // Compatibilidad con formato anterior (solo un curso/división)
  else if (authStore.user?.course_id && authStore.user?.division_id) {
    selectedCourseId.value = authStore.user.course_id;
    selectedDivisionId.value = authStore.user.division_id;
  } else {
    // Si no encontramos datos de curso/división en el formato esperado, 
    // intentemos obtenerlos desde localStorage directamente
    try {
      const userDataFromStorage = localStorage.getItem('user');
      if (userDataFromStorage) {
        const parsedUser = JSON.parse(userDataFromStorage);
        
        if (parsedUser.course_divisions && parsedUser.course_divisions.length > 0) {
          const firstAssignment = parsedUser.course_divisions[0];
          selectedCourseId.value = firstAssignment.course_id;
          selectedDivisionId.value = firstAssignment.division_id;
        } else if (parsedUser.course_id && parsedUser.division_id) {
          selectedCourseId.value = parsedUser.course_id;
          selectedDivisionId.value = parsedUser.division_id;
        }
      }
    } catch (error) {
      errorMessage.value = "Error al obtener los datos del usuario";
    }
  }
  
  // Cargamos los estudiantes según el curso y división seleccionados
  if (selectedCourseId.value && selectedDivisionId.value) {
    await loadStudents();
  } else {
    errorMessage.value = "No se ha podido determinar su curso y división";
  }
});

// Función para cargar estudiantes según el curso y división seleccionados
const loadStudents = async () => {
  if (selectedCourseId.value && selectedDivisionId.value) {
    isLoading.value = true;
    // Forzamos la recarga (true) y pasamos explícitamente los IDs
    await studentsStore.fetchStudents(true, selectedCourseId.value, selectedDivisionId.value);
    isLoading.value = false;
  } else {
    errorMessage.value = "Debe seleccionar un curso y división";
  }
};

// Cambiar el curso/división seleccionado y recargar los estudiantes
const updateCourseSelection = async (courseId, divisionId) => {
  selectedCourseId.value = courseId;
  selectedDivisionId.value = divisionId;
  selectedStudents.value = []; // Limpiamos la selección al cambiar de curso
  errorMessage.value = ""; // Limpiamos cualquier mensaje de error previo
  successMessage.value = ""; // Limpiamos mensajes de éxito
  await loadStudents();
};

const students = computed(() => studentsStore.students);

// Obtener los cursos y divisiones disponibles para el profesor
const teacherCourses = computed(() => {
  if (authStore.user?.course_divisions) {
    return authStore.user.course_divisions;
  }
  
  // Si solo tiene un curso asignado directamente
  if (authStore.user?.course_id && authStore.user?.division_id) {
    return [{
      course_id: authStore.user.course_id,
      course_name: authStore.user.course_name,
      division_id: authStore.user.division_id,
      division_name: authStore.user.division_name
    }];
  }
  
  return [];
});

// Obtener el nombre del curso y división actual
const currentCourseDisplay = computed(() => {
  const course = teacherCourses.value.find(c => 
    c.course_id === selectedCourseId.value && 
    c.division_id === selectedDivisionId.value
  );
  
  if (course) {
    return `${course.course_name} ${course.division_name}`;
  }
  
  return "Seleccione un curso";
});

const toggleSelection = studentId => {
  const index = selectedStudents.value.indexOf(studentId);
  if (index > -1) {
    selectedStudents.value.splice(index, 1);
  } else {
    selectedStudents.value.push(studentId);
  }
};

const handleCreateGroup = async () => {
  if (!authStore.isAuthenticated) {
    errorMessage.value = "Debes iniciar sesión";
    return;
  }

  if (!selectedCourseId.value || !selectedDivisionId.value) {
    errorMessage.value = "Cal seleccionar un curs i una divisió";
    return;
  }

  if (selectedStudents.value.length === 0) {
    errorMessage.value = "Cal seleccionar almenys un estudiant";
    return;
  }

  if (isLoading.value) return;
  isLoading.value = true;

  try {
    // El Backend ahora asignará automáticamente el creator_id basado en el token JWT
    const groupData = {
      name: groupName.value,
      description: groupDescription.value,
      number_of_students: selectedStudents.value.length,
      course_id: selectedCourseId.value,
      division_id: selectedDivisionId.value
    };

    console.log("Enviant dades del grup:", groupData);

    const response = await groupStore.createGroup(groupData);

    if (response.id) {
      await groupStore.addStudentsToGroup(response.id, selectedStudents.value);
      successMessage.value = "Grup creat amb èxit!";
      setTimeout(() => navigateTo("/professor/grups"), 2000);
    }
  } catch (error) {
    console.error("Error creating group:", error);
    errorMessage.value = "Hi ha hagut un error al crear el grup";
  } finally {
    isLoading.value = false;
  }
};

const goBack = () => {
  navigateTo("/professor/grups");
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />

    <div class="max-w-6xl mx-auto px-4 py-8">
      <!-- Header Section with Back Button -->
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Crear Nou Grup</h1>
        <button @click="goBack"
          class="flex items-center gap-2 px-4 py-2 rounded-lg text-[rgb(0,173,238)] hover:bg-[rgba(0,173,238,0.1)] transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Tornar
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Nom del Grup
              </label>
              <input v-model="groupName" type="text" placeholder="Introdueix el nom del grup"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-[rgb(0,173,238)] focus:border-[rgb(0,173,238)]" />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Descripció del Grup
              </label>
              <textarea v-model="groupDescription" placeholder="Descripció (opcional)" rows="4"
                class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:ring-2 focus:ring-[rgb(0,173,238)] focus:border-[rgb(0,173,238)]"></textarea>
            </div>

            <!-- Create Button -->
            <button @click="handleCreateGroup" :disabled="!groupName || isLoading"
              class="w-full px-6 py-3 rounded-lg bg-[rgb(0,173,238)] text-white hover:bg-[rgb(0,153,218)] disabled:opacity-50 transition-colors font-medium">
              {{ isLoading ? "Creant..." : "Crear Grup" }}
            </button>

            <!-- Messages -->
            <div>
              <p v-if="successMessage" class="px-4 py-2 bg-green-50 text-green-700 rounded-lg">
                {{ successMessage }}
              </p>
              <p v-if="errorMessage" class="px-4 py-2 bg-red-50 text-red-700 rounded-lg">
                {{ errorMessage }}
              </p>
            </div>
          </div>
        </div>

        <!-- Students Selection Section -->
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-800">Estudiants</h2>
            <div class="flex gap-2 items-center">
              <!-- Course selector dropdown - only shown if teacher has multiple courses -->
              <div v-if="teacherCourses.length > 1" class="relative">
                <div class="relative inline-block text-left">
                  <div>
                    <button type="button" 
                      class="inline-flex justify-center items-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[rgb(0,173,238)]" 
                      id="course-menu-button" 
                      aria-expanded="true" 
                      aria-haspopup="true"
                      @click="$refs.courseDropdown.classList.toggle('hidden')">
                      {{ currentCourseDisplay }}
                      <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </div>
                  
                  <div ref="courseDropdown" 
                    class="hidden origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10" 
                    role="menu" 
                    aria-orientation="vertical" 
                    aria-labelledby="course-menu-button" 
                    tabindex="-1">
                    <div class="py-1" role="none">
                      <button 
                        v-for="course in teacherCourses" 
                        :key="`${course.course_id}-${course.division_id}`"
                        class="text-gray-700 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 hover:text-gray-900" 
                        role="menuitem" 
                        tabindex="-1" 
                        @click="updateCourseSelection(course.course_id, course.division_id); $refs.courseDropdown.classList.add('hidden')">
                        {{ course.course_name }} {{ course.division_name }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <span class="px-4 py-1.5 bg-[rgba(0,173,238,0.1)] text-[rgb(0,173,238)] rounded-full text-sm font-medium">
                {{ selectedStudents.length }} seleccionats
              </span>
            </div>
          </div>
          
          <div v-if="isLoading" class="flex justify-center py-6">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[rgb(0,173,238)]"></div>
          </div>

          <div v-else-if="students.length === 0" class="text-center py-6">
            <p class="text-gray-500">No s'han trobat estudiants per a aquest curs i divisió.</p>
          </div>

          <div v-else class="space-y-3 max-h-[600px] overflow-y-auto pr-2 -mr-2">
            <div v-for="student in students" :key="student.id" class="relative">
              <button @click="toggleSelection(student.id)" :class="[
                'w-full group flex items-center justify-between px-4 py-3 rounded-lg transition-all duration-200',
                selectedStudents.includes(student.id)
                  ? 'bg-[rgba(0,173,238,0.1)] border-[rgb(0,173,238)] shadow-sm'
                  : 'bg-gray-50 hover:bg-[rgba(0,173,238,0.05)]',
              ]">
                <div class="flex items-center gap-3">
                  <div :class="[
                    'w-8 h-8 rounded-full flex items-center justify-center border-2 transition-colors',
                    selectedStudents.includes(student.id)
                      ? 'border-[rgb(0,173,238)] bg-[rgb(0,173,238)]'
                      : 'border-gray-300 group-hover:border-[rgb(0,173,238)]',
                  ]">
                    <svg v-if="selectedStudents.includes(student.id)" xmlns="http://www.w3.org/2000/svg"
                      class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-medium" :class="[
                      selectedStudents.includes(student.id)
                        ? 'text-[rgb(0,173,238)]'
                        : 'text-gray-700',
                    ]">
                      {{ student.name }} {{ student.last_name }}
                    </h3>
                  </div>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
