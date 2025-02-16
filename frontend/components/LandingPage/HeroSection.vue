<template>
  <div class="bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div class="text-left space-y-6">
          <h1 class="text-5xl font-bold tracking-tight">
            <span class="text-primary">TEST</span><span class="text-gray-900"></span>IFY<br />
          </h1>
          <h2 class="text-3xl font-bold tracking-tight">
            <span class="text-primary">Auto</span><span class="text-gray-900">matitza</span> l'institut,<br />
            <span class="text-primary">Insp</span><span class="text-gray-900">ira</span> l'aprenentatge.
          </h2>
          <p class="text-xl text-gray-600 mt-6">
            Facilita la formació de grups de treball a classes i creació +
            gestió de formularis per a professors, assegurant la inclusió,
            l'equilibri i la transparència en l'assignació, per analitzar i
            millorar el procés.
          </p>

          <div class="flex gap-4 mt-8">
            <router-link to="/login"
              class="px-6 py-3 bg-primary text-white rounded hover:bg-primary/90 transition-colors">
              Comença ara
            </router-link>
          </div>
        </div>

        <div class="relative overflow-hidden rounded-2xl p-8 bg-gray-50">
          <div class="carousel-container">
            <div class="carousel-track">
              <div v-for="(image, index) in images" :key="index" class="carousel-item">
                <img :src="image.url" :alt="image.alt" class="carousel-image" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "HeroSection",
  data() {
    return {
      images: [
        {
          url: "/img/img1.jpg",
          alt: "Dashboard",
        },
        {
          url: "/img/img2.jpg",
          alt: "Students",
        },
        {
          url: "/img/img3.jpg",
          alt: "Analytics",
        },
      ],
    };
  },
};
</script>

<style scoped>
.carousel-container {
  width: 100%;
  max-width: 500px;
  margin: 0 auto;
  overflow: hidden;
  position: relative;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 1rem;
  padding: 1rem;
  background-color: white;
}

.carousel-track {
  display: flex;
  animation: carouselAnimation 15s ease-in-out infinite;
}

.carousel-item {
  flex: 0 0 100%;
  padding: 0 0.5rem;
}

.carousel-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease;
}

.carousel-image:hover {
  transform: scale(1.05);
}

@keyframes carouselAnimation {

  0%,
  20% {
    transform: translateX(0);
  }

  33%,
  53% {
    transform: translateX(-100%);
  }

  66%,
  86% {
    transform: translateX(-200%);
  }

  100% {
    transform: translateX(0);
  }
}

@media (max-width: 1024px) {
  .carousel-container {
    margin-top: 2rem;
    max-width: 400px;
  }
}
</style>
