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

        <!-- Para profesores: selección de asignaturas -->
        @if($user->role_id == 1)
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Assignació d'Assignatures</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label><strong>Assignatures:</strong></label>
                        <div class="border rounded p-3 bg-light">
                            @foreach($subjects as $subject)
                                <div class="form-check">
                                    <input 
                                        type="checkbox" 
                                        class="form-check-input" 
                                        id="subject_{{ $subject->id }}" 
                                        name="subjects[]" 
                                        value="{{ $subject->id }}"
                                        {{ $user->subjects->contains($subject->id) ? 'checked' : '' }}>
                                    <label 
                                        class="form-check-label" 
                                        for="subject_{{ $subject->id }}" 
                                        style="margin-left: 8px;">
                                        {{ $subject->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Para profesores: cursos y divisiones -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assignació de Cursos i Divisions</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Selecciona els cursos i divisions als quals aquest professor tindrà accés:</p>
                    
                    <!-- Contenedor para las combinaciones de curso-división -->
                    <div id="course-division-container">
                        @php
                            // Obtener las asignaciones actuales
                            $courseDivisionPairs = $user->courseDivisionUsers;
                            $pairCount = 0;
                        @endphp
                        
                        @forelse($courseDivisionPairs as $pair)
                            <div class="course-division-pair border rounded p-3 mb-3">
                                @if(!$loop->first)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <strong>Assignació {{ $loop->index + 1 }}</strong>
                                    <button type="button" class="btn btn-sm btn-danger remove-pair">
                                        <i class="fas fa-times"></i> Eliminar
                                    </button>
                                </div>
                                @endif
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label><strong>Curs:</strong></label>
                                            <select name="course_division_pairs[{{ $pairCount }}][course_id]" class="form-control course-select">
                                                <option value="">Selecciona un curs</option>
                                                @foreach ($courses as $course)
                                                    <option value="{{ $course->id }}" {{ $pair->course_id == $course->id ? 'selected' : '' }}>
                                                        {{ $course->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label><strong>Divisió:</strong></label>
                                            <select name="course_division_pairs[{{ $pairCount }}][division_id]" class="form-control">
                                                <option value="">Selecciona una divisió</option>
                                                @foreach($divisions as $division)
                                                    <option value="{{ $division->id }}" {{ $pair->division_id == $division->id ? 'selected' : '' }}>
                                                        {{ $division->division }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php $pairCount++; @endphp
                        @empty
                            <!-- Si no hay asignaciones, mostrar un par vacío -->
                            <div class="course-division-pair border rounded p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label><strong>Curs:</strong></label>
                                            <select name="course_division_pairs[0][course_id]" class="form-control course-select">
                                                <option value="">Selecciona un curs</option>
                                                @foreach ($courses as $course)
                                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label><strong>Divisió:</strong></label>
                                            <select name="course_division_pairs[0][division_id]" class="form-control">
                                                <option value="">Selecciona una divisió</option>
                                                @foreach($divisions as $division)
                                                    <option value="{{ $division->id }}">{{ $division->division }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php $pairCount = 1; @endphp
                        @endforelse
                    </div>
                    
                    <!-- Botón para agregar más combinaciones -->
                    <button type="button" id="add-course-division" class="btn btn-success">
                        <i class="fas fa-plus"></i> Afegir un altre curs i divisió
                    </button>
                    
                    <input type="hidden" id="pair-count" value="{{ $pairCount }}">
                </div>
            </div>
        @endif

        <!-- Si el usuario es un estudiante, mostrar cursos y divisiones -->
        @if($user->role_id == 2)
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Assignació de Curs i Divisió</h5>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label for="course_id"><strong>Curs:</strong></label>
                        <select name="course_id" id="course_id" class="form-control">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ $user->courses->contains($course->id) ? 'selected' : '' }}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="division_id"><strong>Divisió:</strong></label>
                        <select name="division_id" id="division_id" class="form-control">
                            @php
                                // Obtener ID de la división asignada a este estudiante
                                $userDivisionId = $user->courseDivisionUsers->first()?->division_id;
                            @endphp
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" {{ $userDivisionId == $division->id ? 'selected' : '' }}>
                                    {{ $division->division }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endif

        <button type="submit" class="btn btn-primary mt-3 w-100">Actualitzar</button>
    </form>
    <a href="{{ route('users.index') }}" class="btn btn-primary mt-3 w-100">Tornar a la Llista d'Usuaris</a>
</div>
<!-- Script para manejar la adición dinámica de pares curso-división -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar botones de eliminación existentes
    const existingRemoveButtons = document.querySelectorAll('.remove-pair');
    existingRemoveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const pair = this.closest('.course-division-pair');
            if (pair) {
                pair.parentNode.removeChild(pair);
            }
        });
    });
    
    // Script para agregar nuevos pares curso-división
    const addButton = document.getElementById('add-course-division');
    const container = document.getElementById('course-division-container');
    const pairCountInput = document.getElementById('pair-count');
    
    if (addButton && container && pairCountInput) {
        let pairCount = parseInt(pairCountInput.value);
        
        addButton.addEventListener('click', function() {
            // Crear un nuevo par curso-división
            const newPair = document.createElement('div');
            newPair.className = 'course-division-pair border rounded p-3 mb-3';
            newPair.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>Nova assignació</strong>
                    <button type="button" class="btn btn-sm btn-danger remove-pair">
                        <i class="fas fa-times"></i> Eliminar
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label><strong>Curs:</strong></label>
                            <select name="course_division_pairs[${pairCount}][course_id]" class="form-control course-select">
                                <option value="">Selecciona un curs</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label><strong>Divisió:</strong></label>
                            <select name="course_division_pairs[${pairCount}][division_id]" class="form-control">
                                <option value="">Selecciona una divisió</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->division }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            `;
            
            // Agregar el nuevo par al contenedor
            container.appendChild(newPair);
            
            // Incrementar el contador de pares
            pairCount++;
            pairCountInput.value = pairCount;
            
            // Configurar el botón de eliminación
            const removeButton = newPair.querySelector('.remove-pair');
            if (removeButton) {
                removeButton.addEventListener('click', function() {
                    container.removeChild(newPair);
                });
            }
        });
    }
});
</script>
@endsection
