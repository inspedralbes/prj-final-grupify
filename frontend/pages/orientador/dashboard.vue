<script setup>
import { ref, onMounted, computed, reactive } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "~/stores/authStore";
import { useStudentsStore } from "~/stores/studentsStore";
import { useFormStore } from "~/stores/formStore";
import { useCoursesStore } from "~/stores/coursesStore";

const userData = ref(null);
const router = useRouter();
const authStore = useAuthStore();
const studentsStore = useStudentsStore();
const formStore = useFormStore();
const coursesStore = useCoursesStore();

// Estado del dashboard para el orientador con valores iniciales
const dashboardState = reactive({
  pendingForms: 0,
  completedForms: 0,
  totalStudents: 0,
  totalForms: 0,
  currentClasses: [],
  formCompletion: {}
});

// Calcular progreso global de formularios
const formCompletionAverage = computed(() => {
  const forms = Object.values(dashboardState.formCompletion);
  if (forms.length === 0) return 0;

  const sum = forms.reduce((acc, form) => acc + form.percentage, 0);
  return Math.round(sum / forms.length);
});

// Menú con solo los elementos a los que tiene acceso el orientador
const menuItems = [
  {
    title: "Alumnes",
    icon: "M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z",
    route: "/professor/llista-alumnes",
    description: "Gestiona els alumnes"
  },
  {
    title: "Sociograma",
    icon: "M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z",
    route: "/professor/sociograma/SociogramaView",
    description: "Analitza les relacions entre alumnes"
  },
  {
    title: "Cesc",
    icon: "M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z",
    route: "/professor/cesc/CescView",
    description: "Avaluació de conductes d'assetjament"
  },
  {
    title: "Gràfiques",
    icon: "M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z",
    route: "/orientador/graficas",
    description: "Visualitza dades i tendències"
  },
  {
    title: "Formularis",
    icon: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
    route: "/professor/formularis",
    description: "Crea i gestiona formularis"
  },
  {
    title: "Notificacions",
    icon: "M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5",
    route: "/professor/notificacions",
    description: "Gestiona les teves notificacions"
  },
];

// Función para cargar los estudiantes
const fetchStudents = async () => {
  try {
    await studentsStore.fetchStudents();
    dashboardState.totalStudents = studentsStore.students.length;
    console.log("Total de estudiantes cargados:", dashboardState.totalStudents);
  } catch (error) {
    console.error("Error al cargar estudiantes:", error);
    dashboardState.totalStudents = 0;
  }
};

// Función para cargar formularios
const fetchForms = async () => {
  try {
    await formStore.fetchForms();
    dashboardState.totalForms = formStore.forms.length;
    
    // Contar formularios completados
    const completedForms = formStore.forms.filter(form => form.status === 'completed');
    dashboardState.completedForms = completedForms.length;
    
    // Actualizar información de estado de formularios
    if (formStore.forms.length > 0) {
      const formStats = {};
      formStore.forms.forEach(form => {
        if (!formStats[form.title]) {
          formStats[form.title] = {
            completed: 0,
            total: 0,
            percentage: 0
          };
        }
        
        formStats[form.title].total++;
        if (form.status === 'completed') {
          formStats[form.title].completed++;
        }
      });
      
      // Calcular porcentajes
      Object.keys(formStats).forEach(title => {
        const stat = formStats[title];
        stat.percentage = Math.round((stat.completed / stat.total) * 100);
      });
      
      dashboardState.formCompletion = formStats;
    }
    
    console.log("Formularios cargados:", dashboardState.totalForms);
    console.log("Formularios completados:", dashboardState.completedForms);
  } catch (error) {
    console.error("Error al cargar formularios:", error);
    dashboardState.totalForms = 0;
    dashboardState.completedForms = 0;
  }
};

