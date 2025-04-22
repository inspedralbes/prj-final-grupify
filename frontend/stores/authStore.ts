import { defineStore } from "pinia";

// Interfaces para los datos anidados
interface Role {
  id: number;
  name: string;
}

interface Form {
  id: number;
}

interface Subject {
  id: number;
}

// Interfaz para el usuario según la estructura exacta que envía el backend
interface User {
  id: number;
  image: string | null;
  name: string;
  last_name: string;
  email: string;
  role_id: number;
  created_at: string;
  updated_at: string;
  status: number | string;
  course_id: number | null;
  division_id: number | null;
  forms: Form[];
  subjects: Subject[];
  role: Role;
}

// Interfaz para el estado
interface AuthState {
  token: string | null;
  user: User | null;
  isAuthenticated: boolean;
}

export const useAuthStore = defineStore("auth", {
  state: (): AuthState => {
    const token = localStorage.getItem("token") || null;

    return {
      token: token,
      user: JSON.parse(localStorage.getItem("user") || "null"),
      isAuthenticated: !!token,
    };
  },

  getters: {
    // Simplificado para usar directamente role.name
    userRole: (state): string | null => {
      if (!state.user?.role) return null;
      return state.user.role.name;
    },
    
    isAlumno: (state): boolean => {
      return state.user?.role?.name === 'alumno';
    },
    
    isProfesor: (state): boolean => {
      return state.user?.role?.name === 'profesor';
    }
  },

  actions: {
    initialize(): void {
      if (this.token && (!this.user || !this.user.role)) {
        this.checkAuth();
      }
    },

    async checkAuth(): Promise<void> {
      try {
        const response = await $fetch<{ user: User }>('http://localhost:8000/api/user', {
          headers: { Authorization: `Bearer ${this.token}` }
        });

        if (response.user) {
          this.user = response.user;
          localStorage.setItem("user", JSON.stringify(response.user));
        }
      } catch (error) {
        this.logout();
      }
    },

    // Simplificado para manejar exactamente el formato de respuesta del backend
    setAuth(token: string, user: User, role: string): void {
      this.token = token;
      this.user = user;
      this.isAuthenticated = true;
      
      localStorage.setItem("token", token);
      localStorage.setItem("user", JSON.stringify(user));
    },

    async logout(): Promise<void> {
      try {
        if (this.token) {
          await $fetch('http://localhost:8000/api/logout', {
            method: 'POST',
            headers: {
              Authorization: `Bearer ${this.token}`,
              Accept: 'application/json',
            },
          });
        }
      } catch (error) {
        console.error("Error en logout:", error);
      } finally {
        this.token = null;
        this.user = null;
        this.isAuthenticated = false;
        
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        
        navigateTo('/login');
      }
    },

    checkRouteAccess(route: string): boolean {
      if (!this.isAuthenticated) return false;
      if (route.includes('/alumne') && !this.isAlumno) return false;
      if (route.includes('/professor') && !this.isProfesor) return false;
      return true;
    }
  },
});