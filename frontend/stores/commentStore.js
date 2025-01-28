// store/commentStore.js
import { defineStore } from "pinia";

export const useCommentStore = defineStore("comments", {
  state: () => ({
    comments: [], // Almacenamos los comentarios de un grupo
  }),
  actions: {
    // Obtener los comentarios de un grupo
    async fetchComments(idGroup) {
      try {
        const token = localStorage.getItem("auth_token");
        const response = await fetch(`http://localhost:8000/api/groups/${idGroup}/comments`, {
          method: "GET",
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        });

        if (!response.ok) {
          throw new Error("Error fetching comments");
        }

        const data = await response.json();
        this.comments = data.comments;

      } catch (error) {
        console.error("Error fetching comments:", error);
        throw error;
      }
    },

    // Crear un nuevo comentario para un grupo
    async addCommentToGroup(idGroup, commentData) {
      try {
        const token = localStorage.getItem("auth_token");
        const response = await fetch(
          `http://localhost:8000/api/groups/${idGroup}/comments`,
          {
            method: "POST",
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
            body: JSON.stringify({ comment: commentData }),
          }
        );

        if (!response.ok) {
          throw new Error("Error adding comment");
        }

        const newComment = await response.json();
        this.comments.push(newComment); // Agregamos el comentario a la lista
        return newComment;

      } catch (error) {
        console.error("Error adding comment:", error);
        throw error;
      }
    },

    // Eliminar un comentario de un grupo
    async deleteComment(idGroup, commentId) {
      try {
        const token = localStorage.getItem("auth_token");
        const response = await fetch(
          `http://localhost:8000/api/groups/${idGroup}/comments/${commentId}`,
          {
            method: "DELETE",
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            },
          }
        );

        if (!response.ok) {
          throw new Error("Error deleting comment");
        }

        // Eliminar el comentario del estado local
        this.comments = this.comments.filter(comment => comment.id !== commentId);
      } catch (error) {
        console.error("Error deleting comment:", error);
        throw error;
      }
    },
  },
});
