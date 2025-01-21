@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Encapçalament i botó de retorn -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-5 mb-0">Gestió d'Assignatures</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Tornar al Panell
        </a>
    </div>

    <!-- Missatges d'èxit -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tanca"></button>
        </div>
    @endif

    <!-- Grid de dues columnes -->
    <div class="row">
        <!-- Llista d'assignatures -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2" style="color: var(--primary-color)"></i>
                        Llista d'Assignatures
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
                                    <th class="text-end px-4">Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($subjects as $subject)
                                    <tr>
                                        <td class="px-4">{{ $subject->id }}</td>
                                        <td>{{ $subject->name }}</td>
                                        <td>{{ $subject->description }}</td>
                                        <td class="text-end px-4">
                                            <!-- Editar -->
                                            <a href="{{ route('subjects.index', ['edit' => $subject->id]) }}" 
                                               class="btn btn-sm btn-warning me-2 mb-2 w-100">
                                                <i class="fas fa-edit me-1"></i>
                                                Editar
                                            </a>
                                            <!-- Eliminar -->
                                            <form action="{{ route('subjects.destroy', $subject->id) }}" 
                                                  method="POST" 
                                                  class="d-inline w-100">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger w-100" 
                                                        onclick="return confirm('Estàs segur que vols eliminar aquesta assignatura?')">
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

        <!-- Formulari per Crear o Editar Assignatures -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-{{ isset($_GET['edit']) ? 'edit' : 'plus' }} me-2" 
                           style="color: var(--primary-color)"></i>
                        {{ isset($_GET['edit']) ? 'Editar Assignatura' : 'Crear Nova Assignatura' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($_GET['edit']) ? route('subjects.update', $_GET['edit']) : route('subjects.store') }}" 
                          method="POST">
                        @if(isset($_GET['edit']))
                            @method('PUT')
                        @endif
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom de l'Assignatura</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="{{ isset($_GET['edit']) ? $subjects->firstWhere('id', $_GET['edit'])->name : '' }}" 
                                   required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripció</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="description" 
                                   name="description" 
                                   value="{{ isset($_GET['edit']) ? $subjects->firstWhere('id', $_GET['edit'])->description : '' }}">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                {{ isset($_GET['edit']) ? 'Actualitzar' : 'Desar' }}
                            </button>
                            
                            @if(isset($_GET['edit']))
                                <a href="{{ route('subjects.index') }}" class="btn btn-outline-secondary">
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
