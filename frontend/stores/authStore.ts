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

// Interfaz para una asignación de curso y división
interface CourseDivision {
  course_id: number;
  course_name: string;
  division_id: number;
  division_name: string;
}

// Interfaz para el usuario según la estructura exacta que envía el backend
interface User {
  id: number;
  image: string | null;
  name: string;
  last_name: string;
  email: string;
  role_id: number;
  role_name?: string;
  created_at: string;
  updated_at: string;
  status: number | string;
  course_id: number | null;
  division_id: number | null;
  course_name: string | null;
  division_name: string | null;
  course_divisions?: CourseDivision[];
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
    },
    
    isTutor: (state): boolean => {
      return state.user?.role?.name === 'tutor';
    },
    
    isOrientador: (state): boolean => {
      return state.user?.role?.name === 'orientador';
    },
    
    // Verificar si puede ver respuestas de formularios
    canViewResponses: (state): boolean => {
      const roleName = state.user?.role?.name;
      return roleName === 'profesor' || roleName === 'orientador' || roleName === 'admin';
    },
    
    // Verificar si puede asignar formularios
    canAssignForms: (state): boolean => {
      const roleName = state.user?.role?.name;
      return roleName === 'profesor' || roleName === 'tutor' || roleName === 'admin';
    },
    
    // Verificar si puede ver análisis (sociograma, cesc, gráficas)
    canViewAnalysis: (state): boolean => {
      const roleName = state.user?.role?.name;
      return roleName === 'orientador' || roleName === 'admin';
    },
    
    userCourseName: (state): string | null => {
      return state.user?.course_name || null;
    },
    
    userDivisionName: (state): string | null => {
      return state.user?.division_name || null;
    },
    
    userFullCourseInfo: (state): string | null => {
      if (!state.user?.course_name && !state.user?.division_name) return null;
      
      if (state.user.course_name && state.user.division_name) {
        return `${state.user.course_name} ${state.user.division_name}`;
      }
      
      return state.user.course_name || state.user.division_name;
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
        const response = await $fetch<{ user: User }>('https://api.grupify.cat/api/user', {
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
          await $fetch('https://api.grupify.cat/api/logout', {
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
      
      // Verificación de rutas por rol
      if (route.includes('/alumne') && !this.isAlumno) return false;
      
      // Para las rutas de profesor, tutor y orientador
      if (route.includes('/professor')) {
        // Si no es profesor, tutor ni orientador, no tiene acceso
        const roleName = this.user?.role?.name;
        if (roleName !== 'profesor' && roleName !== 'tutor' && roleName !== 'orientador' && roleName !== 'admin') {
          return false;
        }
        
        // Los profesores no pueden acceder a análisis
        if (roleName === 'profesor' && 
            (route.includes('/sociogram') || route.includes('/cesc') || route.includes('/grafico'))) {
          return false;
        }
        
        // Los orientadores no pueden asignar formularios
        if (roleName === 'orientador' && route.includes('/formularis/assignar')) {
          return false;
        }
      }
      
      // Verificación para rutas de admin
      if (route.includes('/admin') && this.user?.role?.name !== 'admin') return false;
      
      return true;
    }
  },
});