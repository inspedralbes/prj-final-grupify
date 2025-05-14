<template>
  <div class="bg-white min-h-screen flex flex-col">
    <!-- Enhanced Navigation Bar -->
    <LandingPageNavBar @show-contact="showContactForm" />
    
    <!-- Contact Form Modal -->
    <LandingPageContactForm
      :is-visible="isContactFormVisible"
      @close="closeContactForm"
    />
    
    <!-- Main Hero Section -->
    <LandingPageHeroSection />
    
    <!-- Logo Carousel Section -->
    <LandingPageImageCarousel />
    
    <!-- Features Section -->
    <LandingPageFeatureSection @request-demo="showContactForm" />
    
    <!-- Education Section -->
    <LandingPageEducationSection />
    
    <!-- About Section -->
    <LandingPageAboutSection />
    
    <!-- Statistics Section -->
    <LandingPageStatsSection />
    
    <!-- Footer -->
    <LandingPageFooter class="mt-auto" />
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Page metadata
useHead({
  title: 'Grupify - Automatitza l\'institut, Inspira l\'aprenentatge',
  meta: [
    { name: 'description', content: 'Plataforma avançada per a la gestió de grups educatius. Facilita la formació de grups equilibrats, gestiona formularis intel·ligents i analitza resultats per millorar l\'aprenentatge.' },
    { name: 'keywords', content: 'grupify, educació, grups, estudiants, professors, gestió educativa, aprenentatge col·laboratiu' },
    { property: 'og:title', content: 'Grupify - Automatitza l\'institut, Inspira l\'aprenentatge' },
    { property: 'og:description', content: 'Plataforma avançada per a la gestió de grups educatius' },
    { property: 'og:type', content: 'website' },
  ]
})

// Reactive state
const isContactFormVisible = ref(false)

// Methods for contact form
const showContactForm = () => {
  isContactFormVisible.value = true
  // Disable body scroll when modal is open
  document.body.style.overflow = 'hidden'
}

const closeContactForm = () => {
  isContactFormVisible.value = false
  // Re-enable body scroll
  document.body.style.overflow = 'unset'
}

// Smooth scroll behavior for anchor links
onMounted(() => {
  // Improve smooth scrolling for anchor links
  const links = document.querySelectorAll('a[href^="#"]')
  links.forEach(link => {
    link.addEventListener('click', (e) => {
      e.preventDefault()
      const targetId = link.getAttribute('href').substring(1)
      const targetElement = document.getElementById(targetId)
      if (targetElement) {
        targetElement.scrollIntoView({ 
          behavior: 'smooth',
          block: 'start'
        })
      }
    })
  })

  // Add scroll-based animations
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-fadeInUp')
        }
      })
    },
    { threshold: 0.1, rootMargin: '-50px' }
  )

  // Observe all sections
  document.querySelectorAll('section, .fade-in-section').forEach(section => {
    observer.observe(section)
  })
})

// Cleanup on unmount
onUnmounted(() => {
  document.body.style.overflow = 'unset'
})
</script>

<style scoped>
/* Animation for sections appearing on scroll */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out forwards;
}

/* Ensure smooth transitions throughout the page */
* {
  transition-property: transform, opacity;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
