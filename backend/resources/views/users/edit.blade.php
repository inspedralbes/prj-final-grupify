@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Usuari</h1>
    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="name">Nom</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label for="last_name">Cognoms</label>
            <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label for="email">Correu Electrònic</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label for="image">URL de la Imatge</label>
            <input type="text" id="image" name="image" value="{{ old('image', $user->image) }}" class="form-control">
        </div>

        <div class="form-group mb-3">
            <label for="role_id">Rol</label>
            <select name="role_id" id="role_id" class="form-control">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Si el usuario es un estudiante, mostrar cursos y divisiones -->
        @if($user->role_id == 2)
            <div class="form-group mb-3">
                <label for="course_id">Curso</label>
                <select name="course_id" id="course_id" class="form-control">
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ $user->courses->contains($course->id) ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="division_id">División</label>
                <select name="division_id" id="division_id" class="form-control">
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" {{ $user->courses->first()?->divisions->contains($division->id) ? 'selected' : '' }}>
                            {{ $division->division }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <button type="submit" class="btn btn-primary mt-3 w-100">Actualitzar</button>
    </form>
    <a href="{{ route('users.index') }}" class="btn btn-primary mt-3 w-100">Tornar a la Llista d'Usuaris</a>
</div>
@endsection
