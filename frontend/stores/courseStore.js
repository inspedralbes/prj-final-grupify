import { defineStore } from 'pinia';
export const useCoursesStore = defineStore('courses', {
  state: () => ({
    courses: [],        // Array para almacenar cursos
    loading: false,     // Para controlar el estado de carga
    error: null,        // Para gestionar el error de la llamada
    onlinecourses: new Set(), // Set para manejar los ID de estudiantes online
  }),
  actions: {
    async fetchCourses(force = false) {
      if (!force && this.courses.length > 0) {
        return; // Si no se fuerza la recarga y ya hay cursos cargados, evita la nueva petición
      }
      this.loading = true;
      this.error = null;
      try {
        // Realizamos el fetch para traer los cursos
        const response = await fetch('http://localhost:8000/api/courses-with-divisions');
        if (!response.ok) {
          throw new Error(`Error al obtener cursos: ${response.statusText}`);
        }
        // Parseamos la respuesta JSON
        const data = await response.json();
        // Validamos que la respuesta sea un array
        if (!Array.isArray(data)) {
          throw new Error("La respuesta de la API no tiene el formato esperado.");
        }
        // Asignamos los datos al estado 'courses' con validación de los campos
        this.courses = data.map((course) => ({
          ...course,
          active: course.active ?? true, // Aseguramos que cada curso tenga el atributo 'active' con valor por defecto 'true'
        }));
      } catch (error) {
        // En caso de error, actualizamos el estado de 'error'
        this.error = `Error al cargar los cursos: ${error.message}`;
        console.error('Error al cargar los cursos:', error);
      } finally {
        // Finalizamos el proceso de carga
        this.loading = false;
      }
    },
    // Si necesitas más acciones, las puedes agregar aquí
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
      return this.courses; // Retorna todos los cursos
    },
    isLoading() {
      return this.loading; // Getter para obtener el estado de carga
    },
    hasError() {
      return this.error !== null; // Getter para verificar si hubo un error
    },
  },
});