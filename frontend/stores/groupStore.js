import { defineStore } from "pinia";

export const useGroupStore = defineStore("groups", {
  state: () => ({
    groups: [], // Para almacenar los grupos
  }),
  actions: {
    async fetchGroups() {
      try {
        const token = localStorage.getItem("auth_token"); // Obtener el token de autenticación
        const response = await fetch("http://localhost:8000/api/groups", {
          method: "GET",
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          },
        });

        if (!response.ok) {
          throw new Error("Error fetching groups");
        }

        const data = await response.json();
        this.groups = data; // Asignar los grupos a la variable del estado

        // Para cada grupo, obtener sus integrantes
        for (const group of this.groups) {
          const membersResponse = await fetch(
            `http://localhost:8000/api/groups/${group.id}/members`,
            {
              method: "GET",
              headers: {
                Authorization: `Bearer ${token}`,
                Accept: "application/json",
              },
            }
          );

          if (membersResponse.ok) {
            const membersData = await membersResponse.json();
            group.members = membersData; // Asignar los miembros del grupo
          }
        }
      } catch (error) {
        console.error("Error fetching groups:", error);
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

        // Actualizar la lista de miembros del grupo
        await this.fetchGroups();
      } catch (error) {
        console.error("Error removing student from group:", error);
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
              Accept: "application/json",
            },
          }
        );

        if (!response.ok) {
          throw new Error("Error deleting group");
        }

        // Actualizar la lista de grupos después de eliminar uno
        await this.fetchGroups();
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
