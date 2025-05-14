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
    @endif

    @if (session('error'))
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
                        <h1 class="h2 mb-2 fw-bold">Gestió de Formularis</h1>
                        <p class="mb-0 opacity-75">Crea i administra formularis per als estudiants</p>
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
        <!-- Versión para escritorio: Tabla de formularios -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm d-none d-md-block">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clipboard-list me-2" style="color: var(--primary-color)"></i>
                        Llista de Formularis
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">ID</th>
                                    <th>Títol</th>
                                    <th>Descripció</th>
                                    <th>Professor</th>
                                    <th>Data límit</th>
                                    <th class="text-center">Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($forms as $form)
                                    <tr>
                                        <td class="px-4">
                                            <span class="form-id">{{ $form->id }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $form->title }}</span>
                                        </td>
                                        <td>
                                            @if($form->description)
                                                {{ Str::limit($form->description, 30) }}
                                            @else
                                                <span class="text-muted">Sense descripció</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($form->teacher)
                                                <span class="d-flex align-items-center">
                                                    <i class="fas fa-user text-primary me-2"></i>
                                                    {{ $form->teacher->name ?? 'Global' }}
                                                </span>
                                            @else
                                                <span class="badge bg-info rounded-pill">Global</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($form->date_limit)
                                                <span class="badge bg-light text-dark border">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    {{ \Carbon\Carbon::parse($form->date_limit)->format('d/m/Y') }}
                                                </span>
                                            @else
                                                <span class="text-muted">No definida</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="{{ route('forms.index', ['edit' => $form->id]) }}" 
                                                   class="btn btn-sm btn-outline-warning rounded-pill px-3" title="Editar formulari">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form action="{{ route('forms.destroy', $form->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                                        onclick="return confirm('Estàs segur que vols eliminar aquest formulari?')"
                                                        title="Eliminar formulari">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center py-4">
                                                <i class="fas fa-clipboard-list text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                                                <h5 class="fw-light text-muted">No s'han trobat formularis</h5>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Versión para móviles: Tarjetas de formularios -->
            <div class="d-md-none">
                @forelse($forms as $form)
                <div class="card border-0 shadow-sm mb-3 form-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <span class="form-id me-2">{{ $form->id }}</span>
                                {{ $form->title }}
                            </h5>
                        </div>
                        
                        <div class="mb-3">
                            @if($form->description)
                                <p class="mb-2">
                                    <i class="fas fa-align-left text-primary me-2"></i>
                                    {{ Str::limit($form->description, 100) }}
                                </p>
                            @endif
                            
                            <p class="mb-1">
                                <i class="fas fa-user text-primary me-2"></i>
                                <span class="text-muted">Professor:</span> 
                                <span class="fw-semibold">{{ $form->teacher->name ?? 'Global' }}</span>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                <span class="text-muted">Data límit:</span> 
                                <span class="fw-semibold">
                                    {{ $form->date_limit ? \Carbon\Carbon::parse($form->date_limit)->format('d/m/Y') : 'No definida' }}
                                </span>
                            </p>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                            <a href="{{ route('forms.index', ['edit' => $form->id]) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-1"></i> Editar
                            </a>
                            <form action="{{ route('forms.destroy', $form->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"
                                    onclick="return confirm('Estàs segur que vols eliminar aquest formulari?')">
                                    <i class="fas fa-trash-alt me-1"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-clipboard-list text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                        <h5 class="fw-light text-muted">No s'han trobat formularis</h5>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Formulario Crear/Editar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-{{ isset($_GET['edit']) ? 'edit' : 'plus' }} me-2" 
                           style="color: var(--primary-color)"></i>
                        {{ isset($_GET['edit']) ? 'Editar Formulari' : 'Nou Formulari' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($_GET['edit']) ? route('forms.update', $_GET['edit']) : route('forms.store') }}" 
                          method="POST" 
                          class="needs-validation" 
                          novalidate>
                        @csrf
                        @if (isset($_GET['edit']))
                            @method('PUT')
                        @endif

                        <div class="mb-4">
                            <label for="title" class="form-label fw-semibold">Títol</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-heading text-primary"></i>
                                </span>
                                <input type="text" 
                                       name="title" 
                                       id="title" 
                                       class="form-control" 
                                       value="{{ isset($_GET['edit']) ? $forms->firstWhere('id', $_GET['edit'])->title : '' }}" 
                                       placeholder="Introduïu el títol del formulari"
                                       required>
                            </div>
                            <div class="form-text">El títol ha de ser descriptiu i clar.</div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-semibold">Descripció</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-align-left text-primary"></i>
                                </span>
                                <textarea 
                                       name="description" 
                                       id="description" 
                                       class="form-control" 
                                       placeholder="Descripció opcional del formulari"
                                       rows="3">{{ isset($_GET['edit']) ? $forms->firstWhere('id', $_GET['edit'])->description : '' }}</textarea>
                            </div>
                            <div class="form-text">Una breu descripció de l'objectiu del formulari.</div>
                        </div>

                        <div class="mb-4">
                            <label for="date_limit" class="form-label fw-semibold">Data límit</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-calendar-alt text-primary"></i>
                                </span>
                                <input type="date" 
                                       name="date_limit" 
                                       id="date_limit" 
                                       class="form-control" 
                                       value="{{ isset($_GET['edit']) ? $forms->firstWhere('id', $_GET['edit'])->date_limit : '' }}" 
                                       required>
                            </div>
                            <div class="form-text">Data màxima per a completar el formulari.</div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary py-2">
                                <i class="fas fa-save me-2"></i> 
                                {{ isset($_GET['edit']) ? 'Actualitzar' : 'Crear Formulari' }}
                            </button>

                            @if (isset($_GET['edit']))
                                <a href="{{ route('forms.index') }}" class="btn btn-outline-secondary py-2">
                                    <i class="fas fa-times me-2"></i> Cancel·lar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            @if(!isset($_GET['edit']))
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-circle bg-primary bg-opacity-10 me-3">
                            <i class="fas fa-lightbulb text-primary"></i>
                        </div>
                        <h5 class="card-title mb-0">Consell</h5>
                    </div>
                    <p class="text-muted mb-0">Els formularis són una eina per a recollir informació dels estudiants. Després de crear un formulari, podràs afegir-hi preguntes i assignar-lo a grups específics.</p>
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
    .form-card {
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .form-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }

    .form-id {
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