@extends('layouts.app')
@section('content')
<div class="container py-4">
    <!-- Encabezado -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 p-md-5 bg-gradient-primary text-white position-relative" style="background: linear-gradient(135deg, #00ADEE 0%, #0078A0 100%);">
                        <div class="row align-items-center">
                            <div class="col-lg-7">
                                <h1 class="display-4 fw-bold mb-3">Panell d'Administració</h1>
                                <p class="lead mb-0">Gestiona tots els aspectes de la plataforma des d'un sol lloc.</p>
                            </div>
                            <div class="col-lg-5 d-none d-lg-block text-center">
                                <i class="fas fa-cogs fa-5x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secciones principales -->
    <h2 class="fw-bold mb-4 ps-2 border-start border-4 border-primary">Gestió d'Entitats</h2>

    <div class="row g-4 mb-5">
        <!-- Usuarios -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('users.index') }}" class="text-decoration-none text-dark">
                <div class="card h-100 border-0 shadow-sm dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-users fa-3x" style="color: #00ADEE;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Usuaris</h5>
                        <p class="card-text text-muted">Gestiona els perfils i accessos</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-4">
                        <span class="badge bg-light text-primary shadow-sm px-3 py-2 rounded-pill">
                            <i class="fas fa-arrow-right me-1"></i> Accedir
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Roles -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('roles.index') }}" class="text-decoration-none text-dark">
                <div class="card h-100 border-0 shadow-sm dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-user-tag fa-3x" style="color: #FF5722;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Rols</h5>
                        <p class="card-text text-muted">Configura els permisos del sistema</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-4">
                        <span class="badge bg-light text-primary shadow-sm px-3 py-2 rounded-pill">
                            <i class="fas fa-arrow-right me-1"></i> Accedir
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Grupos -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('groups.index') }}" class="text-decoration-none text-dark">
                <div class="card h-100 border-0 shadow-sm dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-layer-group fa-3x" style="color: #4CAF50;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Grups</h5>
                        <p class="card-text text-muted">Organitza els equips d'estudiants</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-4">
                        <span class="badge bg-light text-primary shadow-sm px-3 py-2 rounded-pill">
                            <i class="fas fa-arrow-right me-1"></i> Accedir
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Secciones académicas -->
    <h2 class="fw-bold mb-4 ps-2 border-start border-4 border-primary">Gestió Acadèmica</h2>

    <div class="row g-4 mb-5">
        <!-- Cursos -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('courses.index') }}" class="text-decoration-none text-dark">
                <div class="card h-100 border-0 shadow-sm dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-graduation-cap fa-3x" style="color: #2196F3;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Cursos</h5>
                        <p class="card-text text-muted">Administra anys acadèmics</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-4">
                        <span class="badge bg-light text-primary shadow-sm px-3 py-2 rounded-pill">
                            <i class="fas fa-arrow-right me-1"></i> Accedir
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Divisiones -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('divisions.index') }}" class="text-decoration-none text-dark">
                <div class="card h-100 border-0 shadow-sm dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-sitemap fa-3x" style="color: #9C27B0;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Divisions</h5>
                        <p class="card-text text-muted">Estructura les classes i grups</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-4">
                        <span class="badge bg-light text-primary shadow-sm px-3 py-2 rounded-pill">
                            <i class="fas fa-arrow-right me-1"></i> Accedir
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Asignaturas -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('subjects.index') }}" class="text-decoration-none text-dark">
                <div class="card h-100 border-0 shadow-sm dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-book fa-3x" style="color: #FF9800;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Assignatures</h5>
                        <p class="card-text text-muted">Gestiona matèries i contingut</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-4">
                        <span class="badge bg-light text-primary shadow-sm px-3 py-2 rounded-pill">
                            <i class="fas fa-arrow-right me-1"></i> Accedir
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Secciones de Evaluación -->
    <h2 class="fw-bold mb-4 ps-2 border-start border-4 border-primary">Eines d'Avaluació</h2>

    <div class="row g-4">
        <!-- Formularios -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('forms.index') }}" class="text-decoration-none text-dark">
                <div class="card h-100 border-0 shadow-sm dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-clipboard-list fa-3x" style="color: #3F51B5;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Formularis</h5>
                        <p class="card-text text-muted">Crea i gestiona enquestes</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-4">
                        <span class="badge bg-light text-primary shadow-sm px-3 py-2 rounded-pill">
                            <i class="fas fa-arrow-right me-1"></i> Accedir
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Preguntas -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <a href="{{ route('questions.index') }}" class="text-decoration-none text-dark">
                <div class="card h-100 border-0 shadow-sm dashboard-card">
                    <div class="card-body text-center p-4">
                        <div class="icon-container mb-3">
                            <i class="fas fa-question-circle fa-3x" style="color: #607D8B;"></i>
                        </div>
                        <h5 class="card-title fw-bold">Preguntes</h5>
                        <p class="card-text text-muted">Administra el banc de preguntes</p>
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-4">
                        <span class="badge bg-light text-primary shadow-sm px-3 py-2 rounded-pill">
                            <i class="fas fa-arrow-right me-1"></i> Accedir
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    /* Estilos para mejorar la apariencia */
    .dashboard-card {
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
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
        transition: all 0.3s ease;
    }

    .dashboard-card:hover .icon-container {
        transform: scale(1.1);
    }

    .bg-gradient-primary {
        position: relative;
        overflow: hidden;
    }

    .bg-gradient-primary::before {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        opacity: 0.3;
    }

    /* Optimización para móviles */
    @media (max-width: 768px) {
        .icon-container {
            height: 60px;
            width: 60px;
        }

        .icon-container i {
            font-size: 2rem !important;
        }

        .card-text {
            display: none;
            /* Ocultar la descripción en móviles para ahorrar espacio */
        }

        .display-4 {
            font-size: 2.5rem;
        }

        .lead {
            font-size: 1rem;
        }
    }
</style>
@endsection