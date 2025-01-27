import { ref, computed } from 'vue';

export function useCourseSearch(courses) {
    const searchQuery = ref('');
    const selectedCourse = ref('');
    const selectedDivision = ref('');

    const filteredCourses = computed(() => {
        return courses.value.filter(course => {
            // Comprobación del nombre del curso (por búsqueda de texto)
            const matchesQuery = course.name.toLowerCase().includes(searchQuery.value.toLowerCase());

            // Comprobación del ID del curso seleccionado
            const matchesCourse = selectedCourse.value 
                ? course.id === Number(selectedCourse.value)
                : true;

            // Comprobación del ID de la división seleccionada
            const matchesDivision = selectedDivision.value 
                ? course.divisions.some(division => {
                    return division.id === Number(selectedDivision.value); // Compara el ID seleccionado con el ID real
                })
                : true;

            // Devuelve el resultado si todos los filtros coinciden
            return matchesQuery && matchesCourse && matchesDivision;
        });
    });

    return {
        searchQuery,
        selectedCourse,
        selectedDivision,
        filteredCourses,
    };
}
