<template>
  <div class="p-6 space-y-6">
    <StudentDashboardProfileCard />
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <StudentDashboardFormsCard />
      <StudentDashboardNotificationsCard />
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: "alumnes",
});
</script>