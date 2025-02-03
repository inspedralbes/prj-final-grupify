// composables/useStudentSearch.js
import { ref, computed } from "vue";

export function useStudentSearch(initialStudents = []) {
  const students = ref(initialStudents);
  const searchQuery = ref("");
  const selectedCourse = ref("all");
  const selectedDivision = ref("all");

  const filteredStudents = computed(() => {
    if (!students.value) return [];

    return students.value.filter(student => {
      const matchesSearch =
        student.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        student.email.toLowerCase().includes(searchQuery.value.toLowerCase());

      const matchesCourse =
        selectedCourse.value === "all" ||
        student.course.startsWith(selectedCourse.value);

      const matchesDivision =
        selectedDivision.value === "all" ||
        student.division === selectedDivision.value;

      return matchesSearch && matchesCourse && matchesDivision;
    });
  });

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
  };
}
