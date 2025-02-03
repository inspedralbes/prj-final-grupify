@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Títol i botó de tornada -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-5">Gestió de Preguntes</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i> Tornar al Tauler
        </a>
    </div>

    <!-- Missatges d'èxit -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tancar"></button>
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tancar"></button>
        </div>
    @endif

    <!-- Contingut principal -->
    <div class="row">
        <!-- Llista de preguntes -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Llista de Preguntes</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Títol</th>
                                <th>Formulari</th>
                                <th class="text-end">Accions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($questions as $question)
                                <tr>
                                    <td>{{ $question->id }}</td>
                                    <td>{{ $question->title }}</td>
                                    <td>{{ $question->form->title }}</td>
                                    <td class="text-end">
                                        <!-- Botó Editar -->
                                        <a href="{{ route('questions.index', ['edit' => $question->id]) }}" class="btn btn-warning btn-sm me-2">
                                            <i class="fas fa-edit me-1"></i> Editar
                                        </a>

                                        <!-- Formulari Eliminar -->
                                        <form action="{{ route('questions.destroy', $question->id) }}" method="POST" class="d-inline">
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
                        {{ isset($_GET['edit']) ? 'Editar Pregunta' : 'Nova Pregunta' }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ isset($_GET['edit']) ? route('questions.update', $_GET['edit']) : route('questions.store') }}" method="POST">
                        @csrf
                        @if (isset($_GET['edit']))
                            @method('PUT')
                        @endif

                        <div class="mb-3">
                            <label for="title" class="form-label">Títol</label>
                            <input type="text" name="title" id="title" class="form-control" value="{{ isset($_GET['edit']) ? $questions->firstWhere('id', $_GET['edit'])->title : '' }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="form_id" class="form-label">Formulari</label>
                            <select name="form_id" id="form_id" class="form-control" required>
                                <option value="" disabled {{ isset($_GET['edit']) && !$questions->firstWhere('id', $_GET['edit'])->form_id ? 'selected' : '' }}>
                                    Selecciona un formulari
                                </option>
                                @foreach ($forms as $form)
                                    <option value="{{ $form->id }}" 
                                        {{ isset($_GET['edit']) && $questions->firstWhere('id', $_GET['edit'])->form_id == $form->id ? 'selected' : '' }}>
                                        {{ $form->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> {{ isset($_GET['edit']) ? 'Actualitzar' : 'Desar' }}
                            </button>

                            @if (isset($_GET['edit']))
                                <a href="{{ route('questions.index') }}" class="btn btn-outline-secondary mt-2">
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