// Función para cargar clases asignadas
const fetchAssignedClasses = async () => {
  try {
    // Verificar si el orientador tiene clases asignadas
    if (authStore.user?.course_divisions && authStore.user.course_divisions.length > 0) {
      // Cargar cursos disponibles
      await coursesStore.fetchCourses();
      
      // Cargar las clases asignadas
      dashboardState.currentClasses = authStore.user.course_divisions.map(cd => {
        // Generar nombre de clase combinando curso y división
        const className = `${cd.course_name} ${cd.division_name}`;
        
        // Obtener estudiantes para esta clase específica
        const studentsInClass = studentsStore.students.filter(
          student => student.course_id === cd.course_id && student.division_id === cd.division_id
        );
        
        return {
          name: className,
          students: studentsInClass.length,
          nextClass: getNextClassTime(),
          courseId: cd.course_id,
          divisionId: cd.division_id,
          courseName: cd.course_name,
          divisionName: cd.division_name
        };
      });
    } else {
      // No hay clases asignadas
      dashboardState.currentClasses = [];
    }
    
    console.log("Clases asignadas cargadas:", dashboardState.currentClasses);
  } catch (error) {
    console.error("Error al cargar clases asignadas:", error);
    dashboardState.currentClasses = [];
  }
};

// Función para generar un horario para la próxima clase (simulado)
const getNextClassTime = () => {
  const days = ['Dilluns', 'Dimarts', 'Dimecres', 'Dijous', 'Divendres'];
  const hours = ['09:00', '10:15', '11:30', '12:45', '15:30', '16:45'];

  // 25% de probabilidad de que la clase sea "Avui"
  if (Math.random() < 0.25) {
    return `Avui ${hours[Math.floor(Math.random() * hours.length)]}`;
  }

  const randomDay = days[Math.floor(Math.random() * days.length)];
  const randomHour = hours[Math.floor(Math.random() * hours.length)];

  return `${randomDay} ${randomHour}`;
};

// Cargar el usuario desde el localStorage cuando el componente se monta
onMounted(async () => {
  // Verificar que sea un orientador
  if (!authStore.isOrientador) {
    router.push('/login');
    return;
  }
  
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    userData.value = JSON.parse(storedUser);
    console.log("User data loaded:", userData.value);
    
    // Cargar datos reales
    await fetchStudents();
    await fetchForms();
    await fetchAssignedClasses();
  }
});

// Función de logout
const logout = () => {
  authStore.logout();
};

// Función para navegar a una clase específica
const navigateToClass = (classItem) => {
  router.push({
    path: '/professor/llista-alumnes',
    query: {
      class_name: classItem.name
    }
  });
};

// Fecha actual
const getCurrentDate = () => {
  const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
  return new Date().toLocaleDateString('ca-ES', options);
};
</script>

