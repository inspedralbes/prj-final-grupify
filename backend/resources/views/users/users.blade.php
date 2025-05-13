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

    <!-- Card de encabezado -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="p-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #00ADEE 0%, #0078A0 100%);">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h1 class="h2 mb-2 fw-bold">Gestió d'Usuaris</h1>
                        <p class="mb-0 opacity-75">Administra els usuaris del sistema i els seus rols</p>
                    </div>
                    <a href="{{ route('dashboard') }}" class="btn btn-light rounded-pill shadow-sm px-4 py-2 d-inline-flex align-items-center align-self-start">
                        <i class="fas fa-arrow-left me-2"></i>
                        Tornar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de herramientas: botones y filtros -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-4 align-items-center">
                <!-- Botón Añadir -->
                <div class="col-md-6">
                    <a href="{{ route('users.create') }}" class="btn btn-primary rounded-pill px-4 py-2 d-inline-flex align-items-center">
                        <i class="fas fa-plus-circle me-2"></i>
                        Afegir Nou Usuari
                    </a>
                </div>

                <!-- Filtros -->
                <div class="col-md-6">
                    <form action="{{ route('users.index') }}" method="GET">
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <div class="flex-grow-1">
                                <label for="role_filter" class="form-label d-block d-md-none mb-1">Filtrar per rol:</label>
                                <select id="role_filter" name="role_id" class="form-select">
                                    <option value="">Tots els rols</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex gap-2 mt-2 mt-sm-0">
                                <button type="submit" class="btn btn-primary px-3 py-2">
                                    <i class="fas fa-filter me-2"></i>Aplicar
                                </button>
                                @if(request('role_id'))
                                <a href="{{ route('users.index') }}" class="btn btn-light border px-3 py-2">
                                    <i class="fas fa-times me-2"></i>Netejar
                                </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Versión para escritorio: Tabla de usuarios -->
    <div class="card border-0 shadow-sm d-none d-md-block">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4">ID</th>
                            <th class="border-0">Nom</th>
                            <th class="border-0">Cognom</th>
                            <th class="border-0">Correu electrònic</th>
                            <th class="border-0">Rol</th>
                            <th class="border-0 text-center">Accions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="ps-4">{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->last_name }}</td>
                            <td>
                                <span class="d-inline-flex align-items-center">
                                    <i class="fas fa-envelope text-muted me-2"></i>
                                    {{ $user->email }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                                    <i class="fas fa-user-tag me-1" style="color: #00ADEE;"></i>
                                    {{ $user->role ? $user->role->name : 'Sense rol assignat' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Veure detalls">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3" title="Editar usuari">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                            onclick="return confirm('Estàs segur que vols eliminar aquest usuari?')" title="Eliminar usuari">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center py-4">
                                    <i class="fas fa-users text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                    <h5 class="fw-light text-muted">No s'han trobat usuaris</h5>
                                    @if(request('role_id'))
                                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-primary mt-3">
                                        <i class="fas fa-filter me-1"></i> Eliminar filtres
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Versión para móviles: Tarjetas de usuarios -->
    <div class="d-md-none">
        @forelse($users as $user)
        <div class="card border-0 shadow-sm mb-3 user-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="card-title mb-0 d-flex align-items-center">
                        <span class="user-id me-2">{{ $user->id }}</span>
                        {{ $user->name }} {{ $user->last_name }}
                    </h5>
                    <span class="badge rounded-pill bg-light text-dark border">
                        <i class="fas fa-user-tag me-1" style="color: #00ADEE;"></i>
                        {{ $user->role ? $user->role->name : 'Sense rol' }}
                    </span>
                </div>

                <p class="card-text mb-3">
                    <i class="fas fa-envelope text-muted me-2"></i>
                    {{ $user->email }}
                </p>

                <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-1"></i> Veure
                    </a>
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-warning">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger"
                            onclick="return confirm('Estàs segur que vols eliminar aquest usuari?')">
                            <i class="fas fa-trash-alt me-1"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-users text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                <h5 class="fw-light text-muted">No s'han trobat usuaris</h5>
                @if(request('role_id'))
                <a href="{{ route('users.index') }}" class="btn btn-outline-primary mt-3">
                    <i class="fas fa-filter me-1"></i> Eliminar filtres
                </a>
                @endif
            </div>
        </div>
        @endforelse
    </div>

    <!-- Paginación -->
    <div class="d-flex justify-content-center mt-4">
        {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
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
    .user-card {
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .user-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }

    .user-id {
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