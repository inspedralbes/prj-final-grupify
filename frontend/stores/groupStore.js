import { defineStore } from 'pinia';

export const useGroupStore = defineStore('groups', {
  state: () => ({
    groups: [],  // Para almacenar los grupos
  }),
  actions: {
    async fetchGroups() {
      try {
        const token = localStorage.getItem('auth_token');  // Obtener el token de autenticación
        const response = await fetch('http://localhost:8000/api/groups', {
          method: 'GET',
          headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
          },
        });
        if (response.ok) {
          this.groups = await response.json();
        } else {
          throw new Error("Error al obtener los grupos.");
        }
      } catch (error) {
        console.error(error.message);
      }
    },
    async createGroup(groupData, studentIds) {
      try {
        const response = await fetch("http://localhost:8000/api/groups", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            accept: "application/json",
          },
          body: JSON.stringify(groupData),
        });

        if (!response.ok) {
          throw new Error("Error al crear el grupo.");
        }

        const data = await response.json();

        await fetch(`http://localhost:8000/api/groups/${data.id}/addStudentsToGroup`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            accept: "application/json",
          },
          body: JSON.stringify({ student_ids: studentIds }),
        });

        // Refrescar la lista de grupos después de la creación
        await this.fetchGroups();

        return data;
      } catch (error) {
        console.error('Error fetching groups:', error);
      }
    },
  },
});