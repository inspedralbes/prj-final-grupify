const config = useRuntimeConfig().public;

interface LoginCredentials {
    email: string;
    password: string;
}

// Definición de roles para mayor tipado
interface Role {
    id: number;
    name: string;
    created_at: string;
    updated_at: string;
}

// Interfaz alineada exactamente con la respuesta del backend
interface AuthResponse {
    token: string;
    role: string;
    user: {
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
        forms: any[];
        subjects: any[];
        role: Role;
    };
}

export function login(credentials: LoginCredentials): Promise<AuthResponse> {
    return fetch(`${config.apiBaseUrl}/api/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(credentials),
    }).then(async (response) => {
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Login failed');
        }
        
        return await response.json();
    });
}

export function logout(): Promise<void> {
    const token = localStorage.getItem('token');
    
    return fetch(`${config.apiBaseUrl}/api/logout`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`,
        },
    }).then((response) => {
        if (!response.ok) {
            throw new Error('Logout failed');
        }
        localStorage.removeItem('token');
    });
}