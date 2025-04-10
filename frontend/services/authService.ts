const config = useRuntimeConfig()

interface LoginCredentials {
    email: string;
    password: string;
}

interface AuthResponse {
    token: string;
    role: string;
    user: {
        id: string;
        image: string;
        name: string;
        last_name: string;
        email: string;
        role_id: string;
        created_at: string;
        updated_at: string;
        status: string;
        course_id: string;
        division_id: string;
    };
}

export function login(credentials: LoginCredentials): Promise<AuthResponse> {
    return fetch(`${config.apiBaseUrl}/api/login`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('accessToken')}`,
        },
        body: JSON.stringify(credentials),
    }).then((response) => {
        if (!response.ok) {
            throw new Error('Login failed');
        }
        return response.json();
    });
}

export function logout(): Promise<void> {
    return fetch(`${config.API_BASE_URL}/api/auth/logout`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${localStorage.getItem('accessToken')}`,
        },
    }).then((response) => {
        if (!response.ok) {
            throw new Error('Logout failed');
        }
        localStorage.removeItem('accessToken');
    });
}       