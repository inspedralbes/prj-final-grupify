import { defineStore } from "pinia";

export const useKnowledgeStore = defineStore("knowledge", {
  state: () => ({
    documents: [],
  }),

  actions: {
    addDocument(content) {
      const id = Date.now();
      this.documents.push({
        id,
        content,
        timestamp: new Date().toISOString(),
      });
      this.saveToLocalStorage();
      return id;
    },

    deleteDocument(id) {
      this.documents = this.documents.filter(doc => doc.id !== id);
      this.saveToLocalStorage();
    },

    getDocumentById(id) {
      return this.documents.find(doc => doc.id === id);
    },

    loadFromLocalStorage() {
      const saved = localStorage.getItem("teachingAssistantKnowledge");
      if (saved) {
        this.documents = JSON.parse(saved);
      }
    },

    saveToLocalStorage() {
      localStorage.setItem(
        "teachingAssistantKnowledge",
        JSON.stringify(this.documents)
      );
    },

    getAllDocuments() {
      return this.documents;
    },
  },
});
