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

            <!-- Mostrar Rol del usuario -->
            <div class="form-group mb-3">
                <label><strong>Rol:</strong></label>
                <p>{{ $user->role->name ?? 'No especificat' }}</p>
            </div>

            <!-- Mostrar cursos y divisiones de manera diferente según el rol -->
            @if($user->role_id == 1) <!-- Si es profesor -->
                <div class="form-group mb-3">
                    <label><strong>Cursos i Divisions assignats:</strong></label>
                    @if($user->courseDivisionUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Curs</th>
                                        <th>Divisió</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->courseDivisionUsers as $cdu)
                                    <tr>
                                        <td>{{ $cdu->course->name ?? 'N/A' }}</td>
                                        <td>{{ $cdu->division->division ?? 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p>Sense cursos ni divisions assignats</p>
                    @endif
                </div>

                <!-- Si es profesor, mostrar también las materias -->
                <div class="form-group mb-3">
                    <label><strong>Assignatures:</strong></label>
                    @if($user->subjects->count() > 0)
                        <ul>
                            @foreach($user->subjects as $subject)
                                <li>{{ $subject->name }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p>Sense assignatures assignades</p>
                    @endif
                </div>
            @elseif($user->role_id == 2) <!-- Si es estudiante -->
                <div class="form-group mb-3">
                    <label><strong>Curs:</strong></label>
                    <p>{{ $user->courseDivisionUsers->first()?->course->name ?? 'Sense curs assignat' }}</p>
                </div>
                <div class="form-group mb-3">
                    <label><strong>Divisió:</strong></label>
                    <p>{{ $user->courseDivisionUsers->first()?->division->division ?? 'Sense divisió assignada' }}</p>
                </div>
            @endif


        </div>
    </div>
    <a href="{{ route('users.index') }}" class="btn btn-primary w-100 mt-3">Tornar a la Llista d'Usuaris</a>
</div>
@endsection
