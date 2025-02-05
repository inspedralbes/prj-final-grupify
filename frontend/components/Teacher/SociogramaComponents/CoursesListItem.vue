<script setup>
import { onMounted } from "vue";
import { useRouter } from "vue-router";
import { EyeIcon } from "@heroicons/vue/24/outline";
import { useCoursesStore } from "@/stores/coursesStore";

const router = useRouter();
const coursesStore = useCoursesStore();

defineProps({
  course: {
    type: Object,
    required: true,
    default: () => ({}),
  },
});


const viewProfile = (course) => {
  if (!course) return;
  router.push({
    path: `/professor/sociograma/sociogramaProlife/${course.classId}`,
  });
};

</script>

<template>
  <tr class="border-b hover:bg-gray-50">
    <td class="py-4">{{ course.courseName }}</td>
    <td class="py-4">{{ course.division.name }}</td>
      <button class="p-1 hover:text-primary" @click="viewProfile(course)">
        <EyeIcon class="w-5 h-5" />
      </button>
  </tr>
</template>
