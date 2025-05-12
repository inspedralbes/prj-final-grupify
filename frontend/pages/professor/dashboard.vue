<script setup>
import { ref, onMounted, computed, reactive } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "~/stores/authStore";
import { useCoursesStore } from "~/stores/coursesStore";
import { useGroupStore } from "~/stores/groupStore";
import { useStudentsStore } from "~/stores/studentsStore";

const userData = ref(null);
const router = useRouter();
const authStore = useAuthStore();
const coursesStore = useCoursesStore();
const groupStore = useGroupStore();

// Estado del dashboard
const dashboardState = reactive({
  pendingForms: 3,
  completedForms: 15,
  activeGroups: 0,
  totalStudents: 0,
  currentClasses: [],
  formCompletion: {
    'Avaluació Inicial': { completed: 78, total: 78, percentage: 100 },
    'Sociograma Trimestral': { completed: 67, total: 78, percentage: 86 },
    'CESC Bullying': { completed: 45, total: 78, percentage: 58 },
    'Qüestionari Final': { completed: 12, total: 78, percentage: 15 },
  }
});

// Definición completa de todos los elementos del menú
const allMenuItems = [
  {
    title: "Alumnes",
    icon: "M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z",
    route: "/professor/llista-alumnes",
    requiredPermission: null,
    description: "Gestiona els teus alumnes"
  },
  {
    title: "Grups",
    icon: "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z",
    route: "/professor/grups",
    requiredPermission: null,
    description: "Gestiona els grups i classes"
  },
  {
    title: "Formularis",
    icon: "M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z",
    route: "/professor/formularis",
    requiredPermission: null,
    description: "Crea i gestiona formularis"
  },
  {
    title: "Sociograma",
    icon: "M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z",
    route: "/professor/sociograma/SociogramaView",
    requiredPermission: "canViewAnalysis",
    description: "Analitza les relacions entre alumnes"
  },
  {
    title: "Cesc",
    icon: "M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z",
    route: "/professor/cesc/CescView",
    requiredPermission: "canViewAnalysis",
    description: "Avaluació de conductes d'assetjament"
  },
  {
    title: "Gràfiques",
    icon: "M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z",
    route: "/professor/graficas",
    requiredPermission: "canViewAnalysis",
    description: "Visualitza dades i tendències"
  },
  {
    title: "Xat IA",
    icon: "M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z",
    route: "/professor/assistent",
    requiredPermission: null,
    description: "Assistència intel·ligent per a professors"
  },
  {
    title: "Notificacions",
    icon: "M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5",
    route: "/professor/notificacions",
    requiredPermission: null,
    description: "Gestiona les teves notificacions"
  },
  {
    title: "Estat Formularis",
    icon: "M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4",
    route: "/professor/formularis/estat",
    requiredPermission: null,
    description: "Seguiment dels formularis completats"
  },
];

// Filtrar menú según permisos
const menuItems = computed(() => {
  return allMenuItems.filter(item => {
    if (item.requiredPermission === null) {
      return true; // Todos tienen acceso a este elemento
    }
    
    // Verificar si el usuario tiene el permiso requerido
    return authStore[item.requiredPermission];
  });
});

// Placeholder para mantener compatibilidad
const favoriteItems = computed(() => {
  return [];
});

// Calcular progreso global de formularios
const formCompletionAverage = computed(() => {
  const forms = Object.values(dashboardState.formCompletion);
  if (forms.length === 0) return 0;
  
  const sum = forms.reduce((acc, form) => acc + form.percentage, 0);
  return Math.round(sum / forms.length);
});

// Función para obtener datos actualizados del usuario desde el backend
const fetchUserData = async () => {
  try {
    // Solo obtenemos datos nuevos si el usuario está autenticado
    if (authStore.token) {
      await authStore.checkAuth();
      console.log("Datos de usuario actualizados desde el servidor");
    }
  } catch (error) {
    console.error("Error al obtener datos del usuario:", error);
  }
};

