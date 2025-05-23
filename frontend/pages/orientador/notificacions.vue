<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavOrientador />

    <main>
      <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
        <div class="space-y-8">
          <OrientadorNotificationCreate />
          <OrientadorNotificationHistory />
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import DashboardNavOrientador from "@/components/Orientador/DashboardNavOrientador.vue";
import OrientadorNotificationCreate from "@/components/Orientador/Notification/Create.vue";
import OrientadorNotificationHistory from "@/components/Orientador/Notification/History.vue";

// La lógica para las notificaciones y demás está manejada dentro de los componentes hijos
</script>
