<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useCoursesStore } from '~/stores/coursesStore'; // Asegúrate de importar correctamente
import { useStudentsStore } from '~/stores/studentsStore';

const route = useRoute(); // Obtén el objeto route

const classId = ref(null);
const error = ref(null);
const isLoading = ref(true);
const students = ref([]);
const course = ref(null); // Definimos 'course' como una variable reactiva

// Extraemos el parámetro 'classId' desde la URL
classId.value = route.params.classId;

// console.log('classId desde la URL:', classId.value); // Verifica que `classId` está bien definido

onMounted(async () => {
    try {
        const coursesStore = useCoursesStore();
        const studentsStore = useStudentsStore();

        // Verificamos que `classId` esté correctamente asignado
        if (!classId.value) throw new Error('classId no encontrado');

        await coursesStore.fetchCourses();  // Cargamos los cursos
        course.value = coursesStore.courses.find(c => c.classId == classId.value); // Asignamos el curso encontrado


        if (!course.value) throw new Error('Curso no encontrado');

        await studentsStore.fetchStudents();  // Cargamos los estudiantes

        // Filtramos estudiantes que coincidan con `classId` y `course_division_id`
        students.value = studentsStore.students.filter(student =>
            student.course === course.value.courseId && student.division === course.value.division.id
        );

    } catch (err) {
        console.error('Error al cargar los datos:', err);
        error.value = 'Error al cargar los datos';
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <h1>RESULTATS SOCIOGRAMA</h1>

    <div v-if="isLoading">Cargando...</div>
    <div v-else-if="error">{{ error }}</div>
    <div v-else>
        <h2>Curso: {{ course?.courseName }}</h2>
        <h2>División: {{ course?.division?.name }}</h2>

        <h3>Lista de Alumnos</h3>
        <ul v-if="students.length > 0">
            <li v-for="student in students" :key="student.id">
                {{ student.name }} {{ student.last_name }} - {{ student.email }}
            </li>
        </ul>
        <div v-else>No hay estudiantes en esta clase.</div>
    </div>
</template>
