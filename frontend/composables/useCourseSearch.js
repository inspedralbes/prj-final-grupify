import { ref, computed } from 'vue';

export function useCourseSearch(courses) {
    const searchQuery = ref('');
    const selectedCourse = ref('');
    const selectedDivision = ref('');

    const filteredCourses = computed(() => {
        
        return courses.value.filter(course => {
            const matchesQuery = course.courseName.toLowerCase().includes(searchQuery.value.toLowerCase());
            const matchesCourse = selectedCourse.value ? course.courseId === Number(selectedCourse.value) : true;
            const matchesDivision = selectedDivision.value 
                ? course.division.id === Number(selectedDivision.value)
                : true;
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