// Función para cargar los datos de clases desde el authStore
const loadClassesData = async () => {
  try {
    // Primero, obtenemos datos actualizados del usuario
    await fetchUserData();
    
    // Cargar cursos disponibles
    await coursesStore.fetchCourses();
    
    // Si el profesor tiene course_divisions en su perfil, usamos esos datos
    if (authStore.user?.course_divisions && authStore.user.course_divisions.length > 0) {
      // Array para almacenar todas las promesas de carga de estudiantes
      const studentsPromises = [];
      const studentsStore = useStudentsStore();
      
      // Crear un mapa para almacenar la cantidad de estudiantes por curso/división
      const studentCountMap = new Map();
      
      // Para cada course_division, cargar los estudiantes correspondientes
      for (const cd of authStore.user.course_divisions) {
        const promise = studentsStore.fetchStudents(true, cd.course_id, cd.division_id)
          .then(() => {
            // Filtrar estudiantes que pertenecen a este curso/división
            const studentsInClass = studentsStore.students.filter(
              student => student.course_id === cd.course_id && student.division_id === cd.division_id
            );
            
            // Guardar el conteo en el mapa
            const key = `${cd.course_id}-${cd.division_id}`;
            studentCountMap.set(key, studentsInClass.length);
            
            return studentsInClass.length;
          })
          .catch(error => {
            console.error(`Error al cargar estudiantes para ${cd.course_name} ${cd.division_name}:`, error);
            return 0;
          });
        
        studentsPromises.push(promise);
      }
      
      // Esperar a que se carguen todos los datos de estudiantes
      await Promise.all(studentsPromises);
      
      // Transformar course_divisions en el formato requerido para currentClasses
      dashboardState.currentClasses = authStore.user.course_divisions.map(cd => {
        // Generar nombre de clase combinando curso y división
        const className = `${cd.course_name} ${cd.division_name}`;
        
        // Obtener número de estudiantes del mapa o usar 0 como valor por defecto
        const key = `${cd.course_id}-${cd.division_id}`;
        const students = studentCountMap.get(key) || 0;
        
        // Loguear para depuración
        console.log("Añadiendo clase:", {
          className,
          courseId: cd.course_id,
          divisionId: cd.division_id,
          courseName: cd.course_name,
          divisionName: cd.division_name
        });
        
        return {
          name: className,
          students: students,
          nextClass: getNextClassTime(), // Función para generar un horario simulado
          courseId: cd.course_id,
          divisionId: cd.division_id,
          courseName: cd.course_name, // Añadir explícitamente para evitar división de strings
          divisionName: cd.division_name // Añadir explícitamente para evitar división de strings
        };
      });
    } else {
      console.log("No se encontraron course_divisions en el perfil del usuario");
    }
    
    // Cargar grupos asignados al profesor
    await fetchTeacherGroups();
    
  } catch (error) {
    console.error("Error al cargar datos de clases:", error);
  }
};

// Función para cargar los grupos creados por el profesor
const fetchTeacherGroups = async () => {
  try {
    // Obtener los grupos del profesor
    await groupStore.fetchGroups();
    
    // Actualizar el número de grupos activos
    dashboardState.activeGroups = groupStore.groups.length;
    
    // Calcular el número total de estudiantes en todos los grupos
    let totalStudents = groupStore.groups.reduce(
      (total, group) => total + (group.number_of_students || 0), 
      0
    );
    
    // Si hay datos de estudiantes en authStore, usar esos datos
    if (authStore.user?.student_count) {
      dashboardState.totalStudents = authStore.user.student_count;
    } else if (totalStudents > 0) {
      dashboardState.totalStudents = totalStudents;
    }
    
    // Si hay datos de formularios completados en authStore, usar esos datos
    if (authStore.user?.completed_forms_count) {
      dashboardState.completedForms = authStore.user.completed_forms_count;
    }
    
    // Si hay datos de formularios pendientes en authStore, usar esos datos
    if (authStore.user?.pending_forms_count) {
      dashboardState.pendingForms = authStore.user.pending_forms_count;
    }
    
    // Si no hay clases en currentClasses, usar los grupos como clases
    if (dashboardState.currentClasses.length === 0 && groupStore.groups.length > 0) {
      dashboardState.currentClasses = groupStore.groups.map(group => ({
        name: group.name,
        students: group.number_of_students || 0,
        nextClass: getNextClassTime(), // Función para generar un horario simulado
        groupId: group.id // Guardamos el ID del grupo para navegación
      }));
    }
  } catch (error) {
    console.error("Error al cargar grupos del profesor:", error);
  }
};

// Función para generar un horario simulado para la próxima clase
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

// Cargar el usuario y los datos desde el localStorage cuando el componente se monta
onMounted(async () => {
  // Verificar que sea un profesor o administrador
  if (!authStore.isProfesor && !authStore.isAdmin) {
    router.push('/login');
    return;
  }
  
  // Cargar datos del usuario
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    userData.value = JSON.parse(storedUser);
    console.log("Dades d'usuari carregades:", userData.value);
  }
  
  // Cargar datos reales de clases
  await loadClassesData();
});

// Función de logout
const logout = () => {
  authStore.logout();
};

