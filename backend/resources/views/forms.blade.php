@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Título y botón de regreso -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-5">Gestió de Formularis</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i> Tornar al panell
        </a>
    </div>

    <!-- Missatges d'èxit -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
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

    <!-- Contingut principal -->
    <div class="row">
        <!-- Llista de formularis -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Llista de Formularis</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Títol</th>
                                <th>Professor</th>
                                <th class="text-end">Accions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($forms as $form)
                                <tr>
                                    <td>{{ $form->id }}</td>
                                    <td>{{ $form->title }}</td>
                                    <td>{{ $form->teacher->name ?? 'Global' }}</td>
                                    <td class="text-end">
                                        <!-- Botó Editar -->
                                        <a href="{{ route('forms.index', ['edit' => $form->id]) }}" class="btn btn-warning btn-sm me-2">
                                            <i class="fas fa-edit me-1"></i> Editar
                                        </a>

                                        <!-- Formulari Eliminar -->
                                        <form action="{{ route('forms.destroy', $form->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash-alt me-1"></i> Eliminar
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

        <!-- Formulari Crear/Editar -->
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-{{ isset($_GET['edit']) ? 'edit' : 'plus' }} me-2"></i>
                        {{ isset($_GET['edit']) ? 'Editar Formulari' : 'Nou Formulari' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($_GET['edit']) ? route('forms.update', $_GET['edit']) : route('forms.store') }}" method="POST">
                        @csrf
                        @if (isset($_GET['edit']))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="title" class="form-label">Títol</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ isset($_GET['edit']) ? $forms->firstWhere('id', $_GET['edit'])->title : '' }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="date_limit" class="form-label">Data límit</label>
                            <input type="date" name="date_limit" id="date_limit" class="form-control" value="{{ isset($_GET['edit']) ? $forms->firstWhere('id', $_GET['edit'])->date_limit : '' }}" required>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> {{ isset($_GET['edit']) ? 'Actualitzar' : 'Desar' }}
                            </button>

                            @if (isset($_GET['edit']))
                                <a href="{{ route('forms.index') }}" class="btn btn-outline-secondary mt-2">
                                    <i class="fas fa-times me-2"></i> Cancel·lar
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
