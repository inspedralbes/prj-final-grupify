@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Tarjeta de encabezado -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="p-4 bg-gradient-primary text-white" style="background: linear-gradient(135deg, #00ADEE 0%, #0078A0 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h2 mb-2 fw-bold">Crear Nou Usuari</h1>
                        <p class="mb-0 opacity-75">Introdueix les dades per afegir un nou usuari al sistema</p>
                    </div>
                    <a href="{{ route('users.index') }}" class="btn btn-light rounded-pill shadow-sm px-4 py-2">
                        <i class="fas fa-arrow-left me-2"></i>
                        Tornar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mostrar errores de validación -->
    @if ($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Alertas de sesión -->
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    <!-- Contenedor de alertas dinámicas -->
    <div id="alert-container"></div>

    <form action="{{ route('users.store') }}" method="POST" id="userForm">
        @csrf
        <div class="row">
            <!-- Información básica -->
            <div class="col-md-12">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light py-3">
                        <h3 class="card-title mb-0 h5">Informació Bàsica</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nom:</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Cognoms:</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Correu electrònic:</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">Contrasenya:</label>
                                <input type="password" class="form-control" id="password" name="password" minlength="8">
                            </div>
                            <div class="col-md-6">
                                <label for="image" class="form-label">URL d'imatge (opcional):</label>
                                <input type="url" class="form-control" id="image" name="image" value="{{ old('image') }}">
                                <div class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i> Introdueix la URL d'una imatge de perfil.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Rol:</label>
                                <select name="role_id" id="role" class="form-select">
                                    <option value="">Selecciona un rol</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección para Profesores -->
            <div class="col-md-12 role-section" id="teacher-section" style="display: none;">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white py-3">
                        <h3 class="card-title mb-0 h5">Informació del Professor</h3>
                    </div>
                    <div class="card-body p-4">
                        <!-- Asignaturas -->
                        <div class="mb-4">
                            <label class="form-label"><strong>Assignatures:</strong></label>
                            <div class="border rounded p-3 bg-light">
                                <div class="row">
                                @foreach ($subjects as $subject)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="subject_{{ $subject->id }}" name="subjects[]" value="{{ $subject->id }}" {{ (is_array(old('subjects')) && in_array($subject->id, old('subjects'))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="subject_{{ $subject->id }}">
                                                {{ $subject->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            </div>
                            <div class="form-text text-muted mt-2">
                                <i class="fas fa-info-circle me-1"></i> Selecciona les assignatures que impartirà el professor.
                            </div>
                        </div>

                        <!-- Cursos y divisiones -->
                        <div class="mt-4">
                            <label class="form-label"><strong>Assignació de Cursos i Divisions:</strong></label>
                            <div id="teacher-course-division-container">
                                <div class="course-division-pair border rounded p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="mb-3">
                                                <label class="form-label">Curs:</label>
                                                <select name="course_division_pairs[0][course_id]" class="form-select">
                                                    <option value="">Selecciona un curs</option>
                                                    @foreach ($courses as $course)
                                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="mb-3">
                                                <label class="form-label">Divisió:</label>
                                                <select name="course_division_pairs[0][division_id]" class="form-select">
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
                            
                            <button type="button" id="add-teacher-course-division" class="btn btn-success">
                                <i class="fas fa-plus"></i> Afegir una altra assignació
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección para Alumnos -->
            <div class="col-md-12 role-section" id="student-section" style="display: none;">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white py-3">
                        <h3 class="card-title mb-0 h5">Informació de l'Alumne</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Selecciona el curs i la divisió per a aquest alumne
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_course_id" class="form-label">Curs:</label>
                                    <select name="course_division_pairs[0][course_id]" id="student_course_id" class="form-select">
                                        <option value="">Selecciona un curs</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('course_division_pairs.0.course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="student_division_id" class="form-label">Divisió:</label>
                                    <select name="course_division_pairs[0][division_id]" id="student_division_id" class="form-select">
                                        <option value="">Selecciona una divisió</option>
                                        @foreach($divisions as $division)
                                            <option value="{{ $division->id }}" {{ old('course_division_pairs.0.division_id') == $division->id ? 'selected' : '' }}>
                                                {{ $division->division }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección para Tutores -->
            <div class="col-md-12 role-section" id="tutor-section" style="display: none;">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-white py-3">
                        <h3 class="card-title mb-0 h5">Informació del Tutor</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Selecciona el curs i la divisió per a aquest tutor
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tutor_course_id" class="form-label">Curs:</label>
                                    <select name="tutor_course_id" id="tutor_course_id" class="form-select">
                                        <option value="">Selecciona un curs</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}" {{ old('tutor_course_id') == $course->id ? 'selected' : '' }}>
                                                {{ $course->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tutor_division_id" class="form-label">Divisió:</label>
                                    <select name="tutor_division_id" id="tutor_division_id" class="form-select">
                                        <option value="">Selecciona una divisió</option>
                                        @foreach($divisions as $division)
                                            <option value="{{ $division->id }}" {{ old('tutor_division_id') == $division->id ? 'selected' : '' }}>
                                                {{ $division->division }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección para Orientadores -->
            <div class="col-md-12 role-section" id="orientador-section" style="display: none;">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white py-3">
                        <h3 class="card-title mb-0 h5">Informació de l'Orientador</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Selecciona el nivell educatiu per a aquest orientador
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mx-auto">
                                <div class="mb-3">
                                    <label for="nivel_educativo" class="form-label">Nivell Educatiu:</label>
                                    <select name="nivel_educativo" id="nivel_educativo" class="form-select">
                                        <option value="">Selecciona un nivell</option>
                                        <option value="eso" {{ old('nivel_educativo') == 'eso' ? 'selected' : '' }}>ESO Complet</option>
                                        <option value="bachillerato" {{ old('nivel_educativo') == 'bachillerato' ? 'selected' : '' }}>Batxillerat Complet</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="col-12 mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary py-2 px-4">
                    <i class="fas fa-save me-2"></i> Crear Usuari
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary py-2 px-4">
                    <i class="fas fa-times me-2"></i> Cancel·lar
                </a>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos principales
        const form = document.getElementById('userForm');
        const roleSelect = document.getElementById('role');
        const roleSections = document.querySelectorAll('.role-section');
        const teacherSection = document.getElementById('teacher-section');
        const studentSection = document.getElementById('student-section');
        const tutorSection = document.getElementById('tutor-section');
        const orientadorSection = document.getElementById('orientador-section');
        const alertContainer = document.getElementById('alert-container');
        
        // Función para mostrar alertas
        function showAlert(message, type = 'danger') {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show`;
            alert.innerHTML = `
                <i class="fas fa-${type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            // Eliminar alertas anteriores
            const oldAlerts = alertContainer.querySelectorAll('.alert');
            oldAlerts.forEach(a => a.remove());
            
            // Agregar la nueva alerta
            alertContainer.appendChild(alert);
            
            // Desplazarse al principio para ver la alerta
            window.scrollTo(0, 0);
        }
        
        // Función para mostrar u ocultar secciones según el rol
        function handleRoleChange() {
            // Ocultar todas las secciones
            roleSections.forEach(section => {
                section.style.display = 'none';
            });
            
            // Obtener el valor seleccionado (rol)
            const selectedValue = roleSelect.value.trim();
            if (!selectedValue) return;
            
            // Convertir a número
            const selectedRole = parseInt(selectedValue);
            if (isNaN(selectedRole)) return;
            
            // Mostrar la sección correspondiente
            if (selectedRole === 1) { // Professor
                teacherSection.style.display = 'block';
            } else if (selectedRole === 2) { // Alumne
                studentSection.style.display = 'block';
            } else if (selectedRole === 4) { // Tutor
                tutorSection.style.display = 'block';
            } else if (selectedRole === 5) { // Orientador
                orientadorSection.style.display = 'block';
            }
        }
        
        // Gestión de pares curso-división para profesores
        const addTeacherButton = document.getElementById('add-teacher-course-division');
        const teacherContainer = document.getElementById('teacher-course-division-container');
        
        let teacherPairCount = 1; // Comenzamos en 1 porque ya tenemos un par inicial (índice 0)
        
        if (addTeacherButton && teacherContainer) {
            addTeacherButton.addEventListener('click', function() {
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
                            <div class="mb-3">
                                <label class="form-label">Curs:</label>
                                <select name="course_division_pairs[${teacherPairCount}][course_id]" class="form-select">
                                    <option value="">Selecciona un curs</option>
                                    @foreach ($courses as $course)
                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label class="form-label">Divisió:</label>
                                <select name="course_division_pairs[${teacherPairCount}][division_id]" class="form-select">
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
                teacherContainer.appendChild(newPair);
                
                // Incrementar el contador de pares
                teacherPairCount++;
                
                // Configurar el botón de eliminación
                const removeButton = newPair.querySelector('.remove-pair');
                if (removeButton) {
                    removeButton.addEventListener('click', function() {
                        teacherContainer.removeChild(newPair);
                    });
                }
            });
        }
        
        // Escuchar cambios en el selector de rol
        roleSelect.addEventListener('change', handleRoleChange);
        
        // Inicializar la vista según el rol actual (en caso de que haya uno preseleccionado)
        handleRoleChange();
        
        // Validación básica del formulario
        form.addEventListener('submit', function(event) {
            // Prevenir envío por defecto para validación personalizada
            event.preventDefault();
            
            // Validación de campos obligatorios
            const name = document.getElementById('name').value.trim();
            const lastName = document.getElementById('last_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const role = roleSelect.value.trim();
            
            if (!name || !lastName || !email || !password || !role) {
                showAlert('Cal omplir tots els camps obligatoris (nom, cognoms, correu, contrasenya i rol).');
                return;
            }
            
            if (password.length < 8) {
                showAlert('La contrasenya ha de tenir almenys 8 caràcters.');
                return;
            }
            
            // Validar según el rol
            const selectedRole = parseInt(role);
            let isValid = true;
            
            // SOLUCIÓN MEJORADA: Manejar los pares curso-división para todos los roles
            // Agrupar los pares curso-división
            const pairs = {};
            document.querySelectorAll('[name^="course_division_pairs"]').forEach(select => {
                // Extraer el índice y tipo (course_id o division_id) del nombre del campo
                const matches = select.name.match(/course_division_pairs\[(\d+)\]\[(\w+)\]/);
                if (matches) {
                    const [_, index, type] = matches;
                    if (!pairs[index]) pairs[index] = {};
                    pairs[index][type] = select;
                }
            });

            // Verificar cada par y deshabilitar ambos si alguno está vacío
            Object.values(pairs).forEach(pair => {
                const courseSelect = pair.course_id;
                const divisionSelect = pair.division_id;
                
                if (courseSelect && divisionSelect) {
                    // Si alguno está vacío, deshabilitar ambos
                    if (!courseSelect.value || !divisionSelect.value) {
                        courseSelect.disabled = true;
                        divisionSelect.disabled = true;
                    }
                }
            });
            
            if (selectedRole === 1) { // Professor
                // Verificar que haya al menos un par de curso-división válido
                let hasValidPair = false;
                const pairs = {};
                
                document.querySelectorAll('[name^="course_division_pairs"]').forEach(select => {
                    if (select.disabled) return; // Ignorar campos deshabilitados
                    
                    const matches = select.name.match(/course_division_pairs\[(\d+)\]\[(\w+)\]/);
                    if (matches) {
                        const [_, index, type] = matches;
                        if (!pairs[index]) pairs[index] = {};
                        pairs[index][type] = select;
                    }
                });
                
                // Verificar si hay al menos un par válido
                Object.values(pairs).forEach(pair => {
                    if (pair.course_id && pair.division_id && 
                        pair.course_id.value && pair.division_id.value) {
                        hasValidPair = true;
                    }
                });
                
                if (!hasValidPair) {
                    showAlert('Cal seleccionar almenys un parell curs-divisió vàlid per al professor.');
                    isValid = false;
                }
            } else if (selectedRole === 2) { // Alumne
                const courseSelect = document.getElementById('student_course_id');
                const divisionSelect = document.getElementById('student_division_id');
                
                if (courseSelect && divisionSelect) {
                    if (courseSelect.value && divisionSelect.value) {
                        courseSelect.disabled = false;
                        divisionSelect.disabled = false;
                    } else if (!courseSelect.value && !divisionSelect.value) {
                        courseSelect.disabled = true;
                        divisionSelect.disabled = true;
                    } else {
                        showAlert('Cal seleccionar tant el curs com la divisió per a l\'alumne.');
                        isValid = false;
                        courseSelect.disabled = false;
                        divisionSelect.disabled = false;
                    }
                }
            } else if (selectedRole === 4) { // Tutor
                const courseSelect = document.getElementById('tutor_course_id');
                const divisionSelect = document.getElementById('tutor_division_id');
                
                // Para tutores, permitir usar tanto course_division_pairs como los campos específicos
                
                if (courseSelect && divisionSelect) {
                    if ((courseSelect.value && !divisionSelect.value) || (!courseSelect.value && divisionSelect.value)) {
                        // Si solo uno tiene valor, mostrar error
                        showAlert('Cal seleccionar tant el curs com la divisió per al tutor.');
                        isValid = false;
                    } else if (!courseSelect.value && !divisionSelect.value) {
                        // Si ambos están vacíos, deshabilitarlos para evitar validaciones
                        courseSelect.disabled = true;
                        divisionSelect.disabled = true;
                    }
                }
            } else if (selectedRole === 5) { // Orientador
                const nivelSelect = document.getElementById('nivel_educativo');
                
                // Para orientadores, permitir usar tanto course_division_pairs como el nivel educativo
                // Si se especifica nivel, es prioritario
                if (nivelSelect && !nivelSelect.value) {
                    // Verificar si hay al menos un par curso-división válido
                    let hasValidPair = false;
                    
                    document.querySelectorAll('[name^="course_division_pairs"]').forEach(select => {
                        if (!select.disabled && select.value) {
                            hasValidPair = true;
                        }
                    });
                    
                    // Si no hay nivel educativo ni pares curso-división válidos, mostrar advertencia
                    if (!hasValidPair) {
                        showAlert('Cal seleccionar un nivell educatiu o assignar almenys un curs per a l\'orientador.');
                        isValid = false;
                    }
                }
            }
            
            if (!isValid) return;
            
            // Si pasa todas las validaciones, enviar el formulario
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processant...';
            
            // Ahora ya podemos enviar el formulario con seguridad
            form.submit();
        });
    });
</script>
@endsection