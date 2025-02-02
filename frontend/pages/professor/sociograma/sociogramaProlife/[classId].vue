<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useCoursesStore } from '~/stores/coursesStore';
import { useStudentsStore } from '~/stores/studentsStore';

const route = useRoute();
const classId = ref(null);
const error = ref(null);
const isLoading = ref(true);
const students = ref([]);
const course = ref(null);

classId.value = route.params.classId;

onMounted(async () => {
    try {
        const coursesStore = useCoursesStore();
        const studentsStore = useStudentsStore();

        if (!classId.value) throw new Error('classId no encontrado');

        await coursesStore.fetchCourses();
        course.value = coursesStore.courses.find(c => c.classId == classId.value);
        console.log('course:', course.value);
        if (!course.value) throw new Error('Curso no encontrado');

        await studentsStore.fetchStudents();
        console.log('studentsStore.students:', studentsStore.students);
        students.value = studentsStore.students.filter(student =>
            student.course === course.value.courseName && student.division === course.value.division.name
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
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 tracking-tight mb-2">
                    RESULTATS SOCIOGRAMA
                </h1>
                <div class="h-1 w-20 bg-primary mx-auto rounded-full"></div>
            </div>

            <!-- Loading State -->
            <div v-if="isLoading" 
                 class="bg-white rounded-2xl shadow-lg p-8 flex flex-col items-center justify-center min-h-[300px]">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent"></div>
                <p class="mt-4 text-gray-600 font-medium">Carregant dades...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" 
                 class="bg-red-50 border-l-4 border-red-500 p-6 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-red-500 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-red-700 font-medium">{{ error }}</p>
                </div>
            </div>

            <!-- Content -->
            <div v-else class="space-y-6">
                <!-- Course Info Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-xl">
                            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Curs</h2>
                            <p class="text-2xl font-semibold text-gray-900">{{ course?.courseName }}</p>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-xl">
                            <h2 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Divisió</h2>
                            <p class="text-2xl font-semibold text-gray-900">{{ course?.division?.name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Students List Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6 pb-2 border-b">
                        Lista d'Alumnes
                        <span class="text-gray-500 text-sm font-normal ml-2">({{ students.length }} alumnes)</span>
                    </h3>

                    <div v-if="students.length > 0" 
                         class="grid gap-4">
                        <div v-for="student in students" 
                             :key="student.id"
                             class="group bg-gray-50 p-4 rounded-xl hover:bg-gray-100 transition-all duration-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900">
                                        {{ student.name }} {{ student.last_name }}
                                    </h4>
                                    <p class="text-sm text-gray-500 mt-1">{{ student.email }}</p>
                                </div>
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="text-primary hover:text-primary-dark p-2 rounded-full hover:bg-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else 
                         class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hi ha estudiants</h3>
                        <p class="mt-1 text-sm text-gray-500">No s'han trobat estudiants en aquesta classe.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>