// src/stores/students.js
import { defineStore } from "pinia";

export const useStudentsStore = defineStore("students", {
  state: () => ({
    students: [], // Aquí guardamos el listado de estudiantes
    loading: false, // Puedes usarlo para mostrar una carga mientras se obtienen los datos
    error: null, // Para manejar posibles errores
  }),
  actions: {
    async fetchStudents() {
      this.loading = true; // Comienza la carga
      try {
        const response = await fetch("http://localhost:8000/api/get-students");

        if (!response.ok) {
          throw new Error(`Error: ${response.divisionText}`);
        }

        const data = await response.json();
        this.students = data; // Guarda los estudiantes en el estado
        // console.log('Datos recibidos de la API:', data);
      } catch (error) {
        this.error = error.message;
        console.error("Error fetching students:", error);
      } finally {
        this.loading = false; // Termina la carga
      }
    },
    getStudentById(id) {
      // console.log('Buscando estudiante con ID:', id, typeof id);
      const found = this.students.find(student => student.id === Number(id));
      // console.log('Estudiante encontrado:', found);
      return found;
    },
  },
  getters: {
    studentCount: state => {
      return state.students.length;
    },
  },
});
