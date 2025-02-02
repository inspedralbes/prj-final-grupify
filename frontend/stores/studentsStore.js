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
          status: student.status ?? 1,
        }));

        
      } catch (error) {
        this.error = `Error al cargar estudiantes: ${error.message}`;
        console.error("Error fetching students:", error);
      } finally {
        this.loading = false;
      }
    },
    updateStudent(updatedStudent) {
      const studentIndex = this.students.findIndex(
        student => student.id === updatedStudent.id
      );
      if (studentIndex !== -1) {
        this.students.splice(studentIndex, 1, updatedStudent); // Reactividad
      } else {
        console.warn(`Estudiante con ID ${updatedStudent.id} no encontrado.`);
      }
    },

    toggleActive(studentId) {
      const student = this.students.find(s => s.id === studentId);
      if (student) {
        student.status = student.status === 1 ? 0 : 1;
        student.active = !student.active;
      } else {
        console.warn(`Estudiante con ID ${studentId} no encontrado.`);
      }
    },

    // Elimina un estudiante de la lista
    removeStudent(studentId) {
      this.students = this.students.filter(student => student.id !== studentId);
    },

    // Busca un estudiante por ID
    getStudentById(id) {
      return this.students.find(student => student.id === Number(id));
    },

    // Marca a un estudiante como conectado
    setUserOnline(userId) {
      this.onlineStudents.add(userId);
    },

    // Marca a un estudiante como desconectado
    setUserOffline(userId) {
      this.onlineStudents.delete(userId);
    },
  },
  getters: {
    // Verifica si un estudiante está en línea
    isStudentOnline: state => studentId => {
      return state.onlineStudents.has(studentId);
    },
  },
  getStudentsByCourseAndDivision(courseName, divisionName) {
    return this.students.filter(
      student =>
        student.course === courseName && student.division === divisionName
    );
  },
});
