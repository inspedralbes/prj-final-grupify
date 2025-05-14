// composables/useStudentSearch.js
import { ref, computed, watch } from "vue";
import { useStudentsStore } from "@/stores/studentsStore";
import { useAuthStore } from "@/stores/authStore";

export function useStudentSearch(initialStudents = []) {
  const studentsStore = useStudentsStore();
  const authStore = useAuthStore();
  const students = ref(initialStudents);
  const searchQuery = ref("");
  const selectedCourse = ref("all");
  const selectedDivision = ref("all");

  // Filtro de estudiantes con lógica mejorada
  const filteredStudents = computed(() => {
    if (!students.value) return [];

    return students.value.filter(student => {
      // Filtro por texto de búsqueda (nombre, apellido, email)
      const matchesSearch =
        searchQuery.value === "" || 
        student.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        student.last_name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        student.email?.toLowerCase().includes(searchQuery.value.toLowerCase());

      // Filtro por curso
      const matchesCourse =
        selectedCourse.value === "all" ||
        (student.course && student.course === selectedCourse.value);

      // Filtro por división
      const matchesDivision =
        selectedDivision.value === "all" ||
        (student.division && student.division === selectedDivision.value);

      // Aplicar todos los filtros
      return matchesSearch && matchesCourse && matchesDivision;
    });
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
