<template>
  <div id="education" class="bg-white py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <div class="inline-block">
          <span class="text-sm font-semibold text-primary uppercase tracking-wider">
            Graus Educatius
          </span>
          <h2 class="mt-2 text-3xl lg:text-4xl font-bold text-gray-900">
            Adaptat a tots els nivells educatius
          </h2>
          <div class="h-1 w-24 bg-gradient-to-r from-primary to-secondary mx-auto mt-4 rounded-full"></div>
        </div>
        <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto">
          Grupify s'adapta perfectament a diferents etapes educatives, oferint funcionalitats específiques per a cada grau.
        </p>
      </div>

      <!-- Education Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-6xl mx-auto">
        <div
          v-for="course in courses"
          :key="course.title"
          class="group relative bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2"
        >
          <!-- Card Header -->
          <div class="relative h-56 overflow-hidden">
            <img
              :src="course.image"
              :alt="course.title"
              class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/40 to-transparent"></div>
            <div class="absolute bottom-0 left-0 right-0 p-6">
              <h3 class="text-2xl font-bold text-white">{{ course.title }}</h3>
              <span class="inline-block mt-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium text-white">
                {{ course.students }} estudiants
              </span>
            </div>
          </div>

          <!-- Card Content -->
          <div class="p-6">
            <p class="text-gray-600 mb-6">{{ course.description }}</p>
            
            <!-- Features List -->
            <div class="space-y-3 mb-6">
              <div
                v-for="(detail, index) in course.details"
                :key="index"
                class="flex items-center text-gray-700"
              >
                <div class="flex-shrink-0 w-5 h-5 mr-3">
                  <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </div>
                <span class="text-sm">{{ detail }}</span>
              </div>
            </div>

            <!-- Key Benefits -->
            <div class="border-t border-gray-100 pt-4">
              <h4 class="text-sm font-semibold text-gray-900 mb-3">Beneficis clau:</h4>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="benefit in course.benefits"
                  :key="benefit"
                  class="px-3 py-1 bg-primary/10 text-primary text-xs font-medium rounded-full"
                >
                  {{ benefit }}
                </span>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- Call to Action -->
      <div class="mt-16 text-center">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">
          Preparat per transformar la teva aula?
        </h3>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const courses = ref([
  {
    title: "ESO",
    image: "/img/eso.jpg",
    description: "Ideal per a adolescents en desenvolupament. Grupify facilita la formació de grups equilibrats per fomentar la col·laboració i l'aprenentatge entre iguals.",
    details: [
      "1º ESO - Adaptació i integració",
      "2º ESO - Desenvolupament de competències socials",
      "3º ESO - Projectes col·laboratius",
      "4º ESO - Preparació per a l'etapa post-obligatòria"
    ],
    benefits: ["Col·laboració", "Inclusió", "Gestió d'emocions"],
    students: "8,500+",
    setupTime: "5 min"
  },
  {
    title: "Batxillerat",
    image: "/img/bachillerato.jpg",
    description: "Per a estudiants de secundària post-obligatòria que necessiten preparar-se per a la universitat. Grupify ofereix eines avançades per a projectes complexos.",
    details: [
      "Formació integral i especialitzada",
      "Madurez intel·lectual i humana",
      "Coneixements avançats i especialitzats",
      "Destreses per a l'educació superior"
    ],
    benefits: ["Anàlisi avançada", "Projectes complexos", "Preparació universitària"],
    students: "5,200+",
    setupTime: "3 min"
  },
])
</script>

<style scoped>
/* Custom scrollbar for card content */
.card-content::-webkit-scrollbar {
  width: 4px;
}

.card-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 2px;
}

.card-content::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 2px;
}

.card-content::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .group {
    margin-bottom: 1rem;
  }
}
</style>
