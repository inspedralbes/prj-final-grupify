import { defineStore } from "pinia";

export const useCoursesStore = defineStore("courses", {
  state: () => ({
    courses: [],
    loading: false,
    error: null,
    onlinecourses: new Set(),
  }),

  actions: {
    async fetchCourses(force = false, userId = null) {
      if (!force && this.courses.length > 0) {
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        // Construir la URL con el parámetro user_id si está disponible
        let url = "https://api.basebrutt.com/api/courses-with-divisions";
        if (userId) {
          url += `?user_id=${userId}`;
        }

        const response = await fetch(url);

        if (!response.ok) {
          throw new Error(`Error al obtener cursos: ${response.statusText}`);
        }

        const data = await response.json();

        // Validamos que la respuesta sea un array
        if (!Array.isArray(data)) {
          throw new Error(
            "La respuesta de la API no tiene el formato esperado."
          );
        }

        // Transformamos la respuesta para separar divisiones como cursos individuales
        const transformedCourses = data.flatMap(course =>
          Object.values(course.divisions).map(division => ({
            classId: course.id * 1000 + division.id,
            courseId: course.id,
            courseName: course.name,
            division: {
              id: division.id,
              name: division.name,
            },
            active: course.active ?? false,
            sociograma_available: false,
          }))
        );

        // Asignamos los cursos transformados al estado 'courses'
        this.courses = transformedCourses;
      } catch (error) {
        this.error = `Error al cargar los cursos: ${error.message}`;
        console.error("Error al cargar los cursos:", error);
      } finally {
        this.loading = false;
      }
    },

    addOnlineCourse(courseId) {
      this.onlinecourses.add(courseId);
    },

    removeOnlineCourse(courseId) {
      this.onlinecourses.delete(courseId);
    },
  },

  getters: {
    activeCourses() {
      return this.courses.filter(course => course.active); // Devuelve solo los cursos activos
    },

    allCourses() {
      return this.courses; // Retorna todos los cursos (ya transformados con divisiones)
    },

    // Para obtener los cursos que pertenecen a una división específica
    getCoursesByDivision(divisionId) {
      return this.courses.filter(course => course.division.id === divisionId);
    },

    isLoading() {
      return this.loading; // Getter para obtener el estado de carga
    },

    hasError() {
      return this.error !== null; // Getter para verificar si hubo un error
    },
    getCourseByClassId: state => classId => {
      return state.courses.find(course => course.class_id === Number(classId));
    },
  },
});
