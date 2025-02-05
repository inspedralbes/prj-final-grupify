import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { useStudentsStore } from '~/stores/studentsStore';

export const useRelationshipsStore = defineStore('relationships', () => {
  const relationships = ref([]);  // Estado para todas las relaciones
  const isLoading = ref(false);
  const error = ref(null);

  // Obtener todas las relaciones desde la API
  const fetchRelationships = async () => {
    isLoading.value = true;
    try {
      const response = await fetch("http://localhost:8000/api/sociogram-relationships/sociogram-relationships");
      if (!response.ok) throw new Error("Error al obtener las relaciones");
      relationships.value = await response.json();
    } catch (err) {
      console.error("Error al cargar relaciones:", err);
      error.value = "Error al cargar relaciones";
    } finally {
      isLoading.value = false;
    }
  };

  // Getter para filtrar relaciones por curso y división y enriquecer con name y last_name
  const getRelationshipsByCourseAndDivision = (courseName, divisionName) => {
    const studentsStore = useStudentsStore();
    const studentIds = studentsStore.students
      .filter(student => student.course === courseName && student.division === divisionName)
      .map(student => student.id);

    return computed(() =>
      relationships.value
        .filter(rel =>
          studentIds.includes(rel.user_id) &&
          studentIds.includes(rel.peer_id) &&
          (rel.question_id === 15 || rel.question_id === 16)
        )
        .map(rel => {
          const user = studentsStore.students.find(student => student.id === rel.user_id);
          const peer = studentsStore.students.find(student => student.id === rel.peer_id);
          return {
            ...rel,
            peer_name: peer ? peer.name : 'Desconocido',
            peer_last_name: peer ? peer.last_name : '',
            user_name: user ? user.name : 'Desconocido',
            user_last_name: user ? user.last_name : '',
          };
        })
    );
  };

  return {
    relationships,
    isLoading,
    error,
    fetchRelationships,
    getRelationshipsByCourseAndDivision,
  };
});
