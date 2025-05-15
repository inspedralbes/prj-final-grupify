<template>
  <div class="card">
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b">
            <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">Nom</th>
            <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">Estat</th>
            <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">Curs</th>
            <th class="text-left py-3 px-4 text-xs text-gray-500 uppercase">Classe</th>
            <th class="text-right py-3 px-4 text-xs text-gray-500 uppercase">Fitxa</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="student in students"
            :key="student.id"
            class="border-b hover:bg-gray-50"
          >
            <td class="py-3 px-4">
              <div class="flex items-center">
                <div class="h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden mr-3">
                  <img
                    v-if="student.image"
                    :src="student.image"
                    alt="Foto de perfil"
                    class="h-full w-full object-cover"
                  />
                  <span v-else class="text-gray-500 font-medium text-sm">
                    {{ getStudentInitials(student) }}
                  </span>
                </div>
                <div>
                  <p class="font-medium text-gray-900">
                    {{ student.name }} {{ student.last_name }}
                  </p>
                  <p class="text-sm text-gray-500">{{ student.email }}</p>
                </div>
              </div>
            </td>
            <td class="py-3 px-4">
              <span
                class="px-2 py-1 text-xs rounded-full"
                :class="{
                  'bg-green-100 text-green-800': student.status == 1,
                  'bg-gray-100 text-gray-800': student.status != 1,
                }"
              >
                {{ student.status == 1 ? "Actiu" : "Inactiu" }}
              </span>
            </td>
            <td class="py-3 px-4">
              <span class="text-gray-600">{{ student.course_name }}</span>
            </td>
            <td class="py-3 px-4">
              <span class="text-gray-600">{{ student.division_name }}</span>
            </td>
            <td class="py-3 px-4 text-right">
              <NuxtLink
                :to="`/professor/studentProfile/${student.id}`"
                class="text-primary hover:text-primary-dark inline-flex items-center"
              >
                <span class="mr-1">Veure</span>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="1.5"
                  stroke="currentColor"
                  class="w-4 h-4"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M8.25 4.5l7.5 7.5-7.5 7.5"
                  />
                </svg>
              </NuxtLink>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="!students || students.length === 0" class="text-center py-6 text-gray-500">
      <p>No s'han trobat estudiants amb els filtres seleccionats</p>
      <p class="mt-2 text-sm">Si acabes de seleccionar una classe, espera un moment mentre es carreguen les dades</p>
    </div>
  </div>
</template>

<script setup>
import { useStudentsStore } from "@/stores/studentsStore";

const studentsStore = useStudentsStore();

defineProps({
  students: {
    type: Array,
    required: true,
  },
});

// Función para obtener las iniciales del estudiante
const getStudentInitials = (student) => {
  const name = student.name || '';
  const lastName = student.last_name || '';
  
  const firstInitial = name.charAt(0);
  const lastInitial = lastName.charAt(0);
  
  return `${firstInitial}${lastInitial}`.toUpperCase();
};
</script>
