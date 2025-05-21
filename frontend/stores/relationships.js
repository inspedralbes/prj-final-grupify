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
        "https://api.basebrutt.com/api/sociogram-relationships/sociogram-relationships"
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
      // Inicializar mapa para TODOS los estudiantes con valores a 0
      const skillsMap = {};

      // Asegurarnos que todos los estudiantes estén en el mapa, incluso los que no reciben votos
      studentIds.forEach(id => {
        const student = studentsStore.students.find(s => s.id === id);
        if (student) {
          skillsMap[id] = {
            peer_id: id,
            peer_name: student.name,
            peer_last_name: student.last_name,
            liderazgo: 0,
            creatividad: 0,
            organizacion: 0
          };
        }
      });

      // Ahora procesamos los votos recibidos
      relationships.value
        .filter(
          rel =>
            studentIds.includes(rel.user_id) &&
            studentIds.includes(rel.peer_id) &&
            [18, 19, 20].includes(rel.question_id)
        )
        .forEach(rel => {
          // Solo incrementamos si el estudiante ya está en el mapa
          if (skillsMap[rel.peer_id]) {
            if (rel.question_id === 18) skillsMap[rel.peer_id].liderazgo++;
            if (rel.question_id === 19) skillsMap[rel.peer_id].creatividad++;
            if (rel.question_id === 20) skillsMap[rel.peer_id].organizacion++;
          }
        });

      // Retornamos todos los estudiantes, incluso los que no recibieron votos
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

      // Calcular totales para cada habilidad
      const totalStudents = Object.keys(skillsMap).length;

      // Suma de puntuaciones para cada habilidad
      const totals = {
        liderazgo: 0,
        creatividad: 0,
        organizacion: 0,
        total: 0
      };

      // Acumular puntuaciones
      Object.values(skillsMap).forEach(student => {
        totals.liderazgo += student.liderazgo;
        totals.creatividad += student.creatividad;
        totals.organizacion += student.organizacion;
        totals.total += student.total;
      });

      // Tabla de estudiantes con votos (incluyendo los que tienen 0 votos)
      const studentsWithVotes = Object.values(skillsMap).map(student => ({
        id: student.id,
        name: student.name,
        last_name: student.last_name,
        liderazgo: student.liderazgo,
        creatividad: student.creatividad,
        organizacion: student.organizacion,
        total: student.total
      }));

      // Calcular medias dividiendo por el número total de estudiantes
      const averages = {
        liderazgo: totalStudents > 0 ? totals.liderazgo / totalStudents : 0,
        creatividad: totalStudents > 0 ? totals.creatividad / totalStudents : 0,
        organizacion: totalStudents > 0 ? totals.organizacion / totalStudents : 0,
        total: totalStudents > 0 ? totals.total / totalStudents : 0
      };

      // Para depurar el problema de las medias iguales a 3
      console.log(`Datos para ${courseName} ${divisionName}:`);
      console.log('Total estudiantes:', totalStudents);
      console.log('Totales acumulados:', totals);
      console.log('Medias calculadas:', averages);
      console.log('Puntuaciones individuales:', skillsMap);

      // Calcular medias mejoradas basadas en votos reales
      const mediasReales = {
        liderazgo: totalStudents > 0 ? totals.liderazgo / totalStudents : 0,
        creatividad: totalStudents > 0 ? totals.creatividad / totalStudents : 0,
        organizacion: totalStudents > 0 ? totals.organizacion / totalStudents : 0,
        total: totalStudents > 0 ? totals.total / totalStudents : 0
      };

      // Calcular desviaciones estándar para cada habilidad
      const sumSquaredDiff = {
        liderazgo: 0,
        creatividad: 0,
        organizacion: 0,
        total: 0
      };

      Object.values(skillsMap).forEach(student => {
        sumSquaredDiff.liderazgo += Math.pow(student.liderazgo - averages.liderazgo, 2);
        sumSquaredDiff.creatividad += Math.pow(student.creatividad - averages.creatividad, 2);
        sumSquaredDiff.organizacion += Math.pow(student.organizacion - averages.organizacion, 2);
        sumSquaredDiff.total += Math.pow(student.total - averages.total, 2);
      });

      const stdDevs = {
        liderazgo: totalStudents > 1 ? Math.sqrt(sumSquaredDiff.liderazgo / totalStudents) : 0,
        creatividad: totalStudents > 1 ? Math.sqrt(sumSquaredDiff.creatividad / totalStudents) : 0,
        organizacion: totalStudents > 1 ? Math.sqrt(sumSquaredDiff.organizacion / totalStudents) : 0,
        total: totalStudents > 1 ? Math.sqrt(sumSquaredDiff.total / totalStudents) : 0
      };

      // Verificar si hay al menos un voto para calcular las distribuciones
      const hasAnyVotes = totals.total > 0;

      // Calcular distribuciones solo si hay votos, de lo contrario simular datos para no mostrar 0.00
      const distributions = {
        liderazgo: hasAnyVotes ?
          Object.values(skillsMap)
            .map(s => ({
              id: s.id, name: s.name, last_name: s.last_name, votes: s.liderazgo,
              percentage: totals.liderazgo > 0 ? (s.liderazgo / totals.liderazgo) * 100 : 0
            }))
            .sort((a, b) => b.votes - a.votes)
            .slice(0, 5) :
          // Datos simulados para cuando no hay votos
          studentIds.slice(0, 5).map(id => {
            const student = studentsStore.students.find(s => s.id === id);
            return {
              id: student?.id || 0,
              name: student?.name || 'Estudiante',
              last_name: student?.last_name || '',
              votes: 0,
              percentage: 0
            };
          }),

        creatividad: hasAnyVotes ?
          Object.values(skillsMap)
            .map(s => ({
              id: s.id, name: s.name, last_name: s.last_name, votes: s.creatividad,
              percentage: totals.creatividad > 0 ? (s.creatividad / totals.creatividad) * 100 : 0
            }))
            .sort((a, b) => b.votes - a.votes)
            .slice(0, 5) :
          // Datos simulados para cuando no hay votos
          studentIds.slice(0, 5).map(id => {
            const student = studentsStore.students.find(s => s.id === id);
            return {
              id: student?.id || 0,
              name: student?.name || 'Estudiante',
              last_name: student?.last_name || '',
              votes: 0,
              percentage: 0
            };
          }),

        organizacion: hasAnyVotes ?
          Object.values(skillsMap)
            .map(s => ({
              id: s.id, name: s.name, last_name: s.last_name, votes: s.organizacion,
              percentage: totals.organizacion > 0 ? (s.organizacion / totals.organizacion) * 100 : 0
            }))
            .sort((a, b) => b.votes - a.votes)
            .slice(0, 5) :
          // Datos simulados para cuando no hay votos
          studentIds.slice(0, 5).map(id => {
            const student = studentsStore.students.find(s => s.id === id);
            return {
              id: student?.id || 0,
              name: student?.name || 'Estudiante',
              last_name: student?.last_name || '',
              votes: 0,
              percentage: 0
            };
          })
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

      // Calcular índices de variación y concentración
      let coeficienteVariacion = {
        liderazgo: mediasReales.liderazgo > 0 ? stdDevs.liderazgo / mediasReales.liderazgo : 0,
        creatividad: mediasReales.creatividad > 0 ? stdDevs.creatividad / mediasReales.creatividad : 0,
        organizacion: mediasReales.organizacion > 0 ? stdDevs.organizacion / mediasReales.organizacion : 0
      };

      // Calcular índices de concentración (Herfindahl-Hirschman)
      let indiceConcentracion = {
        liderazgo: 0,
        creatividad: 0,
        organizacion: 0
      };

      // Solo calcular si hay votos
      if (totals.liderazgo > 0) {
        indiceConcentracion.liderazgo = Object.values(skillsMap).reduce((sum, student) => {
          const percentage = student.liderazgo / totals.liderazgo;
          return sum + (percentage * percentage);
        }, 0);
      }

      if (totals.creatividad > 0) {
        indiceConcentracion.creatividad = Object.values(skillsMap).reduce((sum, student) => {
          const percentage = student.creatividad / totals.creatividad;
          return sum + (percentage * percentage);
        }, 0);
      }

      if (totals.organizacion > 0) {
        indiceConcentracion.organizacion = Object.values(skillsMap).reduce((sum, student) => {
          const percentage = student.organizacion / totals.organizacion;
          return sum + (percentage * percentage);
        }, 0);
      }

      // Información sobre las medias y destacados
      return {
        courseName,
        divisionName,
        averages,           // Medias originales
        mediasReales,       // Nuevas medias calculadas
        stdDevs,
        hasHighlighted: highlightedStudents.length > 0,
        students: highlightedStudents,
        allStudents: Object.values(skillsMap), // Todos los estudiantes con sus puntuaciones
        studentsWithVotes,   // Tabla con los votos de cada alumno
        distributions,      // Distribución de votos para los 5 mejores estudiantes
        coeficienteVariacion, // Coeficiente de variación
        indiceConcentracion  // Índice de concentración (HHI)
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

      // Inicializar todos los estudiantes
      studentIds.forEach(id => {
        const student = studentsStore.students.find(s => s.id === id);
        if (student) {
          rolesMap[id] = {
            peer_id: id,
            peer_name: student.name,
            peer_last_name: student.last_name,
            popularitat: 0,
            aïllament: 0,
          };
        }
      });

      // Procesar votos
      relationships.value
        .filter(
          rel =>
            studentIds.includes(rel.user_id) &&
            studentIds.includes(rel.peer_id) &&
            [17, 21].includes(rel.question_id)
        )
        .forEach(rel => {
          if (rolesMap[rel.peer_id]) {
            if (rel.question_id === 17) rolesMap[rel.peer_id].popularitat++;
            if (rel.question_id === 21) rolesMap[rel.peer_id].aïllament++;
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
            popularityMap[rel.peer_id].positives++;
          }
          if (rel.question_id === 16) {
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

    // Determinar el nivel educativo del orientador (ESO o Bachillerato)
    // Obtener el usuario actual del localStorage
    let nivelEducativo = 'eso'; // Por defecto, asumimos ESO

    try {
      const userString = localStorage.getItem('user');
      if (userString) {
        const user = JSON.parse(userString);

        // Verificar si hay cursos asignados al orientador
        if (user && user.course_divisions && user.course_divisions.length > 0) {
          // Buscar si hay algún curso de bachillerato
          const hasBachillerato = user.course_divisions.some(cd => {
            const courseName = cd.course_name.toLowerCase();
            return courseName.includes('batx') || courseName.includes('bachiller');
          });

          if (hasBachillerato) {
            nivelEducativo = 'bachillerato';
          }
        }

        console.log('Nivel educativo del orientador:', nivelEducativo);
      }
    } catch (error) {
      console.error('Error al determinar nivel educativo:', error);
    }

    // Obtener cursos y divisiones únicos filtrados por nivel educativo
    let uniqueCourses = [...new Set(studentsStore.students.map(s => s.course))];

    // Filtrar los cursos según el nivel educativo del orientador
    uniqueCourses = uniqueCourses.filter(courseName => {
      if (!courseName) return false;

      const courseNameLower = courseName.toLowerCase();
      if (nivelEducativo === 'eso') {
        return courseNameLower.includes('eso');
      } else if (nivelEducativo === 'bachillerato') {
        return courseNameLower.includes('batx') || courseNameLower.includes('bachiller');
      }

      return true; // Si no hay filtro, mostrar todos
    });

    console.log('Cursos filtrados por nivel educativo:', uniqueCourses);

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

        // Para depurar
        console.log(`Comparación para ${courseFullName}:`, courseData?.averages);

        if (!courseData) {
          // Si no hay datos del curso, añadir entrada con valores a cero
          comparativeData.push({
            course: courseFullName,
            liderazgo: 0,
            creatividad: 0,
            organizacion: 0,
            total: 0,
            averages: { liderazgo: 0, creatividad: 0, organizacion: 0 },
            stdDevs: { liderazgo: 0, creatividad: 0, organizacion: 0 },
            studentsWithVotes: []
          });
          return; // Continuar con el siguiente
        }

        // Contar alumnos destacados en cada habilidad
        let liderazgoCount = 0;
        let creatividadCount = 0;
        let organizacionCount = 0;

        // Si hay estudiantes destacados, contar por cada habilidad
        if (courseData.hasHighlighted) {
          courseData.students.forEach(student => {
            if (student.highlightedSkills.includes('liderazgo')) liderazgoCount++;
            if (student.highlightedSkills.includes('creatividad')) creatividadCount++;
            if (student.highlightedSkills.includes('organizacion')) organizacionCount++;
          });
        }

        // Obtener lista de estudiantes con sus votos
        const studentsWithVotes = courseData.allStudents || [];

        // Total de estudiantes que participaron
        const totalStudents = studentsWithVotes.length;

        // Calcular el total de votos por competencia
        const competenciasTotales = {
          liderazgo: 0,
          creatividad: 0,
          organizacion: 0,
          total: 0
        };

        // Sumar todos los votos
        studentsWithVotes.forEach(student => {
          competenciasTotales.liderazgo += student.liderazgo;
          competenciasTotales.creatividad += student.creatividad;
          competenciasTotales.organizacion += student.organizacion;
          competenciasTotales.total += student.total;
        });

        // Calcular métricas alternativas

        // 1. Medias convencionales (votos totales / número de alumnos)
        const mediasConvencionales = {
          liderazgo: totalStudents > 0 ? competenciasTotales.liderazgo / totalStudents : 0,
          creatividad: totalStudents > 0 ? competenciasTotales.creatividad / totalStudents : 0,
          organizacion: totalStudents > 0 ? competenciasTotales.organizacion / totalStudents : 0,
          total: totalStudents > 0 ? competenciasTotales.total / totalStudents : 0
        };

        // 2. Calcular distribución porcentual de votos
        const studentsWithDistribucion = studentsWithVotes.map(student => {
          return {
            ...student,
            porcentaje_liderazgo: competenciasTotales.liderazgo > 0
              ? (student.liderazgo / competenciasTotales.liderazgo) * 100 : 0,
            porcentaje_creatividad: competenciasTotales.creatividad > 0
              ? (student.creatividad / competenciasTotales.creatividad) * 100 : 0,
            porcentaje_organizacion: competenciasTotales.organizacion > 0
              ? (student.organizacion / competenciasTotales.organizacion) * 100 : 0,
            porcentaje_total: competenciasTotales.total > 0
              ? (student.total / competenciasTotales.total) * 100 : 0
          };
        });

        // 3. Calcular el coeficiente de variación (desviación estándar / media)
        // Esto mide cuán dispersos están los votos (mayor valor = distribución menos uniforme)
        const coeficienteDeVariacion = {
          liderazgo: mediasConvencionales.liderazgo > 0
            ? courseData.stdDevs.liderazgo / mediasConvencionales.liderazgo : 0,
          creatividad: mediasConvencionales.creatividad > 0
            ? courseData.stdDevs.creatividad / mediasConvencionales.creatividad : 0,
          organizacion: mediasConvencionales.organizacion > 0
            ? courseData.stdDevs.organizacion / mediasConvencionales.organizacion : 0,
          total: mediasConvencionales.total > 0
            ? courseData.stdDevs.total / mediasConvencionales.total : 0
        };

        // 4. Índice de concentración (suma de cuadrados de los porcentajes)
        // Similar al índice Herfindahl-Hirschman, mayor valor = más concentración
        const indiceConcentracion = {
          liderazgo: studentsWithDistribucion.reduce((sum, student) =>
            sum + Math.pow(student.porcentaje_liderazgo / 100, 2), 0),
          creatividad: studentsWithDistribucion.reduce((sum, student) =>
            sum + Math.pow(student.porcentaje_creatividad / 100, 2), 0),
          organizacion: studentsWithDistribucion.reduce((sum, student) =>
            sum + Math.pow(student.porcentaje_organizacion / 100, 2), 0),
          total: studentsWithDistribucion.reduce((sum, student) =>
            sum + Math.pow(student.porcentaje_total / 100, 2), 0)
        };

        // Añadir datos al comparativo incluyendo las métricas calculadas
        comparativeData.push({
          course: courseFullName,
          liderazgo: liderazgoCount,
          creatividad: creatividadCount,
          organizacion: organizacionCount,
          total: liderazgoCount + creatividadCount + organizacionCount,
          // Métricas calculadas
          averages: courseData.averages,             // Medias originales
          mediasConvencionales: mediasConvencionales,// Medias calculadas
          coeficienteDeVariacion: coeficienteDeVariacion, // Coeficiente de variación
          indiceConcentracion: indiceConcentracion,  // Índice de concentración
          stdDevs: courseData.stdDevs,
          studentsWithDistribucion: studentsWithDistribucion // Estudiantes con porcentajes
        });
      });
    });

    // Ordenar por nombre de curso
    comparativeData.sort((a, b) => a.course.localeCompare(b.course));

    // Para depurar
    console.log('Datos comparativos finales:', comparativeData);
    console.log('Estudiantes con distribución (primer curso):',
      comparativeData[0]?.studentsWithDistribucion?.slice(0, 3) || 'No hay datos');
    console.log('Métricas calculadas (primer curso):', {
      mediasConvencionales: comparativeData[0]?.mediasConvencionales,
      coeficienteVariacion: comparativeData[0]?.coeficienteDeVariacion,
      indiceConcentracion: comparativeData[0]?.indiceConcentracion
    });

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