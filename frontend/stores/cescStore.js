import { defineStore } from "pinia";

export const useCescStore = defineStore("cesc", {
  state: () => ({
    responses: [],
    responsesByCourseDivision: [],
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
    async fetchResponses() {
      try {
        const response = await fetch("https://api.basebrutt.com/api/forms/all-responses-cesc");
        const data = await response.json();
        console.log("Data:", data);
        this.responsesByCourseDivision = data;
        this.setResponses(data);
        console.log("Responses loaded:", this.responses);
      } catch (error) {
        console.error("Error loading responses:", error);
      }
    },
    setResponses(data) {
      this.responses = data;
    },
    setResponsesByCourseDivision(data) {
      this.responsesByCourseDivision = data;
    },
    setCurrentCourseAndDivision(courseName, courseId, divisionName, divisionId) {
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
    getResponsesByCourseDivision(courseId, divisionId) {
      return this.responses.filter(
        (response) =>
          response.course_id === courseId && response.division_id === divisionId
      );
    }
  }
});