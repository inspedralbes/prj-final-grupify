import { defineStore } from "pinia";

export const useSociogramStore = defineStore("sociogram", {
  state: () => ({
    responses: [], // Aquí almacenaremos las respuestas del sociograma
    currentCourse: {
      courseName: null,
      courseId: null,
    },
    currentDivision: {
      divisionName: null,
      divisionId: null,
    },
  }),
  actions: {
    setResponses(data) {
      this.responses = data;
    },
    setCurrentCourseAndDivision(courseName,courseId,divisionName,divisionId) {
      this.currentCourse.courseName = courseName;
      this.currentCourse.courseId = courseId;
      this.currentDivision.divisionName = divisionName;
      this.currentDivisionId = divisionId;
    },
    clearResponses() {
      this.responses = [];
      this.currentCourseId = null;
      this.currentDivisionId = null;
    },
  }
});