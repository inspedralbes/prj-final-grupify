<!DOCTYPE html>
<html lang="ca">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grupify - Administració</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #00ADEE;
            --primary-dark: #0089BE;
            --primary-hover: #33BDFF;
            --active-background: rgba(255, 255, 255, 0.2);
            --background-color: #f8f9fa;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --navbar-background: linear-gradient(135deg, #00ADEE 0%, #0078A0 100%);
            --link-active-bg: rgba(255, 255, 255, 0.15);
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            margin: 0;
            background-color: var(--background-color);
            min-height: 100vh;
        }

        /* Navbar mejorado */
        .navbar {
            background-image: var(--navbar-background);
            padding: 0.75rem 1.25rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        }

        .navbar-brand {
            font-size: 1.3rem;
            font-weight: 600;
            letter-spacing: 0.01rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
            color: white !important;
            transition: all var(--transition-speed) ease;
        }

        .navbar-brand i {
            font-size: 1.4rem;
            opacity: 0.9;
        }

        .navbar-brand:hover {
            transform: translateY(-1px);
            color: white !important;
        }

        /* Nav links mejorados */
        .nav-links {
            display: flex;
            gap: 0.25rem;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        .nav-links a,
        .nav-link.dropdown-toggle {
            color: rgba(255, 255, 255, 0.95) !important;
            font-weight: 500;
            text-decoration: none;
            padding: 0.6rem 0.9rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
        }

        .nav-links a::before,
        .nav-link.dropdown-toggle::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background-color: white;
            transform: translateX(-50%);
            transition: width var(--transition-speed) ease;
            opacity: 0.7;
        }

        .nav-links a:hover::before,
        .nav-link.dropdown-toggle:hover::before,
        .nav-links a.active::before,
        .nav-link.dropdown-toggle.active::before {
            width: 60%;
        }

        .nav-links a:hover,
        .nav-link.dropdown-toggle:hover {
            background-color: var(--link-active-bg);
            color: white !important;
            transform: translateY(-1px);
        }

        .nav-links a.active,
        .nav-link.dropdown-toggle.active {
            background-color: var(--active-background);
            color: white !important;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .nav-links i {
            font-size: 1rem;
        }

        /* Dropdown mejorado - cambiado a fondo azul con texto blanco */
        .dropdown-menu {
            border: none !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12) !important;
            margin-top: 0.5rem !important;
            padding: 0.5rem !important;
            min-width: 10rem !important;
            background-color: var(--primary-dark) !important;
        }

        .dropdown-item {
            color: white !important;
            border-radius: 0.4rem !important;
            padding: 0.6rem 1rem !important;
            margin-bottom: 0.2rem !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
        }

        .dropdown-item:last-child {
            margin-bottom: 0;
        }

        .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
        }

        .dropdown-item.active {
            background-color: rgba(255, 255, 255, 0.25) !important;
            color: white !important;
            font-weight: 600 !important;
        }

        .dropdown-item i {
            width: 1.2rem !important;
            margin-right: 0.5rem !important;
            text-align: center !important;
            color: rgba(255, 255, 255, 0.85) !important;
        }

        .dropdown-item.active i {
            color: white !important;
        }

        /* Botón logout mejorado */
        .logout-btn {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all var(--transition-speed) ease;
            text-decoration: none;
            cursor: pointer;
        }

        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .logout-btn i {
            font-size: 1rem;
        }

        /* Dashboard header */
        .dashboard-header {
            background-image: var(--navbar-background);
            padding: 1.25rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
        }

        .dashboard-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
            letter-spacing: 0.01rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }

        .dashboard-title i {
            font-size: 1.5rem;
            opacity: 0.9;
        }

        /* Contenido principal */
        .content {
            padding: 2rem;
            min-height: calc(100vh - 70px);
        }

        /* Modal de logout mejorado */
        #logoutModal .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        #logoutModal .modal-header {
            background-image: var(--navbar-background);
            color: white;
            border: none;
            padding: 1.25rem 1.5rem;
        }

        #logoutModal .modal-title {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        #logoutModal .btn-close {
            color: white;
            opacity: 0.8;
            filter: brightness(5);
        }

        #logoutModal .modal-body {
            padding: 2rem;
            text-align: center;
        }

        #logoutModal .modal-body i {
            font-size: 3.5rem;
            color: #FFC107;
            margin-bottom: 1.5rem;
            display: block;
        }

        #logoutModal .modal-body p {
            font-size: 1.2rem;
            color: #444;
            margin: 0;
        }

        #logoutModal .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            padding: 1.25rem;
            justify-content: center;
            gap: 1rem;
        }

        #logoutModal .btn-secondary {
            background-color: #6c757d;
            border: none;
            border-radius: 0.5rem;
            padding: 0.6rem 1.25rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        #logoutModal .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        #logoutModal .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 0.5rem;
            padding: 0.6rem 1.25rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        #logoutModal .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        /* Animaciones de tarjetas para dashboard */
        .dashboard-card {
            transition: all var(--transition-speed) ease;
            overflow: hidden;
            border: none;
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .icon-container {
            height: 80px;
            width: 80px;
            margin: 0 auto;
            border-radius: 50%;
            background-color: rgba(0, 0, 0, 0.03);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-speed) ease;
        }

        .dashboard-card:hover .icon-container {
            transform: scale(1.1);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .navbar {
                padding: 0.6rem 1rem;
            }

            .nav-links {
                flex-wrap: wrap;
                gap: 0.2rem;
            }

            .nav-links a,
            .nav-link.dropdown-toggle {
                padding: 0.5rem 0.7rem;
                font-size: 0.9rem;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }

            .logout-btn {
                padding: 0.5rem 0.8rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                padding: 0.6rem;
            }

            .navbar-brand,
            .logout-btn {
                margin-bottom: 0.5rem;
            }

            .nav-links {
                width: 100%;
                justify-content: center;
                margin-bottom: 0.5rem;
            }

            .content {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar para secciones -->
    @if (!request()->routeIs('dashboard'))
    <nav class="navbar">
        <!-- Logo y título -->
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            <img src="/images/icono.png" alt="Grupify Logo" style="height: 30px; width: auto;"> Grupify
        </a>

        <!-- Enlaces de navegación -->
        <div class="nav-links">
            <a href="{{ route('roles.index') }}" class="{{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="fas fa-user-tag"></i> Rols
            </a>

            <!-- Menú desplegable de Classes -->
            <div class="dropdown d-inline-block">
                <a class="nav-link dropdown-toggle {{ request()->routeIs(['courses.*', 'divisions.*']) ? 'active' : '' }}"
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
                <a class="nav-link dropdown-toggle {{ request()->routeIs(['forms.*', 'questions.*']) ? 'active' : '' }}"
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
        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); submitLogoutForm('logout-header-form');"
            class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Tancar Sessió
        </a>
        <form id="logout-header-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </nav>
    @endif

    <!-- Cabecera del dashboard -->
    @if (request()->routeIs('dashboard'))
    <header class="dashboard-header">
        <div class="dashboard-title">
            <img src="/images/icono.png" alt="Grupify Logo" style="height: 30px; width: auto;"> Grupify
        </div>

        <!-- Botón de cierre de sesión -->
        <a href="{{ route('logout') }}"
            onclick="event.preventDefault(); submitLogoutForm('logout-dashboard-form');"
            class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Tancar Sessió
        </a>
        <form id="logout-dashboard-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </header>
    @endif

    <!-- Modal de confirmación de logout mejorado -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">
                        <i class="fas fa-sign-out-alt"></i> Tancar Sessió
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Estàs segur que vols tancar la sessió?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel·lar
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmLogout">
                        <i class="fas fa-sign-out-alt me-1"></i> Sí, tancar sessió
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="content">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentLogoutFormId = null;

        function submitLogoutForm(formId) {
            // Guardar el ID del formulario actual
            currentLogoutFormId = formId;

            // Mostrar el modal de confirmación
            const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
            logoutModal.show();
        }

        // Manejar el click en el botón de confirmación
        document.getElementById('confirmLogout').addEventListener('click', function() {
            if (currentLogoutFormId) {
                // Cerrar el modal
                const logoutModal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
                logoutModal.hide();

                // Enviar el formulario
                document.getElementById(currentLogoutFormId).submit();
            }
        });
    </script>
</body>

</html>