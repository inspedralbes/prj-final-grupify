<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgb(0, 173, 238);
            --primary-hover: rgb(0, 153, 218);
            --active-background: #0000FF; /* Azul puro */
            --background-color: #f2f2f2;
            --navbar-background: var(--primary-color);
            --link-active-bg: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }

        /* Estilo para el encabezado del dashboard y el navbar de las secciones */
        .dashboard-header,
        .navbar {
            background-color: var(--navbar-background);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .dashboard-title,
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }

        /* Botón de cierre de sesión (estilo simplificado) */
        .logout-btn {
            color: white;
            text-decoration: none;
            transition: color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0; /* Eliminamos el padding */
            background: none; /* Eliminamos el fondo */
            border: none; /* Eliminamos el borde */
        }

        .logout-btn:hover {
            color: var(--primary-hover); /* Cambiamos el color al hacer hover */
        }

        /* Enlaces de navegación en el navbar */
        .nav-links {
            display: flex;
            gap: 0.8rem; /* Espacio entre secciones */
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1rem;
            padding: 0.5rem 0.8rem;
            border-radius: 0.25rem;
            transition: background-color 0.2s ease, color 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            background: none; /* Sin fondo por defecto */
        }

        .nav-links a:hover {
            background-color: var(--link-active-bg); /* Efecto hover */
            color: var(--primary-hover);
        }

        /* Estilo para el enlace activo */
        .nav-links a.active {
            background-color: var(--active-background); /* Fondo azul puro */
            color: white; /* Texto blanco */
        }

        .nav-links i {
            font-size: 1rem; /* Tamaño del ícono */
        }

        /* Estilo para el contenido principal */
        .content {
            padding: 2rem;
            min-height: calc(100vh - 70px); /* Altura del header o navbar */
            background-color: var(--background-color);
        }
    </style>
</head>
<body>
    <!-- Navbar solo para secciones (no para el dashboard) -->
    @if (!request()->routeIs('dashboard'))
    <nav class="navbar">
        <!-- Logo y título con ícono de inicio -->
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            <i class="fas fa-home"></i> Panell Administrador
        </a>
        <!-- Enlaces de navegación -->
        <div class="nav-links">
            <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="fas fa-user-tag"></i> Rols
            </a>
            <a href="{{ route('courses.index') }}" class="{{ request()->routeIs('courses.*') ? 'active' : '' }}">
                <i class="fas fa-graduation-cap"></i> Cursos
            </a>
            <a href="{{ route('subjects.index') }}" class="{{ request()->routeIs('subjects.*') ? 'active' : '' }}">
                <i class="fas fa-book"></i> Assignatures
            </a>
            <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Usuaris
            </a>
            <a href="{{ route('groups.index') }}" class="{{ request()->routeIs('groups.*') ? 'active' : '' }}">
                <i class="fas fa-layer-group"></i> Grups
            </a>
            <a href="{{ route('divisions.index') }}" class="{{ request()->routeIs('divisions.*') ? 'active' : '' }}">
                <i class="fas fa-sitemap"></i> Divisions
            </a>
            <a href="{{ route('forms.index') }}" class="{{ request()->routeIs('forms.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i> Formularis
            </a>
            <a href="{{ route('questions.index') }}" class="{{ request()->routeIs('questions.*') ? 'active' : '' }}">
                <i class="fas fa-question-circle"></i> Preguntes
            </a>
        </div>
        <!-- Botón de cierre de sesión -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Tancar Sessió
            </button>
        </form>
    </nav>
    @endif

    <!-- Encabezado específico para el dashboard -->
    @if (request()->routeIs('dashboard'))
    <header class="dashboard-header">
        <div class="dashboard-title">Panell d'administrador</div>
        <!-- Botón de cierre de sesión -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Tancar Sessió
            </button>
        </form>
    </header>
    @endif

    <!-- Contenido principal -->
    <div class="content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>