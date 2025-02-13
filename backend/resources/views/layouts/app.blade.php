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
            --active-background: #0000FF; 
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
                color: white;
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
            display: flex;
            align-items: center;
            gap: 0.3rem;
            background: none;
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

        /* Estilos para el menú desplegable */
        .dropdown-menu {
            background-color: var(--navbar-background);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dropdown-item {
            color: white;
            padding: 0.5rem 1rem;
        }

        .dropdown-item:hover {
            background-color: var(--link-active-bg);
            color: var(--primary-hover);
        }

        .dropdown-item.active {
            background-color: var(--active-background);
            color: white;
        }

        .nav-link.dropdown-toggle {
            padding: 0.5rem 0.8rem;
            border-radius: 0.25rem;
            color: white;
        }

        .nav-link.dropdown-toggle.active {
            background-color: var(--active-background);
        }

        .nav-link.dropdown-toggle:hover {
            background-color: var(--link-active-bg);
            color: var(--primary-hover);
        }
                
        .navbar-brand:hover {
            color: var(--primary-hover);
        }
                
    </style>
</head>
<body>
    <!-- Navbar solo para secciones (no para el dashboard) -->
    @if (!request()->routeIs('dashboard'))
    <nav class="navbar">
        <!-- Logo y título con ícono de inicio -->
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            <i class="fas fa-home"></i> Panell d'Administrador
        </a>
        <!-- Enlaces de navegación -->
        <div class="nav-links">
    <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
        <i class="fas fa-user-tag"></i> Rols
    </a>
    <!-- Menú desplegable de Classes -->
    <div class="dropdown d-inline-block">
        <a class="nav-link dropdown-toggle text-white {{ request()->routeIs(['courses.*', 'divisions.*']) ? 'active' : '' }}" 
           href="#" 
           role="button" 
           data-bs-toggle="dropdown" 
           aria-expanded="false">
            <i class="fas fa-chalkboard"></i> Classes
        </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item {{ request()->routeIs('courses.*') ? 'active' : '' }}" 
                   href="{{ route('courses.index') }}">
                    <i class="fas fa-graduation-cap"></i> Cursos
                </a>
            </li>
            <li>
                <a class="dropdown-item {{ request()->routeIs('divisions.*') ? 'active' : '' }}" 
                   href="{{ route('divisions.index') }}">
                    <i class="fas fa-sitemap"></i> Divisions
                </a>
            </li>
        </ul>
    </div>
    <a href="{{ route('subjects.index') }}" class="{{ request()->routeIs('subjects.*') ? 'active' : '' }}">
        <i class="fas fa-book"></i> Assignatures
    </a>
    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
        <i class="fas fa-users"></i> Usuaris
    </a>
    <a href="{{ route('groups.index') }}" class="{{ request()->routeIs('groups.*') ? 'active' : '' }}">
        <i class="fas fa-layer-group"></i> Grups
    </a>
    <!-- Menú desplegable de Formularis -->
    <div class="dropdown d-inline-block">
        <a class="nav-link dropdown-toggle text-white {{ request()->routeIs(['forms.*', 'questions.*']) ? 'active' : '' }}" 
           href="#" 
           role="button" 
           data-bs-toggle="dropdown" 
           aria-expanded="false">
            <i class="fas fa-clipboard-list"></i> Formularis
        </a>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item {{ request()->routeIs('forms.*') ? 'active' : '' }}" 
                   href="{{ route('forms.index') }}">
                    <i class="fas fa-clipboard-list"></i> Formularis
                </a>
            </li>
            <li>
                <a class="dropdown-item {{ request()->routeIs('questions.*') ? 'active' : '' }}" 
                   href="{{ route('questions.index') }}">
                    <i class="fas fa-question-circle"></i> Preguntes
                </a>
            </li>
        </ul>
    </div>
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
        <div class="dashboard-title">Panell d'Administrador</div>
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