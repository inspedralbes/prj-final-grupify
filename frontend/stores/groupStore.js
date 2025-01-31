import { defineStore } from "pinia";
import { useAuthStore } from "~/stores/auth";

export const useGroupStore = defineStore("groups", {
  state: () => ({
    groups: [],
  }),
  actions: {
    async fetchGroups() {
      try {
        const authStore = useAuthStore();
        if (!authStore.token) throw new Error("No hay token disponible");

        const response = await $fetch("http://localhost:8000/api/groups", {
          headers: {
            Authorization: `Bearer ${authStore.token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
        });

        this.groups = response;

        // Cargar miembros
        const memberPromises = this.groups.map(async (group) => {
          const members = await $fetch(`/api/groups/${group.id}/members`, {
            headers: {
              Authorization: `Bearer ${authStore.token}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          });
          group.members = members;
        });

        await Promise.all(memberPromises);

      } catch (error) {
        console.error("Error fetching groups:", error);
        throw error;
      }
    },

    async addStudentsToGroup(groupId, studentIds) {
      try {
        const token = localStorage.getItem("auth_token");
        const response = await fetch(
          `http://localhost:8000/api/groups/${groupId}/addStudentsToGroup`,
          {
            method: "POST",
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
            body: JSON.stringify({ student_ids: studentIds }),
          }
        );

        if (!response.ok) {
          throw new Error("Error adding students to group");
        }

        const data = await response.json();
        return data;
      } catch (error) {
        console.error("Error adding students to group:", error);
        throw error;
      }
    },

    async removeStudentFromGroup(groupId, studentId) {
      try {
        const token = localStorage.getItem("auth_token");
        const response = await fetch(
          `http://localhost:8000/api/groups/${groupId}/removeStudentFromGroup`,
          {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            },
            body: JSON.stringify({ student_id: studentId }),
          }
        );

        if (!response.ok) {
          throw new Error("Error removing student from group");
        }

        const data = await response.json();

        // Actualizar el número de estudiantes en el grupo en el store
        const group = this.groups.find(group => group.id === groupId);
        if (group) {
          group.number_of_students = data.number_of_students;
        }

      } catch (error) {
        console.error("Error removing student from group:", error);
        throw error;
      }
    },
    async deleteGroup(groupId) {
      try {
        const token = localStorage.getItem("auth_token");
        const response = await fetch(
          `http://localhost:8000/api/groups/${groupId}`,
          {
            method: "DELETE",
            headers: {
              Authorization: `Bearer ${token}`,
              "Content-Type": "application/json",
              Accept: "application/json",
            },
          }
        );

        if (!response.ok) {
          throw new Error("Error deleting group");
        }

        // Actualizar la lista de grupos después de eliminar uno
      } catch (error) {
        console.error("Error deleting group:", error);
        throw error;
      }
    },
    async createGroup(groupData) {
      try {
        const token = localStorage.getItem("auth_token");
        const response = await fetch("http://localhost:8000/api/groups", {
          method: "POST",
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(groupData),
        });

        if (!response.ok) {
          throw new Error("Error creating group");
        }

        const data = await response.json();
        return data;
      } catch (error) {
        console.error("Error creating group:", error);
        throw error;
      }
    },
  },
});
