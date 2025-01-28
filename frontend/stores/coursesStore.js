import { defineStore } from 'pinia';

export const useCoursesStore = defineStore('courses', {
  state: () => ({
    courses: [],        
    loading: false,     
    error: null,       
    onlinecourses: new Set(),
  }),

  actions: {
    async fetchCourses(force = false) {
      if (!force && this.courses.length > 0) {
        return; 
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await fetch('http://localhost:8000/api/courses-with-divisions');

        if (!response.ok) {
          throw new Error(`Error al obtener cursos: ${response.statusText}`);
        }

        const data = await response.json();

        // Validamos que la respuesta sea un array
        if (!Array.isArray(data)) {
          throw new Error("La respuesta de la API no tiene el formato esperado.");
        }

        // Transformamos la respuesta para separar divisiones como cursos individuales
        const transformedCourses = [];
        data.forEach(course => {
          // Obtener todas las divisiones a partir de los valores del objeto 'divisions'
          const divisionsArray = Object.values(course.divisions);  // Convierte el objeto de divisiones en un array

          divisionsArray.forEach(division => {
            transformedCourses.push({
              courseId: course.id,       // ID del curso principal
              courseName: course.name,   // Nombre del curso
              division: division,       // División como un objeto separado
              active: course.active,     // Esto es provisional porque no está en backend 
            });
          });
        });

        // Asignamos los cursos transformados al estado 'courses'
        this.courses = transformedCourses;

      } catch (error) {
        this.error = `Error al cargar los cursos: ${error.message}`;
        console.error('Error al cargar los cursos:', error);
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
  },
});
