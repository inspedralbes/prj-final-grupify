@extends('layouts.app')
@section('content')
<div class="container">
    <!-- Card para el Título -->
    <div class="mb-4">
        <div class="card">
            <div class="card-body text-center">
                <h1 class="text-3xl font-bold text-gray-800 mb-4">CRUD</h1>
                <p class="text-muted">Selecciona una secció per administrar</p>
            </div>
        </div>
    </div>

    <!-- Cards de componentes -->
    <div class="row g-4">
        <!-- Roles -->
        <div class="col-md-4 col-lg-4">
            <a href="{{ route('roles.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-user-tag fa-2x mb-3" style="color: var(--primary-color)"></i>
                        <h5 class="card-title">Rols</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cursos -->
        <div class="col-md-4 col-lg-4">
            <a href="{{ route('courses.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-graduation-cap fa-2x mb-3" style="color: var(--primary-color)"></i>
                        <h5 class="card-title">Cursos</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Asignaturas -->
        <div class="col-md-4 col-lg-4">
            <a href="{{ route('subjects.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-book fa-2x mb-3" style="color: var(--primary-color)"></i>
                        <h5 class="card-title">Assignatures</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Usuarios -->
        <div class="col-md-4 col-lg-4">
            <a href="{{ route('users.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-users fa-2x mb-3" style="color: var(--primary-color)"></i>
                        <h5 class="card-title">Usuaris</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Grupos -->
        <div class="col-md-4 col-lg-4">
            <a href="{{ route('groups.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-layer-group fa-2x mb-3" style="color: var(--primary-color)"></i>
                        <h5 class="card-title">Grups</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Divisiones -->
        <div class="col-md-4 col-lg-4">
            <a href="{{ route('divisions.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-sitemap fa-2x mb-3" style="color: var(--primary-color)"></i>
                        <h5 class="card-title">Divisions</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Formularios -->
        <div class="col-md-4 col-lg-4">
            <a href="{{ route('forms.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-clipboard-list fa-2x mb-3" style="color: var(--primary-color)"></i>
                        <h5 class="card-title">Formularis</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Preguntas -->
        <div class="col-md-4 col-lg-4">
            <a href="{{ route('questions.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-question-circle fa-2x mb-3" style="color: var(--primary-color)"></i>
                        <h5 class="card-title">Preguntes</h5>
                    </div>
                </div>
            </a>
        </div>

        <!-- Respuestas -->
        <div class="col-md-4 col-lg-4">
            <a href="{{ route('answers.index') }}" class="text-decoration-none">
                <div class="card h-100">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-comment-dots fa-2x mb-3" style="color: var(--primary-color)"></i>
                        <h5 class="card-title">Respostes</h5>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>
@endsection
