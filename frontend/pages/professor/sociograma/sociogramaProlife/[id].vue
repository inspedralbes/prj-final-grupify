<script setup>
import { useRoute } from "vue-router";
import { onMounted, ref, nextTick } from "vue";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";
import { useStudentsStore } from "@/stores/studentsStore";
import { useCoursesStore } from '~/stores/coursesStore'; 
const route = useRoute();
const studentsStore = useStudentsStore();
const coursesStore = useCoursesStore();
const error = ref(null);
const isLoading = ref(true);
const course = ref(null);
const courseId = route.params.id;

onMounted(async () => {
    try {
        await coursesStore.fetchCourses();
        course.value = coursesStore.courses.find((course) => course.id === courseId);
        await studentsStore.fetchStudents(courseId);
    } catch (error) {
        console.error('Error al cargar los datos:', error);
        error.value = 'Error al cargar los datos';
    } finally {
        isLoading.value = false;
    }
});

const students = computed(() => studentsStore.students || []);
console.log(courseId);   
</script>
<template>
    <h1>RESULTATS SOCIOGRAMA</h1>
</template>