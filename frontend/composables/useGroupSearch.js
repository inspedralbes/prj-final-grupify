// composables/useGroupSearch.js
import { ref, computed } from "vue";

export function useGroupSearch(groupsRef) {
  const searchQuery = ref("");
  const selectedCourse = ref("all");
  const selectedDivision = ref("all");

  const filteredGroups = computed(() => {
    const groups = groupsRef.value || [];

    return groups.filter(group => {
      // Manejar valores nulos/undefined
      const groupName = group.name?.toLowerCase() || "";
      const groupDescription = group.description?.toLowerCase() || "";
      const groupCourse = group.course || "";
      const groupDivision = group.division || "";

      const searchTerm = searchQuery.value.toLowerCase();

      const matchesSearch =
        groupName.includes(searchTerm) || groupDescription.includes(searchTerm);

      const matchesCourse =
        selectedCourse.value === "all" ||
        groupCourse.startsWith(selectedCourse.value);

      const matchesDivision =
        selectedDivision.value === "all" ||
        groupDivision === selectedDivision.value;

      return matchesSearch && matchesCourse && matchesDivision;
    });
  });

  return {
    searchQuery,
    selectedCourse,
    selectedDivision,
    filteredGroups,
  };
}
