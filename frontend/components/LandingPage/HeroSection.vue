<template>
  <div class="relative bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto">
      <div class="relative z-10">
        <div class="relative overflow-hidden">
          <!-- Removed redundant bg-white class -->
          
          <div class="px-4 sm:px-6 lg:px-8 py-16 lg:py-24 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
              <!-- Content Section -->
              <div class="text-left space-y-6">
                <!-- Main Heading -->
                <div>
                  <h1 class="text-5xl lg:text-6xl font-bold tracking-tight leading-none">
                    <span class="text-primary">GRUP</span><span class="text-gray-900">IFY</span>
                  </h1>
                  <div class="h-1 w-24 bg-gradient-to-r from-primary to-secondary mt-4 rounded-full" />
                </div>
                
                <!-- Subtitle with animated gradient -->
                <h2 class="text-3xl lg:text-4xl font-bold tracking-tight bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                  Automatitza l'institut,<br />
                  Inspira l'aprenentatge.
                </h2>
                
                <!-- Description -->
                <p class="text-lg text-gray-600 max-w-2xl leading-relaxed">
                  Revoluciona la gestió educativa amb Grupify. Facilita la formació de grups equilibrats, 
                  gestiona formularis intel·ligents i analitza resultats per millorar continuament el procés d'aprenentatge.
                </p>

                <!-- Key Features -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                    </div>
                    <span class="text-gray-700">Formació de grups equilibrats</span>
                  </div>
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                      </svg>
                    </div>
                    <span class="text-gray-700">Gestió intel·ligent de formularis</span>
                  </div>
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                      </svg>
                    </div>
                    <span class="text-gray-700">Analítica avançada</span>
                  </div>
                  <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                      </svg>
                    </div>
                    <span class="text-gray-700">Transparència i inclusió</span>
                  </div>
                </div>

                <!-- Call to Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 mt-10">
                  <router-link
                    to="/login"
                    class="px-8 py-4 bg-gradient-to-r from-primary to-secondary text-white rounded-lg font-medium hover:shadow-lg transform hover:scale-105 transition-all duration-200 text-center"
                  >
                    Comença ara gratuïtament
                  </router-link>
                  <button
                    @click="scrollToSection"
                    class="px-8 py-4 bg-white text-gray-700 border-2 border-gray-200 rounded-lg font-medium hover:bg-gray-50 hover:border-primary transition-all duration-200 text-center"
                  >
                    Descobreix més
                  </button>
                </div>
                
              </div>

              <!-- Image Section with Enhanced Design -->
              <div class="relative lg:pl-12">
                
                <!-- Main image container -->
                <div class="relative z-10 bg-white rounded-2xl shadow-2xl overflow-hidden transform rotate-6 hover:rotate-0 transition-transform duration-500">
                  <div class="carousel-container">
                    <div class="carousel-track">
                      <div
                        v-for="(image, index) in images"
                        :key="index"
                        class="carousel-item bg-white" 
                      >
                        <img :src="image.url" :alt="image.alt" class="carousel-image" />
                      </div>
                    </div>
                  </div>
                  
                  <!-- Image overlay content -->
                  <div class="absolute bottom-0 left-0 right-0 p-6">
                    <div class="flex items-center space-x-2 text-white">
                      <div class="flex -space-x-2">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Usuario 1" class="w-8 h-8 rounded-full border-2 border-white shadow-sm object-cover" />
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Usuario 2" class="w-8 h-8 rounded-full border-2 border-white shadow-sm object-cover" />
                        <img src="https://randomuser.me/api/portraits/men/55.jpg" alt="Usuario 3" class="w-8 h-8 rounded-full border-2 border-white shadow-sm object-cover" />
                      </div>
                      <span class="text-sm font-medium">En ús per 15,000+ estudiants</span>
                    </div>
                  </div>
                </div>
                
                <!-- Floating elements -->
                <div class="absolute top-4 right-8 z-20">
                  <div class="bg-white rounded-lg shadow-lg p-3 transform hover:scale-110 transition-transform duration-200">
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                      <span class="text-xs font-medium text-gray-600">En línia</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Scroll indicator -->
          <div class="flex justify-center pb-8">
            <button @click="scrollToSection" class="animate-bounce">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const images = ref([
  {
    url: "/img/img1.jpg",
    alt: "Dashboard modern de Grupify",
  },
  {
    url: "/img/img2.jpg",
    alt: "Estudiants treballant en grups",
  },
  {
    url: "/img/img3.jpg",
    alt: "Analítiques avançades",
  },
])

const scrollToSection = () => {
  const nextSection = document.querySelector('#education')
  if (nextSection) {
    nextSection.scrollIntoView({ behavior: 'smooth' })
  }
}
</script>

<style scoped>
.carousel-container {
  width: 100%;
  height: 400px;
  overflow: hidden;
  position: relative;
  border-radius: 1rem;
}

.carousel-track {
  display: flex;
  height: 100%;
  animation: carouselAnimation 15s ease-in-out infinite;
}

.carousel-item {
  flex: 0 0 100%;
  position: relative;
  height: 100%;
  background-color: white;
}

.carousel-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.carousel-item:hover .carousel-image {
  transform: scale(1.05);
}

@keyframes carouselAnimation {
  0%, 20% {
    transform: translateX(0);
  }
  33%, 53% {
    transform: translateX(-100%);
  }
  66%, 86% {
    transform: translateX(-200%);
  }
  100% {
    transform: translateX(0);
  }
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .carousel-container {
    height: 300px;
  }
}

@media (max-width: 640px) {
  .carousel-container {
    height: 250px;
  }
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.8s ease-out forwards;
}
</style>
