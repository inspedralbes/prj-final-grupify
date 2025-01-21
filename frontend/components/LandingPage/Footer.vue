<template>
  <footer class="bg-primary text-white py-4 px-6">
    <div class="container mx-auto">
      <p class="text-center">
        &copy; {{ currentYear }} GRUPIFY. Tots els drets reservats.
      </p>
    </div>
  </footer>
</template>

<script>
export default {
  name: "Footer",
  computed: {
    currentYear() {
      return new Date().getFullYear();
    },
  },
};
</script>
