import { defineStore } from "pinia";
import { ref, computed } from "vue";
import { useStudentsStore } from "~/stores/studentsStore";

export const useRelationshipsStore = defineStore("relationships", () => {
  const relationships = ref([]);
  const isLoading = ref(false);
  const error = ref(null);

  // Store de estudiantes
  const studentsStore = useStudentsStore();

  // Fetch de relaciones
  const fetchRelationships = async () => {
    isLoading.value = true;
    try {
      const response = await fetch(
        "http://localhost:8000/api/sociogram-relationships/sociogram-relationships"
      );
      if (!response.ok) throw new Error("Error al obtener las relaciones");
      relationships.value = await response.json();
    } catch (err) {
      console.error("Error al cargar relaciones:", err);
      error.value = "Error al cargar relaciones";
    } finally {
      isLoading.value = false;
    }
  };

  // Obtener relaciones por curso y división
  const getRelationshipsByCourseAndDivision = (courseName, divisionName) => {

    const studentIds = studentsStore.students
      .filter(
        student =>
          student.course === courseName && student.division === divisionName
      )
      .map(student => student.id);

    return computed(() => {
      const result = relationships.value
        .filter(
          rel =>
            studentIds.includes(rel.user_id) &&
            studentIds.includes(rel.peer_id) &&
            (rel.question_id === 15 || rel.question_id === 16)
        )
        .map(rel => {
          const user = studentsStore.students.find(
            student => student.id === rel.user_id
          );
          const peer = studentsStore.students.find(
            student => student.id === rel.peer_id
          );
          return {
            ...rel,
            user_name: user ? user.name : "Desconocido",
            user_last_name: user ? user.last_name : "",
            peer_name: peer ? peer.name : "Desconocido",
            peer_last_name: peer ? peer.last_name : "",
          };
        });

      // console.log('getRelationshipsByCourseAndDivision result:', result);
      return result;
    });
  };

  // Obtener habilidades por curso y división 
  const getSkillsByCourseAndDivision = (courseName, divisionName) => {
    const studentIds = studentsStore.students
      .filter(
        student =>
          student.course === courseName && student.division === divisionName
      )
      .map(student => student.id);

    return computed(() => {
      const skillsMap = {};

      relationships.value
        .filter(
          rel =>
            studentIds.includes(rel.user_id) &&
            studentIds.includes(rel.peer_id) &&
            [18, 19, 20].includes(rel.question_id)
        )
        .forEach(rel => {
          const peer = studentsStore.students.find(
            student => student.id === rel.peer_id
          );
          if (peer) {
            if (!skillsMap[peer.id]) {
              skillsMap[peer.id] = {
                peer_id: peer.id,
                peer_name: peer.name,
                peer_last_name: peer.last_name,
                liderazgo: 0,
                creatividad: 0,
                organizacion: 0,
              };
            }
            if (rel.question_id === 18) skillsMap[peer.id].liderazgo++;
            if (rel.question_id === 19) skillsMap[peer.id].creatividad++;
            if (rel.question_id === 20) skillsMap[peer.id].organizacion++;
          }
        });

      return Object.values(skillsMap);
    });
  };
  // Función para obtener los alumnos destacados en habilidades por curso y división
  const getHighlightedStudentsByCourse = (courseName, divisionName, threshold = 1.5) => {
    // Verificar si los parámetros son válidos
    if (!courseName || !divisionName) {
      console.warn('Valores de curso o división inválidos:', { courseName, divisionName });
      return computed(() => ({ hasHighlighted: false, students: [] }));
    }

    return computed(() => {
      // Verificar si los datos han sido cargados
      if (!studentsStore.students || studentsStore.students.length === 0) {
        console.warn('No hay datos de estudiantes cargados');
        return { hasHighlighted: false, students: [] };
      }

      if (!relationships.value || relationships.value.length === 0) {
        console.warn('No hay datos de relaciones cargados');
        return { hasHighlighted: false, students: [] };
      }

      // Obtener IDs de estudiantes para este curso y división
      const studentIds = studentsStore.students
        .filter(student =>
          student.course === courseName &&
          student.division === divisionName
        )
        .map(student => student.id);

      if (studentIds.length === 0) {
        console.warn(`No se encontraron estudiantes para ${courseName} - ${divisionName}`);
        return { hasHighlighted: false, students: [] };
      }

      // Conseguir los datos de habilidades para todos los estudiantes
      const skillsMap = {};

      // Inicializar mapa para todos los estudiantes
      studentIds.forEach(id => {
        const student = studentsStore.students.find(s => s.id === id);
        if (student) {
          skillsMap[id] = {
            id: student.id,
            name: student.name,
            last_name: student.last_name,
            liderazgo: 0,
            creatividad: 0,
            organizacion: 0,
            total: 0
          };
        }
      });

      // Contar las menciones para cada habilidad
      relationships.value
        .filter(
          rel =>
            studentIds.includes(rel.user_id) &&
            studentIds.includes(rel.peer_id) &&
            [18, 19, 20].includes(rel.question_id)
        )
        .forEach(rel => {
          if (skillsMap[rel.peer_id]) {
            if (rel.question_id === 18) skillsMap[rel.peer_id].liderazgo++;
            if (rel.question_id === 19) skillsMap[rel.peer_id].creatividad++;
            if (rel.question_id === 20) skillsMap[rel.peer_id].organizacion++;
            skillsMap[rel.peer_id].total++;
          }
        });

      // Calcular medias para cada habilidad
      const totals = Object.values(skillsMap).reduce(
        (acc, student) => {
          acc.liderazgo += student.liderazgo;
          acc.creatividad += student.creatividad;
          acc.organizacion += student.organizacion;
          acc.total += student.total;
          acc.count++;
          return acc;
        },
        { liderazgo: 0, creatividad: 0, organizacion: 0, total: 0, count: 0 }
      );

      const averages = {
        liderazgo: totals.count > 0 ? totals.liderazgo / totals.count : 0,
        creatividad: totals.count > 0 ? totals.creatividad / totals.count : 0,
        organizacion: totals.count > 0 ? totals.organizacion / totals.count : 0,
        total: totals.count > 0 ? totals.total / totals.count : 0
      };

      // Calcular desviaciones estándar para cada habilidad
      const sumSquaredDiff = Object.values(skillsMap).reduce(
        (acc, student) => {
          acc.liderazgo += Math.pow(student.liderazgo - averages.liderazgo, 2);
          acc.creatividad += Math.pow(student.creatividad - averages.creatividad, 2);
          acc.organizacion += Math.pow(student.organizacion - averages.organizacion, 2);
          acc.total += Math.pow(student.total - averages.total, 2);
          return acc;
        },
        { liderazgo: 0, creatividad: 0, organizacion: 0, total: 0 }
      );

      const stdDevs = {
        liderazgo: totals.count > 1 ? Math.sqrt(sumSquaredDiff.liderazgo / totals.count) : 0,
        creatividad: totals.count > 1 ? Math.sqrt(sumSquaredDiff.creatividad / totals.count) : 0,
        organizacion: totals.count > 1 ? Math.sqrt(sumSquaredDiff.organizacion / totals.count) : 0,
        total: totals.count > 1 ? Math.sqrt(sumSquaredDiff.total / totals.count) : 0
      };

      // Identificar estudiantes destacados (por encima del umbral * desviación estándar)
      const highlightedStudents = Object.values(skillsMap)
        .map(student => {
          // Calcula los Z-scores para cada habilidad
          const zScores = {
            liderazgo: stdDevs.liderazgo > 0 ? (student.liderazgo - averages.liderazgo) / stdDevs.liderazgo : 0,
            creatividad: stdDevs.creatividad > 0 ? (student.creatividad - averages.creatividad) / stdDevs.creatividad : 0,
            organizacion: stdDevs.organizacion > 0 ? (student.organizacion - averages.organizacion) / stdDevs.organizacion : 0,
            total: stdDevs.total > 0 ? (student.total - averages.total) / stdDevs.total : 0
          };

          // Determina en qué habilidades destaca este estudiante
          const highlightedSkills = [];
          if (zScores.liderazgo >= threshold) highlightedSkills.push('liderazgo');
          if (zScores.creatividad >= threshold) highlightedSkills.push('creatividad');
          if (zScores.organizacion >= threshold) highlightedSkills.push('organizacion');

          return {
            ...student,
            zScores,
            highlightedSkills,
            isHighlighted: highlightedSkills.length > 0
          };
        })
        .filter(student => student.isHighlighted);

      // Información sobre las medias y destacados
      return {
        courseName,
        divisionName,
        averages,
        stdDevs,
        hasHighlighted: highlightedStudents.length > 0,
        students: highlightedStudents,
        allStudents: Object.values(skillsMap) // Todos los estudiantes con sus puntuaciones
      };
    });
  };
  // Obtener roles por curso y división 
  const getRolesByCourseAndDivision = (courseName, divisionName) => {
    const studentIds = studentsStore.students
      .filter(
        student =>
          student.course === courseName && student.division === divisionName
      )
      .map(student => student.id);

    return computed(() => {
      const rolesMap = {};

      relationships.value
        .filter(
          rel =>
            studentIds.includes(rel.user_id) &&
            studentIds.includes(rel.peer_id) &&
            [17, 21].includes(rel.question_id)
        )
        .forEach(rel => {
          const peer = studentsStore.students.find(
            student => student.id === rel.peer_id
          );
          if (peer) {
            if (!rolesMap[peer.id]) {
              rolesMap[peer.id] = {
                peer_id: peer.id,
                peer_name: peer.name,
                peer_last_name: peer.last_name,
                popularitat: 0,
                aïllament: 0,
              };
            }
            if (rel.question_id === 17) rolesMap[peer.id].popularitat++;
            if (rel.question_id === 21) rolesMap[peer.id].aïllament++;
          }
        });
      return Object.values(rolesMap);
    });
  };
  //dades comparatius per curs -> popularitat/aillament
  const getPopularityDataByCourseAndDivision = (courseName, divisionName) => {
    // Verificar si los parámetros son válidos
    if (!courseName || !divisionName) {
      console.warn('Valores de curso o división inválidos:', { courseName, divisionName });
      return computed(() => []);
    }


    return computed(() => {
      // Verificar si los datos han sido cargados
      if (!studentsStore.students || studentsStore.students.length === 0) {
        console.warn('No hay datos de estudiantes cargados');
        return [];
      }


      if (!relationships.value || relationships.value.length === 0) {
        console.warn('No hay datos de relaciones cargados');
        return [];
      }


      // Obtener IDs de estudiantes para este curso y división
      const studentIds = studentsStore.students
        .filter(student =>
          student.course === courseName &&
          student.division === divisionName
        )
        .map(student => student.id);


      if (studentIds.length === 0) {
        console.warn(`No se encontraron estudiantes para ${courseName} - ${divisionName}`);
        // Mostrar valores únicos disponibles para ayudar a depurar
        const uniqueCourses = [...new Set(studentsStore.students.map(s => s.course))];
        const uniqueDivisions = [...new Set(studentsStore.students.map(s => s.division))];
        console.log('Cursos disponibles:', uniqueCourses);
        console.log('Divisiones disponibles:', uniqueDivisions);
        return [];
      }


      // Mapa para contar relaciones positivas y negativas
      const popularityMap = {};


      // Inicializar mapa para todos los estudiantes
      studentIds.forEach(id => {
        popularityMap[id] = {
          positives: 0,
          negatives: 0
        };
      });


      // Filtrar relaciones relevantes y contar
      relationships.value.forEach(rel => {
        if (studentIds.includes(rel.peer_id)) {
          if (rel.question_id === 15) {
            if (!popularityMap[rel.peer_id]) {
              popularityMap[rel.peer_id] = { positives: 0, negatives: 0 };
            }
            popularityMap[rel.peer_id].positives++;
          }
          if (rel.question_id === 16) {
            if (!popularityMap[rel.peer_id]) {
              popularityMap[rel.peer_id] = { positives: 0, negatives: 0 };
            }
            popularityMap[rel.peer_id].negatives++;
          }
        }
      });


      // Clasificar estudiantes según los valores
      let popular = 0;
      let isolated = 0;
      let neutral = 0;


      Object.values(popularityMap).forEach(({ positives, negatives }) => {
        if (positives > negatives) popular++;
        else if (negatives > positives) isolated++;
        else neutral++;
      });


      return [
        { label: "Populares", count: popular },
        { label: "Aislados", count: isolated },
        { label: "Neutrales", count: neutral },
      ];
    });
  };
  const generateCoursesComparison = (threshold = 1.5) => {
    const comparativeData = [];

    // Verificar que las stores estén disponibles
    if (!studentsStore.students || studentsStore.students.length === 0) {
      console.warn('No hay datos de estudiantes cargados');
      return [];
    }

    // Obtener cursos y divisiones únicos
    const uniqueCourses = [...new Set(studentsStore.students.map(s => s.course))];

    // Para cada curso y división, obtener los datos destacados
    uniqueCourses.forEach(courseName => {
      // Obtener divisiones para este curso
      const divisionsForCourse = [...new Set(
        studentsStore.students
          .filter(s => s.course === courseName)
          .map(s => s.division)
      )];

      divisionsForCourse.forEach(divisionName => {
        if (!courseName || !divisionName) return;

        const courseFullName = `${courseName} ${divisionName}`;

        // Obtener datos destacados para este curso específico
        const courseDataComputed = getHighlightedStudentsByCourse(
          courseName,
          divisionName,
          threshold
        );

        // Necesitamos acceder al valor computado
        const courseData = courseDataComputed.value;

        if (!courseData || !courseData.hasHighlighted) {
          // Si no hay alumnos destacados, añadir entrada con valores a cero
          comparativeData.push({
            course: courseFullName,
            liderazgo: 0,
            creatividad: 0,
            organizacion: 0,
            total: 0,
            // Añadir las medias y desviaciones específicas del curso
            averages: courseData ? courseData.averages : { liderazgo: 0, creatividad: 0, organizacion: 0 },
            stdDevs: courseData ? courseData.stdDevs : { liderazgo: 0, creatividad: 0, organizacion: 0 }
          });
          return; // Continuar con el siguiente
        }

        // Contar alumnos destacados en cada habilidad
        let liderazgoCount = 0;
        let creatividadCount = 0;
        let organizacionCount = 0;

        courseData.students.forEach(student => {
          if (student.highlightedSkills.includes('liderazgo')) liderazgoCount++;
          if (student.highlightedSkills.includes('creatividad')) creatividadCount++;
          if (student.highlightedSkills.includes('organizacion')) organizacionCount++;
        });

        // Añadir datos al comparativo incluyendo las medias y desviaciones
        comparativeData.push({
          course: courseFullName,
          liderazgo: liderazgoCount,
          creatividad: creatividadCount,
          organizacion: organizacionCount,
          total: liderazgoCount + creatividadCount + organizacionCount,
          averages: courseData.averages,
          stdDevs: courseData.stdDevs
        });
      });
    });

    // Ordenar por nombre de curso
    comparativeData.sort((a, b) => a.course.localeCompare(b.course));

    return comparativeData;
  };
  return {
    relationships,
    isLoading,
    error,
    fetchRelationships,
    getRelationshipsByCourseAndDivision,
    getSkillsByCourseAndDivision,
    getRolesByCourseAndDivision,
    getPopularityDataByCourseAndDivision,
    getHighlightedStudentsByCourse,
    generateCoursesComparison,
  };
});
