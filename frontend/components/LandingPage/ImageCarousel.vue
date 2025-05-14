<template>
  <div class="bg-gray-50 py-16 border-y border-gray-100">
    <div class="mx-auto max-w-7xl px-6">
      <!-- Section Title -->
      <div class="text-center mb-10">
        <p class="text-sm font-medium text-primary uppercase tracking-wider mb-2">
          Suport institucional
        </p>
        <h2 class="text-xl font-semibold text-gray-700">
          Acció Finançada pel Ministeri d'Educació, Formació Professional i Esports i per la Unió Europea - NextGenerationUE
        </h2>
      </div>

      <!-- Enhanced Carousel Container -->
      <div class="relative overflow-hidden">
        <!-- Gradient Overlays -->
        <div class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-gray-50 to-transparent z-10"></div>
        <div class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-gray-50 to-transparent z-10"></div>
        
        <!-- Carousel Track -->
        <div class="carousel-track-container">
          <div class="carousel-track flex gap-8">
            <template
              v-for="(image, index) in duplicatedImages"
              :key="`${index}-${image.url}`"
            >
              <div class="carousel-item flex-shrink-0">
                <div class="relative overflow-hidden rounded-lg bg-white shadow-sm hover:shadow-md transition-shadow duration-300 group">
                  <img 
                    :src="image.url" 
                    :alt="image.alt" 
                    class="carousel-image transform group-hover:scale-105 transition-transform duration-300"
                    loading="lazy"
                  />
                  
                  <!-- Overlay for interactivity -->
                  <div class="absolute inset-0 bg-gradient-to-t from-black/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <div class="absolute bottom-0 left-0 right-0 p-3">
                      <p class="text-xs text-white text-center font-medium">{{ image.description }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const images = ref([
  { 
    url: "/img/logo1.jpg", 
    alt: "Ministeri d'Educació",
    description: "Ministeri d'Educació"
  },
  { 
    url: "/img/logo2.png", 
    alt: "Formació Professional i Esports",
    description: "Formació Professional"
  },
  { 
    url: "/img/logo3.png", 
    alt: "Unió Europea",
    description: "Unió Europea"
  },
  { 
    url: "/img/logo4.jpg", 
    alt: "NextGenerationUE",
    description: "NextGenerationUE"
  },
  { 
    url: "/img/logo5.jpg", 
    alt: "Fons de Recuperació",
    description: "Fons de Recuperació"
  },
  { 
    url: "/img/logo6.jpg", 
    alt: "Plaça Tecnològica",
    description: "Plaça Tecnològica"
  },
])

const duplicatedImages = computed(() => {
  // Duplicate the images array for seamless infinite scroll
  return [...images.value, ...images.value, ...images.value]
})
</script>

<style scoped>
.carousel-track-container {
  overflow: hidden;
  position: relative;
}

.carousel-track {
  /* Calculate the total width needed for all items */
  width: calc(320px * 18); /* 6 images * 3 sets * (280px + 32px gap) */
  animation: scroll 60s linear infinite;
  will-change: transform;
}

.carousel-track:hover {
  animation-play-state: paused;
}

.carousel-item {
  width: 280px;
  height: 100px;
}

.carousel-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  padding: 1rem;
  filter: grayscale(30%);
  transition: filter 0.3s ease;
}

.carousel-item:hover .carousel-image {
  filter: grayscale(0%);
}

@keyframes scroll {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(calc(-320px * 6)); /* Width of one set */
  }
}

/* Pause animation on reduced motion preference */
@media (prefers-reduced-motion: reduce) {
  .carousel-track {
    animation: none;
  }
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .carousel-item {
    width: 240px;
    height: 80px;
  }
  
  .carousel-track {
    width: calc(256px * 18); /* Adjusted for smaller items */
  }
  
  @keyframes scroll {
    0% {
      transform: translateX(0);
    }
    100% {
      transform: translateX(calc(-256px * 6));
    }
  }
}

@media (max-width: 480px) {
  .carousel-item {
    width: 200px;
    height: 60px;
  }
  
  .carousel-track {
    width: calc(216px * 18);
  }
  
  @keyframes scroll {
    0% {
      transform: translateX(0);
    }
    100% {
      transform: translateX(calc(-216px * 6));
    }
  }
}
</style>
