@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Usuaris</h1>
    <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">Afegir Nou Usuari</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Cognom</th>
                <th>Correu electrònic</th>
                <th>Rol</th>
                <th>Accions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->last_name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role ? $user->role->name : 'Sense rol assignat' }}</td>

                <td>
                    <!-- Botó de visualització -->
                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-sm">Veure</a>
                    
                    <!-- Botó d'edició -->
                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">Editar</a>

                    <!-- Botó d'eliminació -->
                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Estàs segur?')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No s'han trobat usuaris</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Botó de tornar al dashboard -->
    <a href="{{ route('dashboard') }}" class="btn btn-primary mt-3 w-100">Tornar al Dashboard</a>
</div>
@endsection