// Función para seleccionar un curso y división específicos
const selectCourseAndDivision = async (courseName, divisionName) => {
  try {
    // Buscar el course_id y division_id correspondientes en las asignaciones del profesor
    const assignment = authStore.user?.course_divisions?.find(
      cd => cd.course_name === courseName && cd.division_name === divisionName
    );
    
    // Si encontramos la asignación, navegar a la lista de alumnos con los parámetros
    if (assignment) {
      console.log("Navegando a lista de alumnos con:", { 
        courseName, 
        divisionName, 
        courseId: assignment.course_id, 
        divisionId: assignment.division_id 
      });
      
      // Navegar a la lista de alumnos con los parámetros necesarios
      router.push({
        path: '/professor/llista-alumnes',
        query: { 
          course_name: courseName,
          division_name: divisionName,
          course_id: assignment.course_id,
          division_id: assignment.division_id
        }
      });
    } else {
      console.error("No se encontró la asignación para:", courseName, divisionName);
      console.log("Asignaciones disponibles:", authStore.user?.course_divisions);
    }
  } catch (error) {
    console.error("Error al seleccionar curso y división:", error);
  }
};

// Función para navegar a una clase específica
const navigateToClass = (classItem) => {
  console.log("Navegando a clase:", classItem);
  
  // Si tenemos datos específicos de curso y división, usamos la nueva función
  if (classItem.courseId && classItem.divisionId) {
    // Si tenemos los nombres almacenados directamente en el objeto, usarlos
    if (classItem.courseName && classItem.divisionName) {
      selectCourseAndDivision(classItem.courseName, classItem.divisionName);
      return;
    }
    
    // Plan B: Obtener directamente la información del curso y división de authStore
    const courseDiv = authStore.user?.course_divisions?.find(
      cd => cd.course_id === classItem.courseId && cd.division_id === classItem.divisionId
    );
    
    if (courseDiv) {
      // Si encontramos coincidencia directa, usar esos nombres
      selectCourseAndDivision(courseDiv.course_name, courseDiv.division_name);
      return;
    }
    
    // Plan C: Extraer el nombre del curso y división de la propiedad name
    const nameParts = classItem.name.split(' ');
    let courseName = '';
    let divisionName = '';
    
    if (nameParts.length >= 2) {
      // Si hay al menos dos partes, la última es la división
      divisionName = nameParts[nameParts.length - 1];
      // Y el resto es el nombre del curso
      courseName = nameParts.slice(0, nameParts.length - 1).join(' ');
    } else {
      // Si solo hay una parte, usamos esa como nombre del curso
      courseName = classItem.name;
    }
    
    // Usar la nueva función que preselecciona el curso y división y carga los estudiantes
    selectCourseAndDivision(courseName, divisionName);
    
  } else if (classItem.groupId) {
    // Si es un grupo, navegamos directamente a la vista de ese grupo
    router.push({
      path: '/professor/grups',
      query: { 
        group_id: classItem.groupId
      }
    });
  } else {
    // Si no tenemos datos específicos, solo navegamos a la página general de grupos
    router.push('/professor/grups');
  }
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
            <!-- Notificaciones -->
            <button class="p-1 rounded-full text-gray-500 hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary relative">
              <span class="sr-only">Ver notificacions</span>
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>
            
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
          <h1 class="text-2xl font-bold text-gray-900">Bon dia, {{ userData?.name || 'Professor' }}!</h1>
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
            <div v-if="coursesStore.loading || groupStore.loading" class="h-8 w-12 animate-pulse bg-gray-200 rounded"></div>
            <p v-else class="text-2xl font-bold">{{ dashboardState.totalStudents }}</p>
          </div>
        </div>
        
        <!-- Grupos activos -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
          <div class="rounded-full bg-green-100 p-3 mr-4">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Grups Actius</p>
            <div v-if="coursesStore.loading || groupStore.loading" class="h-8 w-8 animate-pulse bg-gray-200 rounded"></div>
            <p v-else class="text-2xl font-bold">{{ dashboardState.activeGroups }}</p>
          </div>
        </div>
        
        <!-- Formularios completados -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
          <div class="rounded-full bg-indigo-100 p-3 mr-4">
            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Formularis Completats</p>
            <div class="flex items-baseline">
              <p class="text-2xl font-bold">{{ dashboardState.completedForms }}</p>
              <p class="ml-2 text-sm text-green-600">+{{ formCompletionAverage }}%</p>
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
              <h2 class="text-lg font-medium text-gray-900">Classes Actuals</h2>
              <NuxtLink to="/professor/grups" class="text-sm text-primary hover:text-primary-dark">
                Veure tots els grups
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
              <h3 class="mt-2 text-sm font-medium text-gray-900">No s'han trobat classes</h3>
              <p class="mt-1 text-sm text-gray-500">Comença creant una classe o grup nou.</p>
              <div class="mt-6">
                <NuxtLink to="/professor/grups" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                  <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Crear nou grup
                </NuxtLink>
              </div>
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
            <div class="p-6 space-y-4">
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
              <h2 class="text-lg font-medium text-gray-900">Totes les Eines</h2>
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
/* Add any additional custom styles here */
</style>
