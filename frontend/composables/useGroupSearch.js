// composables/useGroupSearch.js
import { ref, computed, watch } from "vue";
import { useGroupStore } from "@/stores/groupStore";
import { useAuthStore } from "@/stores/authStore";

export function useGroupSearch(groupsRef) {
  const searchQuery = ref("");
  const selectedCourseId = ref(null);
  const selectedDivisionId = ref(null);
  const isLoading = ref(false);
  const groupStore = useGroupStore();
  const authStore = useAuthStore();

  // Obtener las asignaciones curso-división del profesor
  const teacherAssignments = computed(() => {
    const user = authStore.user;
    
    // Formato nuevo: array de course_divisions
    if (user?.course_divisions && Array.isArray(user.course_divisions)) {
      return user.course_divisions.map(cd => ({
        courseId: cd.course_id,
        courseName: cd.course_name,
        divisionId: cd.division_id,
        divisionName: cd.division_name
      }));
    }
    
    // Formato antiguo: un solo curso/división
    if (user?.course_id && user?.division_id) {
      return [{
        courseId: user.course_id,
        courseName: user.course_name || 'Sin nombre',
        divisionId: user.division_id,
        divisionName: user.division_name || 'Sin nombre'
      }];
    }
    
    return [];
  });

  // Cursos únicos disponibles para el profesor
  const availableCourses = computed(() => {
    const uniqueCourses = new Map();
    
    teacherAssignments.value.forEach(assignment => {
      if (!uniqueCourses.has(assignment.courseId)) {
        uniqueCourses.set(assignment.courseId, {
          id: assignment.courseId,
          name: assignment.courseName
        });
      }
    });
    
    return Array.from(uniqueCourses.values());
  });

  // Divisiones disponibles según el curso seleccionado
  const availableDivisions = computed(() => {
    if (!selectedCourseId.value) {
      return [];
    }
    
    const divisions = new Map();
    
    teacherAssignments.value
      .filter(assignment => assignment.courseId === selectedCourseId.value)
      .forEach(assignment => {
        if (!divisions.has(assignment.divisionId)) {
          divisions.set(assignment.divisionId, {
            id: assignment.divisionId,
            name: assignment.divisionName
          });
        }
      });
    
    return Array.from(divisions.values());
  });

  // Actualizar filtros cuando cambian los valores de curso o división
  watch([selectedCourseId, selectedDivisionId], async ([newCourseId, newDivisionId], [oldCourseId, oldDivisionId]) => {
    if (newCourseId !== oldCourseId || newDivisionId !== oldDivisionId) {
      // Sólo actualizar si realmente cambiaron los valores
      isLoading.value = true;
      try {
        await groupStore.fetchGroups({
          course_id: newCourseId,
          division_id: newDivisionId
        });
      } catch (error) {
        console.error("Error al obtener grupos filtrados:", error);
      } finally {
        isLoading.value = false;
      }
    }
  });

  const filteredGroups = computed(() => {
    const groups = groupsRef.value || [];
    
    if (!searchQuery.value) {
      return groups;
    }

    return groups.filter(group => {
      // Manejar valores nulos/undefined
      const groupName = group.name?.toLowerCase() || "";
      const groupDescription = group.description?.toLowerCase() || "";

      const searchTerm = searchQuery.value.toLowerCase();

      return groupName.includes(searchTerm) || groupDescription.includes(searchTerm);
    });
  });

  return {
    searchQuery,
    selectedCourseId,
    selectedDivisionId,
    filteredGroups,
    isLoading,
    availableCourses,
    availableDivisions,
    teacherAssignments,
    
    // Función para establecer los filtros y actualizar
    setFilters: async (courseId, divisionId) => {
      selectedCourseId.value = courseId;
      selectedDivisionId.value = divisionId;
      
      isLoading.value = true;
      try {
        await groupStore.fetchGroups({
          course_id: courseId,
          division_id: divisionId
        });
      } catch (error) {
        console.error("Error al obtener grupos filtrados:", error);
      } finally {
        isLoading.value = false;
      }
    },
    
    // Función para resetear los filtros
    resetFilters: async () => {
      selectedCourseId.value = null;
      selectedDivisionId.value = null;
      
      isLoading.value = true;
      try {
        await groupStore.fetchGroups({});
      } catch (error) {
        console.error("Error al obtener todos los grupos:", error);
      } finally {
        isLoading.value = false;
      }
    }
  };
}
