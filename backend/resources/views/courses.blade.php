@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Encapçalament i botó de retorn -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-5 mb-0">Gestió de Cursos</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>
            Tornar al Dashboard
        </a>
    </div>

    <!-- Missatges d'èxit -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
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

    <!-- Grid de dues columnes -->
    <div class="row">
        <!-- Llista de cursos -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2" style="color: var(--primary-color)"></i>
                        Llista de Cursos
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-4">ID</th>
                                    <th>Nom</th>
                                    <th class="text-end px-4">Accions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($courses as $course)
                                    <tr>
                                        <td class="px-4">{{ $course->id }}</td>
                                        <td>{{ $course->name }}</td>
                                        <td class="text-end px-4">
                                            <!-- Botó per editar curs -->
                                            <a href="{{ route('courses.index', ['edit' => $course->id]) }}" 
                                               class="btn btn-sm btn-warning me-2">
                                                <i class="fas fa-edit me-1"></i>
                                                Edita
                                            </a>
                                            
                                            <!-- Formulari per eliminar curs -->
                                            <form action="{{ route('courses.destroy', $course->id) }}" 
                                                  method="POST" 
                                                  class="d-inline">
                                                @method('DELETE')
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Esteu segur d eliminar aquest curs?')">
                                                    <i class="fas fa-trash-alt me-1"></i>
                                                    Elimina
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

        <!-- Formulari per crear o editar curs -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-{{ isset($_GET['edit']) ? 'edit' : 'plus' }} me-2" 
                           style="color: var(--primary-color)"></i>
                        {{ isset($_GET['edit']) ? 'Editar Curs' : 'Crear Nou Curs' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($_GET['edit']) ? route('courses.update', $_GET['edit']) : route('courses.store') }}" 
                          method="POST">
                        @if(isset($_GET['edit']))
                            @method('PUT')
                        @endif
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nom del Curs</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="{{ isset($_GET['edit']) ? $courses->firstWhere('id', $_GET['edit'])->name : '' }}" 
                                   required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                {{ isset($_GET['edit']) ? 'Actualitza' : 'Desar' }}
                            </button>

                            @if(isset($_GET['edit']))
                                <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>
                                    Cancel·la
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
