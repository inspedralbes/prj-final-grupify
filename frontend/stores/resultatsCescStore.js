import { defineStore } from "pinia";
import { useStudentsStore } from "~/stores/studentsStore";

const studentsStore = useStudentsStore();

export const useResultatCescStore = defineStore("resultatCesc", {
  state: () => ({
    relationships: [],
    results: [],
    filteredResults: [],
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
    // Fetch all CESC relationships
    async fetchRelationships() {
      try {
        const response = await fetch("http://localhost:8000/api/cesc-relationships/cesc-relationships");
        const data = await response.json();
        this.relationships = data;
        console.log("Relationships loaded:", this.relationships);
      } catch (error) {
        console.error("Error loading relationships:", error);
      }
    },

    // Fetch all CESC results
    async fetchResults() {
      try {
        const response = await fetch("http://localhost:8000/api/cesc-relationships/cesc-results");
        const data = await response.json();
        this.results = data;
        console.log("Results loaded:", this.results);
      } catch (error) {
        console.error("Error loading results:", error);
      }
    },

    // Set current course and division
    setCurrentCourseAndDivision(courseName, courseId, divisionName, divisionId) {
      this.currentCourse.courseName = courseName;
      this.currentCourse.courseId = courseId;
      this.currentDivision.divisionName = divisionName;
      this.currentDivision.divisionId = divisionId;
      this.filterResultsByCourseDivision();
    },

    // Filter results by current course and division
    filterResultsByCourseDivision() {
      if (!this.currentCourse.courseId || !this.currentDivision.divisionId) {
        this.filteredResults = [];
        return;
      }

      // First, get all peer_ids from the current course and division
      const peersInGroup = new Set();
      
      // Use relationships to find peers in the current course/division
      this.relationships.forEach(relationship => {
        // Assuming relationship has course and division information through user
        if (relationship.user?.courses?.some(course => course.id === this.currentCourse.courseId) &&
            relationship.user?.divisions?.some(division => division.id === this.currentDivision.divisionId)) {
          peersInGroup.add(relationship.peer_id);
        }
      });

      // Filter results to only include peers from the current group
      this.filteredResults = this.results.filter(result => peersInGroup.has(result.peer_id));

      // Group results by peer_id to show all tags for each peer
      this.filteredResults = Array.from(peersInGroup).map(peerId => {
        const peerResults = this.results.filter(r => r.peer_id === peerId);
        const peer = this.relationships.find(r => r.peer_id === peerId)?.peer;
        
        return {
          peer_id: peerId,
          peer_name: peer?.name,
          peer_last_name: peer?.last_name,
          tags: peerResults.map(r => ({
            tag_id: r.tag_id,
            tag_name: r.tag?.name,
            vote_count: r.vote_count
          }))
        };
      });
    },

    // Clear all state
    clearResults() {
      this.relationships = [];
      this.results = [];
      this.filteredResults = [];
      this.currentCourse = {
        courseName: null,
        courseId: null,
      };
      this.currentDivision = {
        divisionName: null,
        divisionId: null,
      };
    },

    // Initialize store by fetching all necessary data
    async initialize() {
      await Promise.all([
        this.fetchRelationships(),
        this.fetchResults()
      ]);
    }
  },

  getters: {
    // Get results grouped by tag type
    resultsByTag: (state) => {
      const groupedResults = {};
      state.filteredResults.forEach(peer => {
        peer.tags.forEach(tag => {
          if (!groupedResults[tag.tag_name]) {
            groupedResults[tag.tag_name] = [];
          }
          groupedResults[tag.tag_name].push({
            peer_id: peer.peer_id,
            peer_name: peer.peer_name,
            peer_last_name: peer.peer_last_name,
            vote_count: tag.vote_count
          });
        });
      });
      return groupedResults;
    },

    // Get total votes for each tag type
    tagTotals: (state) => {
      const totals = {};
      state.filteredResults.forEach(peer => {
        peer.tags.forEach(tag => {
          if (!totals[tag.tag_name]) {
            totals[tag.tag_name] = 0;
          }
          totals[tag.tag_name] += tag.vote_count;
        });
      });
      return totals;
    }
  }
});