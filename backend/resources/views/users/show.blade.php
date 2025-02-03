@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalls de l'Usuari</h1>
    <div class="card mb-3">
        <div class="card-body">
            <!-- Icona d'avatar -->
            <div class="form-group mb-3 text-center">
                <i class="fas fa-user-circle fa-5x"></i>
            </div>

            <div class="form-group mb-3">
                <label for="name"><strong>Nom:</strong></label>
                <p>{{ $user->name }}</p>
            </div>
            <div class="form-group mb-3">
                <label for="last_name"><strong>Cognoms:</strong></label>
                <p>{{ $user->last_name }}</p>
            </div>
            <div class="form-group mb-3">
                <label for="email"><strong>Correu electrònic:</strong></label>
                <p>{{ $user->email }}</p>
            </div>
            <div class="form-group mb-3">
                <label for="created_at"><strong>Creat el:</strong></label>
                <p>{{ $user->created_at }}</p>
            </div>
            <div class="form-group mb-3">
                <label for="updated_at"><strong>Actualitzat el:</strong></label>
                <p>{{ $user->updated_at }}</p>
            </div>

            <!-- Mostrar Curso y División si el usuario tiene asignados -->
                    <div class="form-group mb-3">
            <label for="course"><strong>Curs:</strong></label>
            <p>{{ $user->courses->first()?->name ?? 'Sense curs assignat' }}</p>
        </div>
        <div class="form-group mb-3">
            <label for="division"><strong>Divisió:</strong></label>
            <p>{{ $user->courses->first()?->divisions->first()?->division ?? 'Sense divisió assignada' }}</p>
        </div>


        </div>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-primary w-100 mt-3">Tornar a la Llista d'Usuaris</a>
</div>
@endsection
