@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Mensajes de alerta -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> Hi ha hagut errors en el formulari:
        <ul class="mt-2 mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Cabecera con información básica -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="p-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #00ADEE 0%, #0078A0 100%);">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar">
                            <i class="fas fa-user-edit"></i>
                        </div>
                        <div>
                            <h1 class="h2 mb-1 fw-bold">Editar Usuari</h1>
                            <p class="mb-0 d-flex align-items-center">
                                <i class="fas fa-user me-2 opacity-75"></i>
                                {{ $user->name }} {{ $user->last_name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Información personal -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-id-card me-2 text-primary"></i>
                    Informació Personal
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nom</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-user text-primary"></i>
                            </span>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control" placeholder="Introdueix el nom">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Cognoms</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-user-tag text-primary"></i>
                            </span>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" class="form-control" placeholder="Introdueix els cognoms">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Correu Electrònic</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-envelope text-primary"></i>
                            </span>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" placeholder="exemple@correu.com">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="image" class="form-label">URL de la Imatge</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-image text-primary"></i>
                            </span>
                            <input type="text" id="image" name="image" value="{{ old('image', $user->image) }}" class="form-control" placeholder="https://exemple.com/imatge.jpg">
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="role_id" class="form-label">Rol</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="fas fa-user-shield text-primary"></i>
                            </span>
                            <select name="role_id" id="role_id" class="form-select">
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Para profesores: selección de asignaturas -->
        <div id="professor-options" class="card border-0 shadow-sm mb-4 {{ $user->role_id == 1 ? '' : 'd-none' }}">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex align-items-center">
                    <span class="flex-shrink-0 rounded-circle bg-primary-subtle p-2 me-2">
                        <i class="fas fa-book text-primary"></i>
                    </span>
                    <h5 class="mb-0 fw-bold">Assignació d'Assignatures</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="mb-2 fw-medium">Selecciona les assignatures:</label>
                    <div class="subjects-container p-3 rounded border bg-light">
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
                            @foreach($subjects as $subject)
                            <div class="col">
                                <div class="form-check custom-checkbox">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="subject_{{ $subject->id }}"
                                        name="subjects[]"
                                        value="{{ $subject->id }}"
                                        {{ $user->subjects->contains($subject->id) ? 'checked' : '' }}>
                                    <label
                                        class="form-check-label"
                                        for="subject_{{ $subject->id }}">
                                        {{ $subject->name }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Para profesores: cursos y divisiones -->
        <div id="professor-options-courses" class="card border-0 shadow-sm mb-4 {{ $user->role_id == 1 ? '' : 'd-none' }}">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex align-items-center">
                    <span class="flex-shrink-0 rounded-circle bg-primary-subtle p-2 me-2">
                        <i class="fas fa-chalkboard-teacher text-primary"></i>
                    </span>
                    <h5 class="mb-0 fw-bold">Assignació de Cursos i Divisions</h5>
                </div>
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
                    <div class="course-division-pair border rounded p-3 mb-3 bg-light">
                        @if(!$loop->first)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary rounded-pill px-3 py-2">Assignació {{ $loop->index + 1 }}</span>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill remove-pair">
                                <i class="fas fa-times"></i> Eliminar
                            </button>
                        </div>
                        @endif
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Curs:</label>
                                <select name="course_division_pairs[{{ $pairCount }}][course_id]" class="form-select course-select">
                                    <option value="">Selecciona un curs</option>
                                    @foreach ($courses as $course)
                                    <option value="{{ $course->id }}" {{ $pair->course_id == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Divisió:</label>
                                <select name="course_division_pairs[{{ $pairCount }}][division_id]" class="form-select">
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
                    @php $pairCount++; @endphp
                    @empty
                    <!-- Si no hay asignaciones, mostrar un par vacío -->
                    <div class="course-division-pair border rounded p-3 mb-3 bg-light">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Curs:</label>
                                <select name="course_division_pairs[0][course_id]" class="form-select course-select">
                                    <option value="">Selecciona un curs</option>
                                    @foreach ($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Divisió:</label>
                                <select name="course_division_pairs[0][division_id]" class="form-select">
                                    <option value="">Selecciona una divisió</option>
                                    @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->division }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @php $pairCount = 1; @endphp
                    @endforelse
                </div>

                <!-- Botón para agregar más combinaciones -->
                <button type="button" id="add-course-division" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i> Afegir un altre curs i divisió
                </button>

                <input type="hidden" id="pair-count" value="{{ $pairCount }}">
            </div>
        </div>

        <!-- Si el usuario es un tutor, mostrar selección de un solo curso y división -->
        <div id="tutor-options" class="card border-0 shadow-sm mb-4 {{ $user->role_id == 4 ? '' : 'd-none' }}">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex align-items-center">
                    <span class="flex-shrink-0 rounded-circle bg-warning-subtle p-2 me-2">
                        <i class="fas fa-user-tie text-warning"></i>
                    </span>
                    <h5 class="mb-0 fw-bold">Assignació de Curs i Divisió (Tutor)</h5>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Un tutor només pot ser assignat a un curs i divisió:</p>

                @php
                // Obtener la asignación actual para tutores
                $tutorAssignment = $user->courseDivisionUsers->first();
                @endphp

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="tutor_course_id" class="form-label fw-medium">Curs:</label>
                        <select name="course_division_pairs[0][course_id]" id="tutor_course_id" class="form-select">
                            <option value="">Selecciona un curs</option>
                            @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ $tutorAssignment && $tutorAssignment->course_id == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="tutor_division_id" class="form-label fw-medium">Divisió:</label>
                        <select name="course_division_pairs[0][division_id]" id="tutor_division_id" class="form-select">
                            <option value="">Selecciona una divisió</option>
                            @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ $tutorAssignment && $tutorAssignment->division_id == $division->id ? 'selected' : '' }}>
                                {{ $division->division }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="alert alert-info d-flex align-items-center mt-3">
                    <i class="fas fa-info-circle fs-5 me-3"></i>
                    <span>El tutor només tindrà accés al curs i divisió seleccionats.</span>
                </div>
            </div>
        </div>

        <!-- Si el usuario es un estudiante, mostrar cursos y divisiones -->
        <div id="student-options" class="card border-0 shadow-sm mb-4 {{ $user->role_id == 2 ? '' : 'd-none' }}">
            <div class="card-header bg-white py-3 border-0">
                <div class="d-flex align-items-center">
                    <span class="flex-shrink-0 rounded-circle bg-success-subtle p-2 me-2">
                        <i class="fas fa-user-graduate text-success"></i>
                    </span>
                    <h5 class="mb-0 fw-bold">Assignació de Curs i Divisió</h5>
                </div>
            </div>
            <div class="card-body">
                @php
                // Obtener la asignación actual para estudiantes
                $studentAssignment = $user->courseDivisionUsers->first();
                @endphp
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="student_course_id" class="form-label fw-medium">Curs:</label>
                        <select name="course_division_pairs[0][course_id]" id="student_course_id" class="form-select">
                            <option value="">Selecciona un curs</option>
                            @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ $studentAssignment && $studentAssignment->course_id == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="student_division_id" class="form-label fw-medium">Divisió:</label>
                        <select name="course_division_pairs[0][division_id]" id="student_division_id" class="form-select">
                            <option value="">Selecciona una divisió</option>
                            @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ $studentAssignment && $studentAssignment->division_id == $division->id ? 'selected' : '' }}>
                                {{ $division->division }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="alert alert-info d-flex align-items-center mt-3">
                    <i class="fas fa-info-circle fs-5 me-3"></i>
                    <span>L'alumne només serà assignat al curs i divisió seleccionats.</span>
                </div>
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="d-grid gap-3">
            <button type="submit" class="btn btn-primary py-2 fw-medium">
                <i class="fas fa-save me-2"></i> Guardar Canvis
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-primary py-2 fw-medium">
                <i class="fas fa-arrow-left me-2"></i> Tornar a la Llista d'Usuaris
            </a>
        </div>
    </form>
</div>

<style>
    /* Estilo para el avatar de usuario */
    .user-avatar {
        width: 64px;
        height: 64px;
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .user-avatar i {
        font-size: 2rem;
        color: white;
    }

    /* Estilos para input, select y buttons */
    .form-control,
    .form-select {
        padding: 0.6rem 0.75rem;
        border-color: #dee2e6;
        box-shadow: none;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: rgba(0, 173, 238, 0.5);
        box-shadow: 0 0 0 0.2rem rgba(0, 173, 238, 0.25);
    }

    .btn {
        border-radius: 0.375rem;
        font-weight: 500;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: #00ADEE;
        border-color: #00ADEE;
    }

    .btn-primary:hover {
        background-color: #0089BE;
        border-color: #0089BE;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-outline-primary {
        color: #00ADEE;
        border-color: #00ADEE;
    }

    .btn-outline-primary:hover {
        background-color: #00ADEE;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Fondos de colores suaves */
    .bg-primary-subtle {
        background-color: rgba(0, 173, 238, 0.15);
    }

    .bg-success-subtle {
        background-color: rgba(25, 135, 84, 0.15);
    }

    .bg-warning-subtle {
        background-color: rgba(255, 193, 7, 0.15);
    }

    /* Mejora para checkboxes */
    .custom-checkbox {
        padding: 0.5rem 0.75rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
    }

    .custom-checkbox:hover {
        background-color: rgba(0, 173, 238, 0.05);
    }

    .form-check-input:checked {
        background-color: #00ADEE;
        border-color: #00ADEE;
    }

    /* Estilos para curso-división */
    .course-division-pair {
        transition: all 0.3s ease;
        border-radius: 0.5rem;
    }

    .course-division-pair:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .user-avatar {
            width: 54px;
            height: 54px;
        }

        .user-avatar i {
            font-size: 1.75rem;
        }

        h1.h2 {
            font-size: 1.4rem;
        }

        .form-control,
        .form-select {
            font-size: 1rem;
            padding: 0.5rem 0.75rem;
        }

        .card-header {
            padding: 0.75rem 1rem;
        }

        .card-body {
            padding: 1rem;
        }

        .subjects-container {
            padding: 0.75rem !important;
        }
    }

    @media (max-width: 576px) {
        .p-4 {
            padding: 1rem !important;
        }

        .user-avatar {
            width: 48px;
            height: 48px;
        }

        .user-avatar i {
            font-size: 1.5rem;
        }

        h1.h2 {
            font-size: 1.3rem;
        }
    }
</style>

<!-- Script para manejar la adición dinámica de pares curso-división y mostrar las opciones según el rol -->
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
                newPair.className = 'course-division-pair border rounded p-3 mb-3 bg-light';
                newPair.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-primary rounded-pill px-3 py-2">Nova assignació</span>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill remove-pair">
                        <i class="fas fa-times"></i> Eliminar
                    </button>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Curs:</label>
                        <select name="course_division_pairs[${pairCount}][course_id]" class="form-select course-select">
                            <option value="">Selecciona un curs</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Divisió:</label>
                        <select name="course_division_pairs[${pairCount}][division_id]" class="form-select">
                            <option value="">Selecciona una divisió</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}">{{ $division->division }}</option>
                            @endforeach
                        </select>
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

        // Mostrar/ocultar opciones según el rol seleccionado
        const roleSelect = document.getElementById('role_id');
        const professorOptions = document.getElementById('professor-options');
        const professorOptionsCourses = document.getElementById('professor-options-courses');
        const tutorOptions = document.getElementById('tutor-options');
        const studentOptions = document.getElementById('student-options');

        roleSelect.addEventListener('change', function() {
            const selectedRole = this.value;

            // Ocultar todas las opciones primero
            professorOptions.classList.add('d-none');
            professorOptionsCourses.classList.add('d-none');
            tutorOptions.classList.add('d-none');
            studentOptions.classList.add('d-none');

            // Mostrar las opciones según el rol seleccionado
            if (selectedRole == 1) { // Profesor
                professorOptions.classList.remove('d-none');
                professorOptionsCourses.classList.remove('d-none');
            } else if (selectedRole == 4 || selectedRole == 5) { // Tutor u Orientador
                tutorOptions.classList.remove('d-none');
            } else if (selectedRole == 2) { // Estudiante
                studentOptions.classList.remove('d-none');
            }
        });

        // Validación del formulario antes de enviar
        const form = document.querySelector('form');
        form.addEventListener('submit', function(event) {
            // Prevenir el envío por defecto para validación
            event.preventDefault();
            
            // Verificar que hay al menos un curso y división según el rol
            const selectedRole = parseInt(roleSelect.value);
            let isValid = true;
            
            // Eliminar selectores de cursos y divisiones vacíos para evitar errores de validación
            const allSelectsCD = document.querySelectorAll('select[name^="course_division_pairs"]');
            allSelectsCD.forEach(select => {
                // Si el campo está vacío, deshabilitarlo para que no se envíe
                if (!select.value) {
                    select.disabled = true;
                }
            });
            
            // Crear alerta
            function showAlert(message) {
                // Eliminar alertas previas
                const existingAlerts = document.querySelectorAll('.alert-danger:not(.d-none)');
                existingAlerts.forEach(el => el.remove());
                
                // Crear nueva alerta
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show mb-4';
                alertDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle me-2"></i> ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                form.prepend(alertDiv);
                window.scrollTo(0, 0);
            }
            
            if (selectedRole === 1) { // Profesor
                // Verificar si hay al menos un par curso-división válido
                const courseDivPairs = [...document.querySelectorAll('#professor-options-courses .course-division-pair')];
                const validPair = courseDivPairs.some(pair => {
                    const courseSelect = pair.querySelector('select[name$="[course_id]"]');
                    const divisionSelect = pair.querySelector('select[name$="[division_id]"]');
                    return courseSelect && divisionSelect && courseSelect.value && divisionSelect.value;
                });
                
                if (!validPair) {
                    showAlert('Cal seleccionar almenys un curs i divisió per al professor.');
                    isValid = false;
                }
            } else if (selectedRole === 2) { // Alumno
                const studentCourseSelect = document.getElementById('student_course_id');
                const studentDivisionSelect = document.getElementById('student_division_id');
                
                if (!studentCourseSelect.value || !studentDivisionSelect.value) {
                    showAlert('Cal seleccionar un curs i divisió per a l\'alumne.');
                    isValid = false;
                } else {
                    // Habilitar para asegurar que se envíen
                    studentCourseSelect.disabled = false;
                    studentDivisionSelect.disabled = false;
                }
            } else if (selectedRole === 4 || selectedRole === 5) { // Tutor u Orientador
                const tutorCourseSelect = document.getElementById('tutor_course_id');
                const tutorDivisionSelect = document.getElementById('tutor_division_id');
                
                if (!tutorCourseSelect.value || !tutorDivisionSelect.value) {
                    showAlert('Cal seleccionar un curs i divisió per al tutor/orientador.');
                    isValid = false;
                } else {
                    // Habilitar para asegurar que se envíen
                    tutorCourseSelect.disabled = false;
                    tutorDivisionSelect.disabled = false;
                }
            }
            
            if (!isValid) {
                return;
            }
            
            // Si pasa todas las validaciones, mostrar indicador de carga y enviar
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processant...';
            
            form.submit();
        });
    });
</script>
@endsection