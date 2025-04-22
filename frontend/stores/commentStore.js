import { defineStore } from "pinia";
import { useAuthStore } from "~/stores/authStore";

export const useCommentStore = defineStore("comment", {
  state: () => ({
    comments: [],
  }),
  actions: {
    async fetchComments(idGroup) {
      try {
          const authStore = useAuthStore();
          if (!authStore.token) {
              throw new Error("No authentication token available");
          }
          
          const response = await fetch(`http://localhost:8000/api/groups/${idGroup}/comments`, {
              method: "GET",
              headers: {
                  Authorization: `Bearer ${authStore.token}`,
                  "Content-Type": "application/json",
                  Accept: "application/json",
              },
          });
  
          if (!response.ok) {
              const errorData = await response.json();
              throw new Error(errorData.message || "Error fetching comments");
          }
          
          const data = await response.json();
          this.comments = data.comments;
  
      } catch (error) {
          console.error("Error fetching comments:", error);
          this.comments = []; // Set empty array on error
          throw error;
      }
  },

    async addCommentToGroup(idGroup, commentData) {
      try {
        const authStore = useAuthStore();
        const response = await fetch(
          `http://localhost:8000/api/groups/${idGroup}/comments`,
          {
            method: "POST",
            headers: {
              Authorization: `Bearer ${authStore.token}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
            body: JSON.stringify(commentData),
          }
        );

        if (!response.ok) {
          const errorData = await response.json();
          throw new Error(errorData.message || "Error adding comment");
        }

        const newComment = await response.json();
        this.comments.push(newComment.comment);
        return newComment;

      } catch (error) {
        console.error("Error adding comment:", error);
        throw error;
      }
    },

    async deleteCommentFromGroup(idGroup, commentId) {
      try {
        const authStore = useAuthStore();
        const response = await fetch(
          `http://localhost:8000/api/groups/${idGroup}/comments/${commentId}`,
          {
            method: "DELETE",
            headers: {
              Authorization: `Bearer ${authStore.token}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );

        if (!response.ok) throw new Error("Error deleting comment from group");

        this.comments = this.comments.filter(comment => comment.id !== commentId);
      } catch (error) {
        console.error("Error deleting comment from group:", error);
        throw error;
      }
    },
  },
});