@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Encapçalament i botó de retorn -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-5 mb-0">Gestió de Grups</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Tornar al Tauler de Control
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

    <!-- Llista de Grups -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2" style="color: var(--primary-color)"></i>
                        Llista de Grups
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">ID</th>
                                    <th>Nom</th>
                                    <th>Integrants</th>
                                    <th class="text-end px-4">Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groups as $group)
                                    <tr>
                                        <td class="px-4">{{ $group->id }}</td>
                                        <td>{{ $group->name }}</td>
                                        <td>
                                            @if ($group->users->isEmpty())
                                                <span class="text-gray-500">Sense integrants</span>
                                            @else
                                                <ul class="list-disc list-inside">
                                                    @foreach ($group->users as $user)
                                                        <li>{{ $user->name }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        <td class="text-end px-4">
                                            <a href="{{ route('groups.index', ['edit' => $group->id]) }}" 
                                               class="btn btn-sm btn-warning me-2">
                                                <i class="fas fa-edit me-1"></i>
                                                Editar
                                            </a>
                                            <form action="{{ route('groups.destroy', $group->id) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Estàs segur que vols eliminar aquest grup?')">
                                                    <i class="fas fa-trash-alt me-1"></i>
                                                    Eliminar
                                                </button>
                                            </form>
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
                        {{ isset($_GET['edit']) ? 'Editar Grup' : 'Crear Nou Grup' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($_GET['edit']) ? route('groups.update', $_GET['edit']) : route('groups.store') }}" 
                          method="POST">
                        @if(isset($_GET['edit']))
                            @method('PUT')
                        @endif
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom del Grup</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="{{ isset($_GET['edit']) ? $groups->firstWhere('id', $_GET['edit'])->name : '' }}" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripció del Grup</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="description" 
                                   name="description" 
                                   value="{{ isset($_GET['edit']) ? $groups->firstWhere('id', $_GET['edit'])->description : '' }}">
                        </div>

                        <div class="mb-3">
                            <label for="number_of_students" class="form-label">Nombre d'Estudiants</label>
                            <input type="number" 
                                   class="form-control" 
                                   id="number_of_students" 
                                   name="number_of_students" 
                                   value="{{ isset($_GET['edit']) ? $groups->firstWhere('id', $_GET['edit'])->number_of_students : '' }}" 
                                   required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                {{ isset($_GET['edit']) ? 'Actualitzar' : 'Desar' }}
                            </button>

                            @if(isset($_GET['edit']))
                                <a href="{{ route('groups.index') }}" class="btn btn-outline-secondary">
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