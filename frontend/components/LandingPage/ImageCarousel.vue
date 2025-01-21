<template>
  <div class="bg-gray-100 py-12">
    <div class="mx-auto max-w-7xl px-6">
      <h2 class="text-2xl font-semibold text-gray-900 mb-8 text-center">
        Acció Finançada pel Ministeri d'Educació, Formació Professional i
        Esports i per la Unió Europea - NextGenerationUE
      </h2>
      <div class="carousel-container">
        <div class="carousel-track">
          <template
            v-for="(image, index) in duplicatedImages"
            :key="`${index}-${image.url}`"
          >
            <div class="carousel-item">
              <img :src="image.url" :alt="image.alt" class="carousel-image" />
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ImageCarousel",
  data() {
    return {
      images: [
        { url: "/img/logo1.jpg", alt: "Image 1" },
        { url: "/img/logo2.png", alt: "Image 2" },
        { url: "/img/logo3.png", alt: "Image 3" },
        { url: "/img/logo4.jpg", alt: "Image 4" },
        { url: "/img/logo5.jpg", alt: "Image 5" },
        { url: "/img/logo6.jpg", alt: "Image 6" },
      ],
    };
  },
  computed: {
    duplicatedImages() {
      return [...this.images, ...this.images];
    },
  },
};
</script>

<style scoped>
.carousel-container {
  width: 100%;
  overflow: hidden;
  position: relative;
}

.carousel-track {
  display: flex;
  width: fit-content;
  animation: scroll 40s linear infinite;
}

.carousel-track:hover {
  animation-play-state: paused;
}

.carousel-item {
  flex: 0 0 200px;
  padding: 0 0.5rem;
  transition: transform 0.3s ease;
}

.carousel-item:hover {
  transform: scale(1.05);
}

.carousel-image {
  width: 100%;
  height: 100px;
  object-fit: contain;
  border-radius: 0.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

@keyframes scroll {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}

@media (max-width: 640px) {
  .carousel-item {
    flex: 0 0 150px;
  }
}
</style>