<template>
  <div class="min-h-screen bg-[#f5f7fb]">
    <!-- Header con navegación -->
    <header class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo y título -->
          <div class="flex items-center">
            <div class="flex-shrink-0 flex items-center">
              <svg class="h-8 w-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <span class="ml-2 text-xl font-semibold text-gray-900">Grupify</span>
            </div>
          </div>

          <!-- Búsqueda -->
          <div class="hidden sm:flex flex-1 max-w-xl px-4">
            <div class="w-full">
              <label for="search" class="sr-only">Cercar</label>
              <div class="relative">
                <input id="search" type="text" placeholder="Cercar alumnes, grups o formularis..." class="block w-full bg-gray-100 border border-transparent rounded-md py-2 pl-10 pr-3 text-sm placeholder-gray-500 focus:outline-none focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Herramientas de usuario -->
          <div class="flex items-center space-x-4">
            <!-- Perfil de usuario -->
            <div class="flex items-center">
              <div v-if="userData" class="flex items-center">
                <img :src="userData.image || 'https://via.placeholder.com/32'" alt="Avatar" class="h-8 w-8 rounded-full">
                <span class="ml-2 text-sm font-medium text-gray-700 hidden sm:block">{{ userData.name }}</span>
              </div>
              <button @click="logout" class="ml-3 text-gray-500 hover:text-primary">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Bienvenida y datos básicos -->
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Bon dia, {{ userData?.name || 'Orientador' }}!</h1>
          <p class="text-gray-600">{{ getCurrentDate() }}</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-2">
          <span class="text-sm text-gray-600">Curs actual:</span>
          <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-sm font-medium">
            2024-2025
          </span>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Alumnos totales -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
          <div class="rounded-full bg-blue-100 p-3 mr-4">
            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Alumnes</p>
            <div v-if="studentsStore.loading" class="h-8 w-12 animate-pulse bg-gray-200 rounded"></div>
            <p v-else class="text-2xl font-bold">{{ dashboardState.totalStudents }}</p>
          </div>
        </div>

        <!-- Formularios disponibles -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
          <div class="rounded-full bg-green-100 p-3 mr-4">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Formularis Disponibles</p>
            <div v-if="formStore.loading" class="h-8 w-8 animate-pulse bg-gray-200 rounded"></div>
            <p v-else class="text-2xl font-bold">{{ dashboardState.totalForms }}</p>
          </div>
        </div>

        <!-- Formularios completados -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
          <div class="rounded-full bg-indigo-100 p-3 mr-4">
            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Formularis Completats</p>
            <div v-if="formStore.loading" class="h-8 w-12 animate-pulse bg-gray-200 rounded"></div>
            <div v-else class="flex items-baseline">
              <p class="text-2xl font-bold">{{ dashboardState.completedForms }}</p>
              <p v-if="dashboardState.totalForms > 0" class="ml-2 text-sm text-green-600">
                {{ Math.round((dashboardState.completedForms / dashboardState.totalForms) * 100) }}%
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Next Classes / Current Groups -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <h2 class="text-lg font-medium text-gray-900">Classes Assignades</h2>
              <NuxtLink to="/professor/llista-alumnes" class="text-sm text-primary hover:text-primary-dark">
                Veure tots els alumnes
              </NuxtLink>
            </div>
            
            <!-- Estado de carga -->
            <div v-if="coursesStore.loading" class="py-10 flex justify-center items-center">
              <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-primary"></div>
            </div>
            
            <!-- Estado vacío -->
            <div v-else-if="dashboardState.currentClasses.length === 0" class="py-10 text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No s'han trobat classes assignades</h3>
              <p class="mt-1 text-sm text-gray-500">Encara no tens cap classe assignada.</p>
            </div>
            
            <!-- Lista de clases -->
            <div v-else class="divide-y divide-gray-200">
              <div
                v-for="(classItem, index) in dashboardState.currentClasses"
                :key="index"
                class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 cursor-pointer"
                @click="navigateToClass(classItem)"
              >
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-medium">
                      {{ classItem.name.substring(0, 2) }}
                    </div>
                  </div>
                  <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">{{ classItem.name }}</h3>
                    <p class="text-xs text-gray-500">{{ classItem.students }} alumnes</p>
                  </div>
                </div>
                <div class="flex items-center">
                  <span class="text-sm text-gray-500">{{ classItem.nextClass }}</span>
                  <svg class="h-5 w-5 text-gray-400 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Completion Status -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
              <h2 class="text-lg font-medium text-gray-900">Estat dels Formularis</h2>
              <NuxtLink to="/professor/formularis/estat" class="text-sm text-primary hover:text-primary-dark">
                Veure complet
              </NuxtLink>
            </div>
            
            <!-- Estado de carga -->
            <div v-if="formStore.loading" class="py-10 flex justify-center items-center">
              <div class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-primary"></div>
            </div>
            
            <!-- Estado vacío -->
            <div v-else-if="Object.keys(dashboardState.formCompletion).length === 0" class="py-10 text-center">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No hi ha dades de formularis</h3>
              <p class="mt-1 text-sm text-gray-500">No s'han trobat formularis al sistema.</p>
            </div>
            
            <!-- Datos de formularios -->
            <div v-else class="p-6 space-y-4">
              <div v-for="(form, name) in dashboardState.formCompletion" :key="name" class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span>{{ name }}</span>
                  <span class="font-medium">{{ form.completed }}/{{ form.total }} ({{ form.percentage }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="h-2 rounded-full"
                       :class="{
                         'bg-green-500': form.percentage >= 80,
                         'bg-yellow-500': form.percentage >= 50 && form.percentage < 80,
                         'bg-red-500': form.percentage < 50
                       }"
                       :style="`width: ${form.percentage}%`"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Todas las herramientas -->
          <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
              <h2 class="text-lg font-medium text-gray-900">Eines de l'Orientador</h2>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 gap-2">
                <NuxtLink v-for="item in menuItems" :key="item.title" :to="item.route"
                  class="flex items-center p-2 rounded-lg hover:bg-gray-50 transition-colors">
                  <svg class="h-5 w-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                  </svg>
                  <div>
                    <span class="text-sm font-medium text-gray-800">{{ item.title }}</span>
                    <p class="text-xs text-gray-500">{{ item.description }}</p>
                  </div>
                </NuxtLink>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.text-primary {
  color: #00adec;
}
</style>
