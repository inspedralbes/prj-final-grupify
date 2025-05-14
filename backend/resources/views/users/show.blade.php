@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Cabecera con información básica -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="p-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #00ADEE 0%, #0078A0 100%);">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div>
                            <h1 class="h2 mb-1 fw-bold">{{ $user->name }} {{ $user->last_name }}</h1>
                            <p class="mb-0 d-flex align-items-center">
                                <i class="fas fa-envelope me-2 opacity-75"></i>
                                {{ $user->email }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3 mt-md-0">
                        <span class="badge bg-white text-primary px-3 py-2 rounded-pill fw-bold shadow-sm">
                            <i class="fas fa-user-tag me-1"></i>
                            {{ $user->role->name ?? 'No especificat' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Información personal - ahora con diseño de iconos circulares -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold text-center">
                        <i class="fas fa-id-card me-2 text-primary"></i>
                        Informació Personal
                    </h5>
                </div>
                <div class="card-body">
                    <div class="profile-info-container">
                        <div class="profile-info-item">
                            <div class="profile-info-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="profile-info-content">
                                <div class="profile-info-label">Nom</div>
                                <div class="profile-info-value">{{ $user->name }}</div>
                            </div>
                        </div>

                        <div class="profile-info-item">
                            <div class="profile-info-icon">
                                <i class="fas fa-user-tag"></i>
                            </div>
                            <div class="profile-info-content">
                                <div class="profile-info-label">Cognoms</div>
                                <div class="profile-info-value">{{ $user->last_name }}</div>
                            </div>
                        </div>

                        <div class="profile-info-item">
                            <div class="profile-info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="profile-info-content">
                                <div class="profile-info-label">Correu</div>
                                <div class="profile-info-value">{{ $user->email }}</div>
                            </div>
                        </div>

                        <div class="profile-info-item">
                            <div class="profile-info-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="profile-info-content">
                                <div class="profile-info-label">Rol</div>
                                <div class="profile-info-value">
                                    <span class="badge bg-light text-primary border px-3 py-2">
                                        {{ $user->role->name ?? 'No especificat' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light py-3 border-0">
                    <div class="d-flex align-items-center small text-muted gap-3 justify-content-center">
                        <div>
                            <i class="fas fa-calendar-plus me-1"></i>
                            Creat: {{ \Carbon\Carbon::parse($user->created_at)->format('d/m/Y H:i') }}
                        </div>
                        <div>
                            <i class="fas fa-edit me-1"></i>
                            Actualitzat: {{ \Carbon\Carbon::parse($user->updated_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información académica - sin cambios -->
        <div class="col-lg-7">
            @if($user->role_id == 1) <!-- Si es profesor -->
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>
                        Informació Docent
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3 border-start border-4 border-primary ps-3">Cursos i Divisions assignats</h6>

                    @if($user->courseDivisionUsers->count() > 0)
                    <div class="table-responsive rounded mb-4">
                        <table class="table table-hover mb-0 border">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0">Curs</th>
                                    <th class="border-0">Divisió</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->courseDivisionUsers as $cdu)
                                <tr>
                                    <td class="align-middle">
                                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                                            <i class="fas fa-graduation-cap me-1"></i>
                                            {{ $cdu->course->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill">
                                            <i class="fas fa-sitemap me-1"></i>
                                            {{ $cdu->division->division ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-light border text-center mb-4">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        Sense cursos ni divisions assignats
                    </div>
                    @endif

                    <h6 class="mb-3 border-start border-4 border-primary ps-3">Assignatures</h6>

                    @if($user->subjects->count() > 0)
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($user->subjects as $subject)
                        <span class="badge bg-light text-dark border px-3 py-2 d-flex align-items-center">
                            <i class="fas fa-book me-2 text-primary"></i>
                            {{ $subject->name }}
                        </span>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-light border text-center">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        Sense assignatures assignades
                    </div>
                    @endif
                </div>
            </div>
            @elseif($user->role_id == 4 || $user->role_id == 5) <!-- Si es tutor u orientador -->
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-user-tie me-2 text-primary"></i>
                        Informació {{ $user->role_id == 4 ? 'del Tutor' : "de l'Orientador" }}
                    </h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="row g-4 text-center">
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 bg-light">
                                <div class="display-6 mb-3 text-{{ $user->role_id == 4 ? 'warning' : 'info' }}">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h6 class="mb-2 text-muted">Curs Assignat</h6>
                                <div class="fs-5 fw-medium">
                                    {{ $user->courseDivisionUsers->first()?->course->name ?? 'Sense curs assignat' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 bg-light">
                                <div class="display-6 mb-3 text-{{ $user->role_id == 4 ? 'warning' : 'info' }}">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <h6 class="mb-2 text-muted">Divisió Assignada</h6>
                                <div class="fs-5 fw-medium">
                                    {{ $user->courseDivisionUsers->first()?->division->division ?? 'Sense divisió assignada' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @elseif($user->role_id == 2) <!-- Si es estudiante -->
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-user-graduate me-2 text-primary"></i>
                        Informació Acadèmica
                    </h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="row g-4 text-center">
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 bg-light">
                                <div class="display-6 mb-3 text-primary">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h6 class="mb-2 text-muted">Curs Assignat</h6>
                                <div class="fs-5 fw-medium">
                                    {{ $user->courseDivisionUsers->first()?->course->name ?? 'Sense curs assignat' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 bg-light">
                                <div class="display-6 mb-3 text-primary">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <h6 class="mb-2 text-muted">Divisió Assignada</h6>
                                <div class="fs-5 fw-medium">
                                    {{ $user->courseDivisionUsers->first()?->division->division ?? 'Sense divisió assignada' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <a href="{{ route('users.index') }}" class="btn btn-primary rounded-pill px-4 py-2 d-inline-flex align-items-center shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>
            Tornar a la Llista d'Usuaris
        </a>
    </div>
</div>

<style>
    /* Estilo para el avatar de usuario */
    .user-avatar {
        width: 80px;
        height: 80px;
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .user-avatar i {
        font-size: 3rem;
        color: white;
    }

    /* Estilos para información personal con iconos */
    .profile-info-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        padding: 0.5rem 1rem;
    }

    .profile-info-item {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .profile-info-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 173, 238, 0.1);
        border-radius: 50%;
        color: #00ADEE;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .profile-info-content {
        flex-grow: 1;
    }

    .profile-info-label {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .profile-info-value {
        font-weight: 500;
        color: #333;
        font-size: 1.1rem;
    }

    /* Ajustes para la lista de info personal */
    .list-group-item {
        border-left: none;
        border-right: none;
        border-color: rgba(0, 0, 0, 0.05);
    }

    /* Estilos para fondos de colores suaves */
    .bg-primary-subtle {
        background-color: rgba(0, 173, 238, 0.15);
    }

    .bg-info-subtle {
        background-color: rgba(23, 162, 184, 0.15);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .user-avatar {
            width: 60px;
            height: 60px;
        }

        .user-avatar i {
            font-size: 2.2rem;
        }

        h1.h2 {
            font-size: 1.5rem;
        }

        .badge {
            font-size: 0.8rem;
        }

        /* Ajustes para el perfil en móvil */
        .profile-info-container {
            padding: 0.25rem 0;
            gap: 1.25rem;
        }

        .profile-info-item {
            gap: 1rem;
        }

        .profile-info-icon {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .profile-info-label {
            font-size: 0.8rem;
        }

        .profile-info-value {
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .p-4 {
            padding: 1rem !important;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
        }

        .user-avatar i {
            font-size: 1.8rem;
        }

        h1.h2 {
            font-size: 1.3rem;
        }

        .card-header {
            padding: 0.75rem 1rem;
        }

        .card-body {
            padding: 1rem;
        }

        .card-footer .d-flex {
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
    }
</style>
@endsection