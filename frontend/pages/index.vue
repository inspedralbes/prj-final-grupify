<template>
  <div class="bg-white min-h-screen flex flex-col">
    <LandingPageNavBar @show-contact="showContactForm" />
    <LandingPageContactForm
      :is-visible="isContactFormVisible"
      @close="closeContactForm"
    />
    <LandingPageHeroSection />
    <LandingPageImageCarousel />
    <LandingPageEducationSection />
    <LandingPageFooter class="mt-auto" />
  </div>
</template>

<script setup>
// Estado reactivo
const isContactFormVisible = ref(false);

// Métodos para manejar el formulario de contacto
const showContactForm = () => {
  isContactFormVisible.value = true;
};

const closeContactForm = () => {
  isContactFormVisible.value = false;
};
</script>
