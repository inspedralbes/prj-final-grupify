import { defineStore } from "pinia";
import { useAuthStore } from "~/stores/auth";
import { useBitacoraStore } from "~/stores/bitacoraStore";

export const useGroupStore = defineStore("groups", {
  state: () => ({
    groups: [],
  }),
  actions: {
    async fetchGroups() {
      try {
        const authStore = useAuthStore();
        const token = authStore.token;

        const response = await $fetch("http://localhost:8000/api/groups", {
          headers: {
            Authorization: `Bearer ${token}`, 
            "Content-Type": "application/json",
            Accept: "application/json",
          }
        });

        this.groups = response.map(group => ({
          ...group,
          members: group.users || [], 
          number_of_students: (group.users || []).length 
        }));

      } catch (error) {
        console.error("Error fetching groups:", error);
        throw error;
      }
    },

    async addStudentsToGroup(groupId, studentIds) {
      try {
        const authStore = useAuthStore();
        const token = authStore.token;
        const bitacoraStore = useBitacoraStore();

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

        if (!response.ok) throw new Error("Error añadiendo estudiantes");
        const data = await response.json();

        await this.fetchGroups(); 

        await bitacoraStore.fetchBitacora(groupId);
        await bitacoraStore.fetchNotes(groupId);

        return data;
      } catch (error) {
        console.error("Error:", error);
        throw error;
      }
    },

    async removeStudentFromGroup(groupId, studentId) {
      try {
        const token = useAuthStore().token;
        const bitacoraStore = useBitacoraStore();

        const bitacoraResponse = await fetch(`http://localhost:8000/api/bitacoras/${groupId}`, {
          headers: {
            Authorization: `Bearer ${token}`,
            Accept: "application/json",
          }
        });
        
        if (!bitacoraResponse.ok) {
          throw new Error("Error fetching bitacora");
        }
        
        const bitacora = await bitacoraResponse.json();
        const bitacoraId = bitacora.id;

        const response = await fetch(
          `http://localhost:8000/api/groups/${groupId}/removeStudentFromGroup`,
          {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            },
            body: JSON.stringify({ 
              student_id: studentId,
              bitacora_id: bitacoraId 
            }),
          }
        );

        if (!response.ok) {
          throw new Error("Error removing student from group");
        }

        const data = await response.json();

        await this.fetchGroups();
        await bitacoraStore.fetchBitacora(groupId);
        await bitacoraStore.fetchNotes(groupId);

        return data;
      } catch (error) {
        console.error("Error removing student from group:", error);
        throw error;
      }
    },

    async deleteGroup(groupId) {
      try {
        const authStore = useAuthStore();
        const token = authStore.token;

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

        if (!response.ok) throw new Error("Error eliminando el grupo");
        
        this.groups = this.groups.filter(group => group.id !== groupId);
        
        return await response.json();
      } catch (error) {
        console.error("Error:", error);
        throw error;
      }
    },

    async createGroup(groupData) {
      try {
        const authStore = useAuthStore();
        const token = authStore.token;

        const response = await fetch("http://localhost:8000/api/groups", {
          method: "POST",
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(groupData),
        });

        if (!response.ok) throw new Error("Error creando el grupo");
        
        const newGroup = await response.json();

        await this.fetchGroups();
        
        return newGroup;
      } catch (error) {
        console.error("Error:", error);
        throw error;
      }
    }
  },
});