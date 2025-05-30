import { defineStore } from "pinia";
import { useAuthStore } from "~/stores/authStore";
import { useBitacoraStore } from "~/stores/bitacoraStore";

export const useGroupStore = defineStore("groups", {
  state: () => ({
    groups: [],
  }),
  actions:
  {
    async fetchGroups(filters = {}) {
      try {
        const authStore = useAuthStore();
        const token = authStore.token;

        // Construir la URL con parámetros de filtro
        let url = "https://api.grupify.cat/api/groups";
        const params = new URLSearchParams();

        // Añadir filtros si están definidos
        if (filters.course_id) {
          params.append('course_id', filters.course_id);
        }

        if (filters.division_id) {
          params.append('division_id', filters.division_id);
        }

        // Añadir parámetros a la URL si existen
        if (params.toString()) {
          url += `?${params.toString()}`;
        }

        console.log("Fetching groups from URL:", url);

        const response = await $fetch(url, {
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          }
        });

        console.log("Groups received from backend:", response.length);

        this.groups = response.map(group => ({
          ...group,
          members: (group.users || []).filter(user => {
            // Aceptar cualquier variación del rol de alumno
            const userRole = user.role?.name || user.role;
            return userRole === "alumno" || userRole === "alumne";
          }),
          number_of_students: (group.users || []).filter(user => {
            const userRole = user.role?.name || user.role;
            return userRole === "alumno" || userRole === "alumne";
          }).length
        }));

        console.log("Processed groups:", this.groups.length);

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
          `https://api.grupify.cat/api/groups/${groupId}/addStudentsToGroup`,
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

        // Verificar si la bitácora existe
        let bitacoraId = null;
        try {
          const bitacoraResponse = await fetch(`https://api.grupify.cat/api/bitacoras/${groupId}`, {
            headers: {
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            }
          });

          if (bitacoraResponse.ok) {
            const bitacora = await bitacoraResponse.json();
            bitacoraId = bitacora.id;
          }
        } catch (error) {
          console.warn("No se encontró la bitácora, continuando sin ella:", error);
        }

        const response = await fetch(
          `https://api.grupify.cat/api/groups/${groupId}/removeStudentFromGroup`,
          {
            method: "DELETE",
            headers: {
              "Content-Type": "application/json",
              Authorization: `Bearer ${token}`,
              Accept: "application/json",
            },
            body: JSON.stringify({
              student_id: studentId,
              bitacora_id: bitacoraId // Enviar null si no existe la bitácora
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
          `https://api.grupify.cat/api/groups/${groupId}`,
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

        // No necesitamos incluir manualmente creator_id, el backend lo asignará
        // automáticamente basado en el usuario autenticado

        console.log("Creating group with data:", groupData);

        const response = await fetch("http://api.grupify.cat/api/groups", {
          method: "POST",
          headers: {
            Authorization: `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify(groupData),
        });

        if (!response.ok) {
          const errorData = await response.json();
          console.error("Server response error:", errorData);
          throw new Error(`Error creando el grupo: ${errorData.message || response.statusText}`);
        }

        const newGroup = await response.json();
        console.log("Group created successfully:", newGroup);

        await this.fetchGroups();

        return newGroup;
      } catch (error) {
        console.error("Error creating group:", error);
        throw error;
      }
    }
  },
});