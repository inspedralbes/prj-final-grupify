import { defineStore } from "pinia";

export const useStudentsStore = defineStore("students", {
  state: () => ({
    students: [],
    loading: false,
    error: null,
    onlineStudents: new Set(), // IDs de estudiantes conectados
  }),
  actions: {
    async fetchStudents(force = false) {
      if (!force && this.students.length > 0) {
        return; // Evitar recarga innecesaria
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await fetch("http://localhost:8000/api/get-students");

        if (!response.ok) {
          throw new Error(`Error: ${response.statusText}`);
        }

        const data = await response.json();

        if (!Array.isArray(data)) {
          throw new Error(
            "La respuesta de la API no tiene el formato esperado."
          );
        }

        this.students = data.map(student => ({
          ...student,
          active: student.active ?? true, // Si no viene definido
        }));
      } catch (error) {
        this.error = `Error al cargar estudiantes: ${error.message}`;
        console.error("Error fetching students:", error);
      } finally {
        this.loading = false; // Termina la carga
      }
    },

    updateStudent(updatedStudent) {
      const studentIndex = this.students.findIndex(
        student => student.id === updatedStudent.id
      );
      if (studentIndex !== -1) {
        this.students.splice(studentIndex, 1, updatedStudent); // Garantiza reactividad
      } else {
        console.warn(`Estudiante con ID ${updatedStudent.id} no encontrado.`);
      }
    },

    toggleActive(studentId) {
      const student = this.students.find(s => s.id === studentId);
      if (student) {
        student.active = !student.active; // Cambia el estado
      } else {
        console.warn(`Estudiante con ID ${studentId} no encontrado.`);
      }
    },

    removeStudent(studentId) {
      this.students = this.students.filter(student => student.id !== studentId);
    },

    getStudentById(id) {
      // console.log('Buscando estudiante con ID:', id, typeof id);
      const found = this.students.find(student => student.id === Number(id));
      // console.log('Estudiante encontrado:', found);
      return found;
    },
    setUserOnline(userId) {
      this.onlineStudents.add(userId);
    },
    setUserOffline(userId) {
      this.onlineStudents.delete(userId);
    },
  },
  getters: {
    isStudentOnline: (state) => (studentId) => {
      return state.onlineStudents.has(studentId);
    },
  },
});
