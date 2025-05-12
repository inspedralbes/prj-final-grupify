@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Encapçalament i botó de retorn -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-5 mb-0">Gestió de Rols</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Tornar al panell
        </a>
    </div>

    <!-- Missatges d'èxit o error -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Errors de validació -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Llista de Rols -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2" style="color: var(--primary-color)"></i>
                        Llista de Rols
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">ID</th>
                                    <th>Nom</th>
                                    <th>Descripció</th>
                                    <th class="text-end">Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td class="px-4">{{ $role->id }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->description ?? 'Sense descripció' }}</td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end align-items-center">
                                                <!-- Botó Editar -->
                                                <a href="{{ route('roles.index', ['edit' => $role->id]) }}" class="btn btn-warning btn-sm me-2">
                                                    <i class="fas fa-edit me-1"></i> Editar
                                                </a>

                                                <!-- Formulari Eliminar -->
                                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Estàs segur d'eliminar aquest rol?')">
                                                        <i class="fas fa-trash-alt me-1"></i> Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulari per Crear o Editar -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-{{ isset($_GET['edit']) ? 'edit' : 'plus' }} me-2" 
                           style="color: var(--primary-color)"></i>
                        {{ isset($_GET['edit']) ? 'Editar Rol' : 'Crear Nou Rol' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($_GET['edit']) ? route('roles.update', $_GET['edit']) : route('roles.store') }}" 
                          method="POST">
                        @if(isset($_GET['edit']))
                            @method('PUT')
                        @endif
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom del Rol</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="{{ isset($_GET['edit']) ? $roles->firstWhere('id', $_GET['edit'])->name : '' }}" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripció del Rol</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ isset($_GET['edit']) ? $roles->firstWhere('id', $_GET['edit'])->description : '' }}</textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                {{ isset($_GET['edit']) ? 'Actualitzar' : 'Desar' }}
                            </button>

                            @if(isset($_GET['edit']))
                                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Cancel·lar
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection