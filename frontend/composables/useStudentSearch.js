// composables/useStudentSearch.js
import { ref, computed, watch } from "vue";
import { useStudentsStore } from "@/stores/studentsStore";
import { useAuthStore } from "@/stores/authStore";

export function useStudentSearch(initialStudents = []) {
  const studentsStore = useStudentsStore();
  const authStore = useAuthStore();
  const students = ref(initialStudents);
  const searchQuery = ref("");
  const selectedCourse = ref("");
  const selectedDivision = ref("");

  // Filtro de estudiantes con lógica mejorada y logging para depuración
  const filteredStudents = computed(() => {
    if (!students.value || students.value.length === 0) return [];

    console.log("Total estudiantes a filtrar:", students.value.length);
    console.log("Filtros actuales - Curso:", selectedCourse.value, "División:", selectedDivision.value);
    
    // Mostrar algunos ejemplos de los datos de estudiantes para depuración
    if (students.value.length > 0) {
      console.log("Ejemplos de datos de estudiantes:");
      console.log("Estudiante 1:", {
        id: students.value[0].id,
        name: students.value[0].name,
        course_name: students.value[0].course_name,
        division_name: students.value[0].division_name
      });
    }

    // Aplicar filtros con validación más estricta
    const filtered = students.value.filter(student => {
      // Filtro por texto de búsqueda (nombre, apellido, email)
      const matchesSearch =
        searchQuery.value === "" || 
        (student.name && student.name.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
        (student.last_name && student.last_name.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
        (student.email && student.email.toLowerCase().includes(searchQuery.value.toLowerCase()));

      // Filtro por curso y división
      let matchesCourseAndDivision = true;
      
      // Solo aplicar filtro de curso/división si se han seleccionado valores
      if (selectedCourse.value !== "" && selectedDivision.value !== "") {
        // Verificar si la información del curso y división está presente en el estudiante
        if (!student.course_name || !student.division_name) {
          matchesCourseAndDivision = false;
        } else {
          // Aplicar filtro de curso y división
          matchesCourseAndDivision = 
            student.course_name === selectedCourse.value && 
            student.division_name === selectedDivision.value;
        }
      }

      // Aplicar todos los filtros
      return matchesSearch && matchesCourseAndDivision;
    });

    console.log("Estudiantes filtrados:", filtered.length);
    return filtered;
  });

  // Función para verificar si un estudiante pertenece a alguno de los cursos del profesor
  const isInTeacherCourses = (student) => {
    if (!authStore.user?.course_divisions || authStore.user.course_divisions.length === 0) {
      return true; // Si no hay asignaciones específicas, mostrar todos
    }

    // Verificar si el estudiante pertenece a alguna de las asignaciones del profesor
    return authStore.user.course_divisions.some(cd => 
      cd.course_id === student.course_id && cd.division_id === student.division_id
    );
  };

  const setStudents = newStudents => {
    students.value = newStudents;
  };

  return {
    students,
    searchQuery,
    selectedCourse,
    selectedDivision,
    filteredStudents,
    setStudents,
    isInTeacherCourses
  };
}
