
@extends('layouts.app')


@section('content')
<div class="container">
    <h1>Crear nou usuari</h1>
    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="name">Nom:</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="form-group">
            <label for="last_name">Cognom:</label>
            <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
        </div>


        <div class="form-group">
            <label for="email">Gmail:</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
        </div>


        <div class="form-group">
            <label for="password">Contrasenya:</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>


        <div class="form-group">
            <label for="image">Imatge URL:</label>
            <input type="url" class="form-control" id="image" name="image" value="{{ old('image') }}">
        </div>


        <div class="form-group">
            <label for="role">Rol:</label>
            <select name="role_id" id="role" class="form-control" required>
                <option value="">Selecciona un rol</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Subject Assignment (Only for Teachers) -->
        <div class="mb-3" id="subject-group" style="display: none;">
            <label class="form-label"><strong>Asignaturas:</strong></label>
            <div class="form-check-container border rounded p-3 bg-light">
                @foreach ($subjects as $subject)
                    <div class="form-check">
                        <input 
                            type="checkbox" 
                            class="form-check-input" 
                            id="subject_{{ $subject->id }}" 
                            name="subjects[]" 
                            value="{{ $subject->id }}">
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

        <!-- Course and Division Assignment (For Teachers) -->
        <div id="teacher-course-division" style="display: none;">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Assignació de Cursos i Divisions</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Selecciona els cursos i divisions als quals aquest professor tindrà accés:</p>
                    
                    <!-- Contenedor para las combinaciones de curso-división -->
                    <div id="course-division-container">
                        <!-- Primera combinación (siempre visible) -->
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
                    </div>
                    
                    <!-- Botón para agregar más combinaciones -->
                    <button type="button" id="add-course-division" class="btn btn-success">
                        <i class="fas fa-plus"></i> Afegir un altre curs i divisió
                    </button>
                </div>
            </div>
        </div>

        <!-- Course and Division Assignment (Only for Tutors) -->
        <div id="tutor-fields" style="display: none;">
            <div class="card mb-3">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0">Assignació de Curs i Divisió (Tutor)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Un tutor només pot ser assignat a un curs i divisió:</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="tutor_course_id"><strong>Curs:</strong></label>
                                <select name="course_division_pairs[0][course_id]" id="tutor_course_id" class="form-control">
                                    <option value="">Selecciona un curs</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="tutor_division_id"><strong>Divisió:</strong></label>
                                <select name="course_division_pairs[0][division_id]" id="tutor_division_id" class="form-control">
                                    <option value="">Selecciona una divisió</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->division }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> El tutor només tindrà accés al curs i divisió seleccionats.
                    </div>
                </div>
            </div>
        </div>

        <!-- Course and Division Assignment (Only for Students) -->
        <div id="student-fields" style="display: none;">
            <div class="form-group">
                <label for="course">Curs:</label>
                <select name="courses[]" id="course" class="form-control" multiple>
                <option value="">Selecciona un curs</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>


            <!-- Division Selection for Students -->
            <div class="form-group" id="division-group">
            <label for="divisions">Divisions</label>
            <select name="divisions[]" id="divisions" class="form-control" multiple>
                @foreach($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->division }}</option>
                @endforeach
            </select>
        </div>
        </div>

        

        <button type="submit" class="btn btn-primary mt-3">Crear Usuari</button>
    </form>
    <a href="{{ route('users.index') }}" class="btn btn-primary mt-3">Tornar a la llista d'usuaris</a>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('role');
    const subjectGroup = document.getElementById('subject-group');
    const studentFields = document.getElementById('student-fields');
    const divisionGroup = document.getElementById('division-group');
    const teacherCourseDiv = document.getElementById('teacher-course-division');
    const tutorFields = document.getElementById('tutor-fields');


    roleSelect.addEventListener('change', function () {
        const selectedRole = parseInt(this.value);


        // Mostrar/ocultar campos en función del rol
        subjectGroup.style.display = 'none';
        studentFields.style.display = 'none';
        divisionGroup.style.display = 'none';
        teacherCourseDiv.style.display = 'none';
        tutorFields.style.display = 'none';


        if (selectedRole === 1) { // Rol de "Profesor"
            subjectGroup.style.display = 'block';
            teacherCourseDiv.style.display = 'block'; // Mostrar selector de cursos/divisiones para profesores
        } else if (selectedRole === 2) { // Rol de "Alumno"
            studentFields.style.display = 'block';
            divisionGroup.style.display = 'block';
        } else if (selectedRole === 4) { // Rol de "Tutor" (asumiendo que su ID es 4)
            tutorFields.style.display = 'block';
        }
        console.log("Rol seleccionado:", selectedRole);
    });
});


</script>

<!-- Script para manejar la adición dinámica de pares curso-división -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Script existente para manejar cambios de rol
    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        roleSelect.dispatchEvent(new Event('change'));
    }
    
    // Nuevo script para agregar pares curso-división
    const addButton = document.getElementById('add-course-division');
    const container = document.getElementById('course-division-container');
    
    if (addButton && container) {
        let pairCount = 1; // Comenzamos en 1 porque ya tenemos un par inicial (índice 0)
        
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const errorDiv = document.createElement('div');
        errorDiv.classList.add('invalid-feedback');
        passwordInput.parentNode.appendChild(errorDiv);

        passwordInput.addEventListener('input', function () {
            if (passwordInput.value.length < 8) {
                passwordInput.classList.add('is-invalid');
                errorDiv.textContent = 'La contrasenya ha de tenir almenys 8 caràcters.';
            } else {
                passwordInput.classList.remove('is-invalid');
                errorDiv.textContent = '';
            }
        });
    });
</script>

@endsection
