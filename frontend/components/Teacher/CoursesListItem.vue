<script setup>
import { EyeIcon } from "@heroicons/vue/24/outline";
import { useCoursesStore } from '~/stores/coursesStore'; 

const coursesStore = useCoursesStore();
const router = useRouter();

const viewProfile = (courseId) => {
  router.push(`/professor/courseProfile/${courseId}`);
};

defineProps({
  course: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <tr :key="course.id" class="border-b hover:bg-gray-50">
    <td class="py-4">
      <div class="flex items-center space-x-3">
        <div class="relative">
          <div
            class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold"
          >
            {{
              course.name
                .split(" ")
                .map(n => n[0])
                .join("")
                .toUpperCase()
            }}
          </div>
          <!-- Indicador de estado online/offline -->
          <div
            class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white"
            :class="coursesStore.iscourseOnline(course.id) ? 'bg-green-500' : 'bg-gray-400'"
          ></div>
        </div>
        <span>{{ course.name }}</span>
      </div>
    </td>

    <!-- Mostrar solo el nombre de la primera división del curso -->
    <td>{{ course.divisions && course.divisions.length > 0 ? course.divisions[0].name : 'Sin división' }}</td>

    <td>
      <div class="flex space-x-2">
        <button
          class="p-1 hover:text-primary"
          @click.stop="viewProfile(course.id)"
        >
          <EyeIcon class="w-5 h-5" />
        </button>
      </div>
    </td>
    
    <td>
      <span
        class="px-3 py-1 rounded-full text-white text-sm font-medium"
        :class="course.active ? 'bg-green-500' : 'bg-red-500'"
      >
        {{ course.active ? "Actiu" : "Inactiu" }}
      </span>
    </td>
  </tr>
</template>
