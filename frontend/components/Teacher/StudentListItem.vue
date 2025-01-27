<script setup>
import { EyeIcon } from "@heroicons/vue/24/outline";
import { useStudentsStore } from "@/stores/studentsStore";
import { useRouter } from "vue-router";

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
    <!-- Columna: NOM -->
    <td class="py-4 px-6">
      <div class="flex items-center space-x-3">
        <div class="relative">
          <div
            class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold"
          >
            {{
              student.name
                .split(" ")
                .map(n => n[0])
                .join(" ")
                .toUpperCase()
            }}
          </div>
          <!-- Indicador de estado online/offline -->
          <div
            class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-white"
            :class="studentsStore.isStudentOnline(student.id) ? 'bg-green-500' : 'bg-gray-400'"
          ></div>
        </div>
        <div>
          <span class="block text-sm font-medium text-gray-900">
            {{ student.name }} {{ student.last_name }}
          </span>
        </div>
      </div>
    </td>

    <!-- Columna: ESTAT -->
    <td class="py-4 px-6">
      <span
        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
        :class="{
          'bg-green-100 text-green-800': student.status === 1,
          'bg-red-100 text-red-800': student.status === 0
        }"
      >
        {{ student.status === 1 ? "actiu" : "inactiu" }}
      </span>
    </td>

    <!-- Columna: CURS -->
    <td class="py-4 px-6 text-sm text-gray-500">
      {{ student.course }}
    </td>

    <!-- Columna: CLASSE -->
    <td class="py-4 px-6 text-sm text-gray-500">
      {{ student.division }}
    </td>

    <!-- Columna: FITXA -->
    <td class="py-4 px-6 text-right">
      <div class="flex space-x-2 justify-end">
        <button
          class="p-1 hover:text-primary"
          @click.stop="viewProfile(student.id)"
          title="Ver perfil"
        >
          <EyeIcon class="w-5 h-5" />
        </button>
      </div>
    </td>
  </tr>
</template>
