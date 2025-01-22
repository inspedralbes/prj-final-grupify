
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
            <input type="password" class="form-control" id="password" name="password" required>
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
        <div class="form-group" id="subject-group" style="display: none;">
            <label for="subject">Assignatura:</label>
            <select name="subject_id" id="subject" class="form-control">
                <option value="">Selecciona una assignatura </option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                @endforeach
            </select>
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
    const divisionGroup = document.getElementById('division-group'); // Asegúrate de que este ID exista en el HTML


    roleSelect.addEventListener('change', function () {
        const selectedRole = parseInt(this.value);


        // Mostrar/ocultar campos en función del rol
        subjectGroup.style.display = 'none';
        studentFields.style.display = 'none';
        divisionGroup.style.display = 'none';  // Añadir esto para ocultar divisiones por defecto


        if (selectedRole === 1) { // Rol de "Profesor"
            subjectGroup.style.display = 'block';
        } else if (selectedRole === 2) { // Rol de "Alumno"
            studentFields.style.display = 'block';
            divisionGroup.style.display = 'block'; // Mostrar divisiones cuando el rol es "Alumno"
        }
        console.log("Rol seleccionado:", selectedRole);
    });
});


</script>

<!-- Agregar este script al final -->
<script>
document.getElementById('role').addEventListener('change', function() {
    const courseDivisionFields = document.getElementById('courseDivisionFields');
    // Mostrar campos solo para profesores (1) y alumnos (2)
    if (this.value == '1' || this.value == '2') {
        courseDivisionFields.style.display = 'block';
    } else {
        courseDivisionFields.style.display = 'none';
    }
});

// Ejecutar el evento change al cargar la página para manejar el valor inicial
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    roleSelect.dispatchEvent(new Event('change'));
});
</script>
@endsection
