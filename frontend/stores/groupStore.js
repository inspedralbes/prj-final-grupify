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
        this.groups.forEach(async group => {
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
        });
      } catch (error) {
        console.error("Error fetching groups:", error);
      }
    },
  },
});
