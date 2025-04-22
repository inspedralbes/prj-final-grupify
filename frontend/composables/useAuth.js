import { login, logout } from "../services/authService";

export default function useAuth() {
    const HandleLogin = async (userData) => {
        try {
            if (!userData) {
                throw new Error("No user data provided");
            }
            
            // La función login ya maneja la conversión a JSON
            const data = await login(userData);
            
            // Almacenar el token en localStorage
            localStorage.setItem('token', data.token);
            
            return data;
        } catch (error) {
            console.error("Error during login:", error);
            throw error; // Re-lanzar el error para que el componente pueda manejarlo
        }
    }

    const HandleLogout = async () => {
        try {
            await logout(); // logout() ya maneja la eliminación del token
            return { success: true };
        } catch (error) {
            console.error("Error during logout:", error);
            throw error;
        }
    }

    return {
        HandleLogin,
        HandleLogout
    }
}