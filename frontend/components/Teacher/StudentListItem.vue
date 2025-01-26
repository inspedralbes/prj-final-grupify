<script setup>
import { EyeIcon } from "@heroicons/vue/24/outline";
import { useStudentsStore } from "@/stores/studentsStore";

const studentsStore = useStudentsStore();
const router = useRouter();

const viewProfile = (studentId) => {
  router.push(`/professor/studentProfile/${studentId}`);
};

defineProps({
  student: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <tr :key="student.id" class="border-b hover:bg-gray-50">
    <td class="py-4">
      <div class="flex items-center space-x-3">
        <div class="relative">
          <div
            class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold"
          >
            {{
              student.name
                .split(" ")
                .map(n => n[0])
                .join("")
                .toUpperCase()
            }}
          </div>
          <!-- Indicador de estado online/offline -->
          <div
            class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white"
            :class="studentsStore.isStudentOnline(student.id) ? 'bg-green-500' : 'bg-gray-400'"
          ></div>
        </div>
        <span>{{ student.name }}</span>
        <span>{{ student.last_name }}</span>
      </div>
    </td>
    <td>{{ student.course }}</td>
    <td>{{ student.division }}</td>

    <td>
      <div class="flex space-x-2">
        <button
          class="p-1 hover:text-primary"
          @click.stop="viewProfile(student.id)"
        >
          <EyeIcon class="w-5 h-5" />
        </button>
      </div>
    </td>
    
    <td>
      <span
        class="px-3 py-1 rounded-full text-white text-sm font-medium"
        :class="student.active ? 'bg-green-500' : 'bg-red-500'"
      >
        {{ student.active ? "Actiu" : "Inactiu" }}
      </span>
    </td>
  </tr>
</template>