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
  pendingForms: 0,
  completedForms: 0,
  activeGroups: 0,
  totalStudents: 0,  // Inicializar a 0, se actualizará con los datos reales
  currentClasses: [],
  formCompletion: {},
  loadingForms: true // Flag para mostrar estado de carga de formularios
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
    route: "http://localhost:8501",
    requiredPermission: null,
    description: "Assistència intel·ligent per a professors"
  },
  {
    title: "Notificacions",
    icon: "M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0M3.124 7.5A8.969 8.969 0 0 1 5.292 3m13.416 0a8.969 8.969 0 0 1 2.168 4.5",
    route: "/professor/notificacions",
    requiredPermission: null,
    description: "Gestiona les teves notificacions"
  },];

// Filtrar menú según permisos
const menuItems = computed(() => {
  return allMenuItems.filter(item => {
    if (item.requiredPermission === null) {
      return true; // Todos tienen acceso a este elemento
    }

    // Verificar si l'usuari té el permís requerit
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

// Calcular el porcentaje real de respuestas totales (respuestas / total posible)
const totalResponsesPercentage = computed(() => {
  const forms = Object.values(dashboardState.formCompletion);
  if (forms.length === 0) return 0;
  
  const totalStudents = forms.reduce((acc, form) => acc + form.total, 0);
  const completedResponses = forms.reduce((acc, form) => acc + form.completed, 0);
  
  if (totalStudents === 0) return 0;
  return Math.round((completedResponses / totalStudents) * 100);
});

// Funció per obtenir dades actualitzades de l'usuari des del backend
const fetchUserData = async () => {
  try {
    // Només obtenim dades noves si l'usuari està autenticat
    if (authStore.token) {
      await authStore.checkAuth();
      console.log("Dades d'usuari actualitzades des del servidor");
    }
  } catch (error) {
    console.error("Error al obtenir dades de l'usuari:", error);
  }
};

// Funció per carregar les dades de classes des de l'authStore
const loadClassesData = async () => {
  try {
    // Primer, obtenim dades actualitzades de l'usuari
    await fetchUserData();

    // Cargar cursos disponibles
    const userId = authStore.user?.id;
    await coursesStore.fetchCourses(true, userId);

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

      // Calcular el total de estudiantes sumando los conteos de cada mapa
      let totalStudents = 0;
      studentCountMap.forEach(count => {
        totalStudents += count;
      });

      // Actualizar el contador total de estudiantes
      dashboardState.totalStudents = totalStudents;

      console.log("Total de estudiantes calculado:", totalStudents);

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
          divisionName: cd.division_name,
          students: students
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
      console.log("No es trobaren course_divisions en el perfil de l'usuari");
    }

    // Carregar grups assignats al professor
    await fetchTeacherGroups();

  } catch (error) {
    console.error("Error al carregar dades de classes:", error);
  }
};

// Funció per carregar els grups creats pel professor
const fetchTeacherGroups = async () => {
  try {
    // Obtenir els grups del professor
    await groupStore.fetchGroups();

    // Actualitzar el nombre de grups actius
    dashboardState.activeGroups = groupStore.groups.length;

    // Calcular el nombre total d'estudiants en tots els grups
    let groupStudents = groupStore.groups.reduce(
      (total, group) => total + (group.number_of_students || 0),
      0
    );

    // Si hay datos de estudiantes en authStore, usar esos datos
    if (authStore.user?.student_count) {
      dashboardState.totalStudents = authStore.user.student_count;
    } else if (groupStudents > 0 && dashboardState.totalStudents === 0) {
      // Solo actualizar si no tenemos datos de estudiantes aún
      dashboardState.totalStudents = groupStudents;
    }

    console.log("Total de estudiantes después de fetchTeacherGroups:", dashboardState.totalStudents);

    // Si hay datos de formularios completados en authStore, usar esos datos
    if (authStore.user?.completed_forms_count) {
      dashboardState.completedForms = authStore.user.completed_forms_count;
    }

    // Si hay datos de formularios pendientes en authStore, usar esos datos
    if (authStore.user?.pending_forms_count) {
      dashboardState.pendingForms = authStore.user.pending_forms_count;
    }

    // Si no hi ha classes a currentClasses, usar els grups com a classes
    if (dashboardState.currentClasses.length === 0 && groupStore.groups.length > 0) {
      dashboardState.currentClasses = groupStore.groups.map(group => ({
        name: group.name,
        students: group.number_of_students || 0,
        nextClass: getNextClassTime(), // Funció per generar un horari simulat
        groupId: group.id // Guardem l'ID del grup per a navegació
      }));
    }
  } catch (error) {
    console.error("Error al carregar grups del professor:", error);
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

// Carregar l'usuari i les dades des del localStorage quan el component es munta
onMounted(async () => {
  // Verificar que sigui un professor, tutor o administrador
  if (!authStore.isProfesor && !authStore.isTutor && !authStore.isAdmin) {
    console.log("Usuari no autoritzat per accedir al dashboard de professor");
    router.push('/login');
    return;
  }
  console.log("Usuari autoritzat per accedir al dashboard de professor:", authStore.userRole);

  // Carregar dades de l'usuari
  const storedUser = localStorage.getItem("user");
  if (storedUser) {
    userData.value = JSON.parse(storedUser);
    console.log("Dades d'usuari carregades:", userData.value);
  }

  // Cargar datos reales de clases
  await loadClassesData();
  
  // Carregar informació de formularis
  await fetchFormStats();

  console.log("Estado final del dashboard:", {
    totalStudents: dashboardState.totalStudents,
    activeGroups: dashboardState.activeGroups,
    completedForms: dashboardState.completedForms,
    currentClasses: dashboardState.currentClasses.map(c => ({
      name: c.name,
      students: c.students
    }))
  });
});

// Función de logout
const logout = () => {
  authStore.logout();
};

// Funció per seleccionar un curs i divisió específics
const selectCourseAndDivision = async (courseName, divisionName) => {
  try {
    // Buscar el course_id i division_id corresponents en les assignacions del professor
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

// Función para obtener estadísticas de formularios
const fetchFormStats = async () => {
  try {
    // Activar estado de carga
    dashboardState.loadingForms = true;
    
    // Obtener formularios asignados al profesor
    const token = authStore.token;
    const teacherId = authStore.user?.id;

    if (!token || !teacherId) {
      console.error("No hay token o ID de profesor disponible");
      dashboardState.loadingForms = false;
      return;
    }

    // Obtener todos los formularios del profesor
    const formsResponse = await fetch(`http://localhost:8000/api/forms?teacher_id=${teacherId}`, {
      method: "GET",
      headers: {
        Accept: "application/json",
        Authorization: `Bearer ${token}`
      }
    });

    if (!formsResponse.ok) {
      throw new Error("Error al obtener los formularios");
    }

    const forms = await formsResponse.json();
    console.log("Formularios obtenidos:", forms.length);

    // Objeto para almacenar el recuento de formularios por tipo
    const formStats = {};
    
    // Variables para contar formularios completados y pendientes
    let completedCount = 0;
    let pendingCount = 0;
    let totalStudentsCount = 0;
    const processedStudentIds = new Set(); // Para contar estudiantes únicos
    
    // Si no hay formularios, terminar aquí
    if (!forms || forms.length === 0) {
      console.log("No se encontraron formularios para este profesor");
      dashboardState.formCompletion = {};
      dashboardState.completedForms = 0;
      dashboardState.pendingForms = 0;
      dashboardState.loadingForms = false;
      return;
    }

    // Per a cada formulari, obtenir els usuaris assignats i les seves respostes
    for (const form of forms) {
      try {
        // Obtenir les respostes detallades per veure qui ha contestat realment
        const apiUrl = `http://localhost:8000/api/form-user/${form.id}/assigned-users`;
        const response = await fetch(apiUrl, {
          method: "GET",
          headers: {
            Accept: "application/json",
            Authorization: `Bearer ${token}`,
          },
        });

        if (!response.ok) {
          console.error(`Error al obtener respuestas para formulario ${form.id}: ${response.status}`);
          continue;
        }

        const data = await response.json();
        
        if (data.users && data.users.length > 0) {
          // Guardar los IDs de estudiantes para contar estudiantes únicos
          data.users.forEach(user => {
            processedStudentIds.add(user.id);
          });

          const totalStudents = data.users.length;
          
          // Verifiquem explícitament el camp answered en cada usuari
          let answeredCount = 0;
          for (const user of data.users) {
            if (user.answered === true || user.answered === 1 || user.answered === '1') {
              answeredCount++;
            }
          }
          
          // Usar el comptador explícit en lloc del filtrat ràpid
          const percentage = totalStudents > 0 ? Math.round((answeredCount / totalStudents) * 100) : 0;

          console.log(`Formulari ${form.title}: ${answeredCount}/${totalStudents} (${percentage}%)`);

          // Guardar estadísticas para este formulario
          formStats[form.title] = {
            completed: answeredCount,
            total: totalStudents,
            percentage: percentage
          };

          // Actualizar contadores globales
          // Para Formularis Completats, solo contar formularios con 100% de respuestas
          if (percentage === 100) {
            completedCount += 1; // Contar el formulario, no las respuestas
          } else {
            pendingCount += 1; // Contar como pendiente si no está al 100%
          }
        }
      } catch (error) {
        console.error(`Error al obtener estadísticas para el formulario ${form.id}:`, error);
      }
    }

    // Actualizar el estado del dashboard con las estadísticas reales
    dashboardState.formCompletion = formStats;
    dashboardState.completedForms = completedCount;
    dashboardState.pendingForms = pendingCount;
    
    // Actualizar el total de estudiantes único si no se estableció anteriormente
    if (dashboardState.totalStudents === 0) {
      dashboardState.totalStudents = processedStudentIds.size;
    }

    console.log("Estadísticas de formularios cargadas:", formStats);
    console.log("Total de estudiantes únicos:", processedStudentIds.size);
    console.log("Respuestas completadas:", completedCount);
    console.log("Respuestas pendientes:", pendingCount);
  } catch (error) {
    console.error("Error al obtener estadísticas de formularios:", error);
  } finally {
    // Desactivar estado de carga al finalizar
    dashboardState.loadingForms = false;
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <span class="ml-2 text-xl font-semibold text-gray-900">Grupify</span>
            </div>
          </div>



          <!-- Herramientas de usuario -->
          <div class="flex items-center space-x-4">
            <!-- Perfil de usuario -->
            <div class="flex items-center">
              <div v-if="userData" class="flex items-center">
                <img :src="userData.image || 'https://via.placeholder.com/32'" alt="Avatar"
                  class="h-8 w-8 rounded-full">
                <span class="ml-2 text-sm font-medium text-gray-700 hidden sm:block">{{ userData.name }}</span>
              </div>
              <button @click="logout" class="ml-3 text-gray-500 hover:text-primary">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Alumnes</p>
            <div v-if="coursesStore.loading || groupStore.loading" class="h-8 w-12 animate-pulse bg-gray-200 rounded">
            </div>
            <p v-else class="text-2xl font-bold">{{ dashboardState.totalStudents }}</p>
          </div>
        </div>

        <!-- Grupos activos -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
          <div class="rounded-full bg-green-100 p-3 mr-4">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Grups Actius</p>
            <div v-if="coursesStore.loading || groupStore.loading" class="h-8 w-8 animate-pulse bg-gray-200 rounded">
            </div>
            <p v-else class="text-2xl font-bold">{{ dashboardState.activeGroups }}</p>
          </div>
        </div>

        <!-- Formularios completados -->
        <div class="bg-white rounded-lg shadow p-5 flex items-center">
          <div class="rounded-full bg-indigo-100 p-3 mr-4">
            <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <div>
            <p class="text-gray-500 text-sm">Formularis Completats</p>
            <div class="flex items-baseline">
              <p class="text-2xl font-bold">{{ dashboardState.completedForms }}</p>
              <p class="ml-2 text-sm text-gray-600">de {{ Object.keys(dashboardState.formCompletion).length }}</p>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              Total de respostes: <span class="font-medium">{{ totalResponsesPercentage }}%</span>
            </p>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No s'han trobat classes</h3>
              <p class="mt-1 text-sm text-gray-500">Comença creant una classe o grup nou.</p>
              <div class="mt-6">
                <NuxtLink to="/professor/grups"
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                  <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Crear nou grup
                </NuxtLink>
              </div>
            </div>
            <!-- Lista de clases -->
            <div v-else class="divide-y divide-gray-200">
              <div v-for="(classItem, index) in dashboardState.currentClasses" :key="index"
                class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 cursor-pointer"
                @click="navigateToClass(classItem)">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div
                      class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-medium">
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
              <NuxtLink to="/professor/formularis" class="text-sm text-primary hover:text-primary-dark">
                Veure tots els formularis
              </NuxtLink>
            </div>
            
            <!-- Estado de carga -->
            <div v-if="dashboardState.loadingForms" class="p-12 flex justify-center items-center">
              <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary"></div>
            </div>
            
            <!-- Estado vacío - No hay formularios asignados -->
            <div v-else-if="Object.keys(dashboardState.formCompletion).length === 0" class="p-8 text-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <h3 class="mt-4 text-lg font-medium text-gray-900">Cap formulari assignat</h3>
              <p class="mt-2 text-sm text-gray-500">No tens cap formulari assignat als teus estudiants.</p>
              <div class="mt-6">
                <NuxtLink to="/professor/formularis" 
                  class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                  Assignar formularis
                </NuxtLink>
              </div>
            </div>
            
            <!-- Lista de formularios -->
            <div v-else class="p-6 space-y-4">
              <div v-for="(form, name) in dashboardState.formCompletion" :key="name" class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span>{{ name }}</span>
                  <span class="font-medium">{{ form.completed }}/{{ form.total }} ({{ form.percentage }}%)</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="h-2 rounded-full" :class="{
                    'bg-green-500': form.percentage >= 80,
                    'bg-yellow-500': form.percentage >= 50 && form.percentage < 80,
                    'bg-red-500': form.percentage < 50
                  }" :style="`width: ${form.percentage}%`"></div>
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
                <template v-for="item in menuItems" :key="item.title">
                  <NuxtLink v-if="!item.route.startsWith('http')" :to="item.route"
                    class="flex items-center p-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="h-5 w-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    <div>
                      <span class="text-sm font-medium text-gray-800">{{ item.title }}</span>
                      <p class="text-xs text-gray-500">{{ item.description }}</p>
                    </div>
                  </NuxtLink>
                  <a v-else :href="item.route" target="_blank" rel="noopener noreferrer"
                    class="flex items-center p-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="h-5 w-5 text-primary mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                    </svg>
                    <div>
                      <span class="text-sm font-medium text-gray-800">{{ item.title }}</span>
                      <p class="text-xs text-gray-500">{{ item.description }}</p>
                    </div>
                  </a>
                </template>
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
