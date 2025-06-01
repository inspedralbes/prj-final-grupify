@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Alertas para mensajes de sesión flash -->
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-2"></i>
            <strong>{{ session('success') }}</strong>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @elseif (session('error'))
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
                        <h1 class="h2 mb-2 fw-bold">Gestió de Grups</h1>
                        <p class="mb-0 opacity-75">Administra els grups d'estudiants i les seves assignacions</p>
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
        <!-- Columna de la tabla/lista de grupos -->
        <div class="col-lg-7">
            <!-- Versión escritorio: Tabla de grupos -->
            <div class="card border-0 shadow-sm d-none d-md-block">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2" style="color: var(--primary-color)"></i>
                        Llista de Grups
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Nom</th>
                                    <th>Integrants</th>
                                    <th class="text-end px-4">Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($groups as $group)
                                    <tr>
                                        <td class="ps-4">
                                            <span class="group-id">{{ $group->id }}</span>
                                        </td>
                                        <td class="fw-semibold">{{ $group->name }}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div>
                                                    <span class="badge bg-info rounded-pill me-2">
                                                        <i class="fas fa-users-cog me-1"></i> {{ $group->number_of_students }} places
                                                    </span>
                                                    
                                                    @if ($group->users->isEmpty())
                                                        <span class="badge bg-light text-dark border">
                                                            <i class="fas fa-user-slash me-1"></i> Sense integrants
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success rounded-pill">
                                                            <i class="fas fa-user-check me-1"></i> {{ $group->users->count() }} assignats
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                @if (!$group->users->isEmpty())
                                                <div class="mt-2 small">
                                                    <span class="text-muted">Integrants: </span>
                                                    @foreach ($group->users->take(2) as $user)
                                                        <span class="fw-semibold">{{ $user->name }}</span>@if (!$loop->last), @endif
                                                    @endforeach
                                                    @if ($group->users->count() > 2)
                                                        <span class="text-muted">i {{ $group->users->count() - 2 }} més</span>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end px-4">
                                            <div class="d-flex justify-content-end align-items-center gap-2">
                                                <a href="{{ route('groups.index', ['edit' => $group->id]) }}" 
                                                   class="btn btn-sm btn-outline-warning rounded-pill px-3" title="Editar grup">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('groups.destroy', $group->id) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger rounded-pill px-3" 
                                                            onclick="return confirm('Estàs segur que vols eliminar aquest grup?')"
                                                            title="Eliminar grup">
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
                                                <i class="fas fa-users text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                                <h5 class="fw-light text-muted">No s'han trobat grups</h5>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Versión móvil: Tarjetas de grupos -->
            <div class="d-md-none">
                @forelse($groups as $group)
                <div class="card border-0 shadow-sm mb-3 group-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <span class="group-id me-2">{{ $group->id }}</span>
                                {{ $group->name }}
                            </h5>
                            <div>
                                <span class="badge bg-info rounded-pill me-1">
                                    <i class="fas fa-users-cog me-1"></i> {{ $group->number_of_students }} places
                                </span>
                                <br class="d-sm-none">
                                @if ($group->users->isEmpty())
                                    <span class="badge bg-light text-dark border mt-1 mt-sm-0">
                                        <i class="fas fa-user-slash me-1"></i> Sense integrants
                                    </span>
                                @else
                                    <span class="badge bg-success rounded-pill mt-1 mt-sm-0">
                                        <i class="fas fa-user-check me-1"></i> {{ $group->users->count() }} assignats
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if (!$group->users->isEmpty())
                        <div class="mb-3">
                            <p class="text-muted mb-1 small">Membres del grup:</p>
                            <ul class="list-unstyled mb-0">
                                @foreach ($group->users->take(3) as $user)
                                    <li><i class="fas fa-user-circle text-primary me-2 small"></i>{{ $user->name }}</li>
                                @endforeach
                                @if ($group->users->count() > 3)
                                    <li class="text-muted small">+ {{ $group->users->count() - 3 }} més</li>
                                @endif
                            </ul>
                        </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                            <a href="{{ route('groups.index', ['edit' => $group->id]) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-1"></i> Editar
                            </a>
                            <form action="{{ route('groups.destroy', $group->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"
                                    onclick="return confirm('Estàs segur que vols eliminar aquest grup?')">
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
                        <h5 class="fw-light text-muted">No s'han trobat grups</h5>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Columna del formulario -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-{{ isset($_GET['edit']) ? 'edit' : 'plus' }} me-2" 
                           style="color: var(--primary-color)"></i>
                        {{ isset($_GET['edit']) ? 'Editar Grup' : 'Crear Nou Grup' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($_GET['edit']) ? route('groups.update', $_GET['edit']) : route('groups.store') }}" 
                          method="POST" class="needs-validation" novalidate>
                        @if(isset($_GET['edit']))
                            @method('PUT')
                        @endif
                        @csrf

                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold">Nom del Grup</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-users text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="{{ isset($_GET['edit']) ? $groups->firstWhere('id', $_GET['edit'])->name : '' }}" 
                                       placeholder="Introdueix el nom del grup"
                                       required>
                            </div>
                            <div class="form-text">Per exemple: "Grup A", "Equip de Matemàtiques", etc.</div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Descripció del Grup</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-info-circle text-primary"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       id="description" 
                                       name="description" 
                                       value="{{ isset($_GET['edit']) ? $groups->firstWhere('id', $_GET['edit'])->description : '' }}"
                                       placeholder="Breu descripció (opcional)">
                            </div>
                            <div class="form-text">Una descripció curta sobre el propòsit d'aquest grup</div>
                        </div>

                        <div class="mb-4">
                            <label for="number_of_students" class="form-label fw-semibold">Places Disponibles</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-user-graduate text-primary"></i>
                                </span>
                                <input type="number" 
                                       class="form-control" 
                                       id="number_of_students" 
                                       name="number_of_students" 
                                       value="{{ isset($_GET['edit']) ? $groups->firstWhere('id', $_GET['edit'])->number_of_students : '' }}" 
                                       placeholder="Núm. de places"
                                       min="1"
                                       required>
                            </div>
                            <div class="form-text">
                                Indica el nombre màxim d'estudiants que poden formar part d'aquest grup.
                                <span class="text-info"><i class="fas fa-info-circle me-1"></i>Els estudiants s'han d'assignar després de crear el grup.</span>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary py-2">
                                <i class="fas fa-save me-2"></i>
                                {{ isset($_GET['edit']) ? 'Actualitzar Grup' : 'Crear Grup' }}
                            </button>

                            @if(isset($_GET['edit']))
                                <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary py-2">
                                    <i class="fas fa-times me-2"></i>
                                    Cancel·lar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            @if (!isset($_GET['edit']))
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-primary bg-opacity-10 me-3">
                            <i class="fas fa-lightbulb text-primary"></i>
                        </div>
                        <h5 class="card-title mb-0">Consell</h5>
                    </div>
                    <p class="text-muted mb-0">Els grups són la base per organitzar els estudiants i gestionar les activitats. Després de crear un grup, podràs assignar-hi estudiants i fer un seguiment del seu progrés a través de la bitàcola associada.</p>
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
    .group-card {
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .group-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }

    .group-id {
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
    .form-control:focus {
        border-color: #00ADEE;
        box-shadow: 0 0 0 0.25rem rgba(0, 173, 238, 0.25);
    }

    .input-group-text {
        border: none;
        background-color: #f8f9fa;
    }

    /* Estilos para el círculo de icono */
    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
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