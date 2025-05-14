@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Alertas para mensajes de sesión flash -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <strong>{{ session('success') }}</strong>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>{{ session('error') }}</strong>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Errors de validació -->
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>
                <strong>S'han trobat errors de validació:</strong>
                <ul class="mb-0 mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Card de encabezado -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="p-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #00ADEE 0%, #0078A0 100%);">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h1 class="h2 mb-2 fw-bold">Gestió de Rols</h1>
                        <p class="mb-0 opacity-75">Administra els rols del sistema i els seus permisos</p>
                    </div>
                    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-pill shadow-sm px-4 py-2 d-inline-flex align-items-center align-self-start">
                        <i class="fas fa-arrow-left me-2"></i>
                        Tornar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Versión para escritorio: Tabla de roles -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm d-none d-md-block">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-tag me-2" style="color: var(--primary-color)"></i>
                        Llista de Rols
                    </h5>
                    @if(!isset($_GET['edit']))
                    <a href="{{ route('roles.index', ['new' => true]) }}" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nou Rol
                    </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 ps-4">ID</th>
                                    <th class="border-0">Nom</th>
                                    <th class="border-0">Descripció</th>
                                    <th class="border-0 text-center">Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                    <tr>
                                        <td class="ps-4">{{ $role->id }}</td>
                                        <td>
                                            <span class="fw-semibold">{{ $role->name }}</span>
                                        </td>
                                        <td>{{ $role->description ?? 'Sense descripció' }}</td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('roles.index', ['edit' => $role->id]) }}" 
                                                   class="btn btn-sm btn-outline-warning rounded-pill px-3" title="Editar rol">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('roles.destroy', $role->id) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                                            onclick="return confirm('Estàs segur que vols eliminar aquest rol?')"
                                                            title="Eliminar rol">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center py-4">
                                                <i class="fas fa-user-tag text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                                <h5 class="fw-light text-muted">No s'han trobat rols</h5>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Versión para móviles: Tarjetas de roles -->
            <div class="d-md-none">
                @forelse($roles as $role)
                <div class="card border-0 shadow-sm mb-3 role-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <span class="role-id me-2">{{ $role->id }}</span>
                                {{ $role->name }}
                            </h5>
                        </div>

                        <p class="card-text mb-3">
                            <i class="fas fa-info-circle text-muted me-2"></i>
                            {{ $role->description ?? 'Sense descripció' }}
                        </p>

                        <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                            <a href="{{ route('roles.index', ['edit' => $role->id]) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-1"></i> Editar
                            </a>
                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"
                                    onclick="return confirm('Estàs segur que vols eliminar aquest rol?')">
                                    <i class="fas fa-trash-alt me-1"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-user-tag text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                        <h5 class="fw-light text-muted">No s'han trobat rols</h5>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Formulari per Crear o Editar -->
        <div class="col-lg-4">
            @if(isset($_GET['edit']) || isset($_GET['new']))
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-{{ isset($_GET['edit']) ? 'edit' : 'plus' }} me-2" 
                           style="color: var(--primary-color)"></i>
                        {{ isset($_GET['edit']) ? 'Editar Rol' : 'Crear Nou Rol' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($_GET['edit']) ? route('roles.update', $_GET['edit']) : route('roles.store') }}" 
                          method="POST" class="needs-validation" novalidate>
                        @if(isset($_GET['edit']))
                            @method('PUT')
                        @endif
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold">Nom del Rol</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-tag text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="{{ isset($_GET['edit']) ? $roles->firstWhere('id', $_GET['edit'])->name : '' }}" 
                                       required
                                       placeholder="Introdueix el nom del rol">
                            </div>
                            <div class="form-text">El nom ha de ser únic i descriptiu.</div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Descripció del Rol</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-info-circle text-primary"></i>
                                </span>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="4"
                                          placeholder="Descriu les funcionalitats d'aquest rol">{{ isset($_GET['edit']) ? $roles->firstWhere('id', $_GET['edit'])->description : '' }}</textarea>
                            </div>
                            <div class="form-text">Una breu descripció dels permisos i funcions d'aquest rol.</div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary py-2">
                                <i class="fas fa-save me-2"></i>
                                {{ isset($_GET['edit']) ? 'Actualitzar Rol' : 'Crear Rol' }}
                            </button>

                            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary py-2">
                                <i class="fas fa-times me-2"></i>
                                Cancel·lar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            @else
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center py-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-user-tag" style="font-size: 3rem; color: #00ADEE;"></i>
                    </div>
                    <h4 class="mb-3">Gestiona els Rols</h4>
                    <p class="text-muted mb-4">Els rols determinen els permisos i accions que poden realitzar els usuaris al sistema.</p>
                    <a href="{{ route('roles.index', ['new' => true]) }}" class="btn btn-primary rounded-pill px-4 d-inline-flex align-items-center">
                        <i class="fas fa-plus-circle me-2"></i>
                        Afegir Nou Rol
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    /* Estilos para mejorar la apariencia de la tabla */
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        color: #495057;
    }

    .table td {
        vertical-align: middle;
        padding: 0.75rem 0.5rem;
        color: #333;
    }

    /* Mejorar apariencia de los botones de acción */
    .btn-outline-primary,
    .btn-outline-warning,
    .btn-outline-danger {
        border-width: 1.5px;
    }

    .btn-outline-primary:hover,
    .btn-outline-warning:hover,
    .btn-outline-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Estilos para las tarjetas de móvil */
    .role-card {
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .role-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }

    .role-id {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background-color: #f8f9fa;
        border-radius: 50%;
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 600;
    }

    /* Animación sutil para filas */
    tbody tr {
        transition: all 0.2s ease;
    }

    tbody tr:hover {
        background-color: rgba(0, 173, 238, 0.05) !important;
    }

    /* Mejorar apariencia de los formularios */
    .form-control:focus, 
    .form-select:focus {
        border-color: #00ADEE;
        box-shadow: 0 0 0 0.25rem rgba(0, 173, 238, 0.25);
    }

    .input-group-text {
        border: none;
        background-color: #f8f9fa;
    }

    /* Ajustes adicionales para responsividad */
    @media (max-width: 576px) {
        .card-body {
            padding: 1rem;
        }

        .btn {
            padding: 0.4rem 0.75rem;
            font-size: 0.9rem;
        }
    }
</style>
@endsection