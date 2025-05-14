<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher />

    <main>
      <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
        <div class="space-y-8">
          <TeacherNotificationCreate />
          <TeacherNotificationHistory />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";

// La lógica para las notificaciones y demás está manejada dentro de los componentes hijos
</script>
