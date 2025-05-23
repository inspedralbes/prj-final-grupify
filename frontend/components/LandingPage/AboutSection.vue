<template>
  <div id="about" class="pb-0">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div class="text-center max-w-3xl mx-auto mb-16">
        <span class="text-sm font-semibold text-primary uppercase tracking-wider">
          Qui som
        </span>
        <h2 class="mt-2 text-3xl lg:text-4xl font-bold text-gray-900">
          La història darrere de Grupify
        </h2>
        <div class="h-1 w-24 bg-gradient-to-r from-primary to-secondary mx-auto mt-4 rounded-full"></div>
        <p class="mt-6 text-lg text-gray-600">
          Nascut de la necessitat real dels educadors, desenvolupat amb passió per la innovació educativa.
        </p>
      </div>

      <!-- Story Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-20">
        <!-- Story Content -->
        <div class="space-y-6">
          <div class="relative pl-8 before:absolute before:left-0 before:top-3 before:h-4 before:w-4 before:bg-primary before:rounded-full">
            <h3 class="text-xl font-semibold text-gray-900">El nostre començament</h3>
            <p class="mt-2 text-gray-600">
              Grupify va sorgir de la necessitat detectada per professors d'ESO i Batxillerat que passaven hores formant grups manuals. 
              Va néixer com un projecte pilot en un institut de Barcelona i ara ajuda a milers d'educadors.
            </p>
          </div>
          
          <div class="relative pl-8 before:absolute before:left-0 before:top-3 before:h-4 before:w-4 before:bg-secondary before:rounded-full">
            <h3 class="text-xl font-semibold text-gray-900">La nostra missió</h3>
            <p class="mt-2 text-gray-600">
              Democratitzar l'accés a eines avançades de gestió educativa, assegurant que cada estudiant tingui l'oportunitat 
              de participar en grups equilibrats i inclusius.
            </p>
          </div>
          
          <div class="relative pl-8 before:absolute before:left-0 before:top-3 before:h-4 before:w-4 before:bg-purple-500 before:rounded-full">
            <h3 class="text-xl font-semibold text-gray-900">El nostre compromís</h3>
            <p class="mt-2 text-gray-600">
              Desenvolupem tecnologia que posa les persones al centre, sempre respectant la privacitat, 
              fomentant la inclusió i facilitant la feina dels educadors.
            </p>
          </div>
        </div>

        <!-- Visual Representation -->
        <div class="relative">
          <div class="relative z-10 grid grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-300">
              <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.832 18.477 19.247 18 17.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <h4 class="font-semibold text-gray-900">Educació</h4>
              <p class="text-sm text-gray-600 mt-1">Experiència pedagògica</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-300 mt-6">
              <div class="w-12 h-12 bg-secondary/10 rounded-lg flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
              </div>
              <h4 class="font-semibold text-gray-900">Tecnologia</h4>
              <p class="text-sm text-gray-600 mt-1">Innovació aplicada</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-300">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <h4 class="font-semibold text-gray-900">Dades</h4>
              <p class="text-sm text-gray-600 mt-1">Anàlisi intel·ligent</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-transform duration-300 mt-6">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <h4 class="font-semibold text-gray-900">Comunitat</h4>
              <p class="text-sm text-gray-600 mt-1">Col·laboració activa</p>
            </div>
          </div>
          
          <!-- Background Decorations -->
          <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-primary/10 to-secondary/10 rounded-full blur-2xl"></div>
          <div class="absolute bottom-0 left-0 w-40 h-40 bg-gradient-to-tr from-secondary/10 to-primary/10 rounded-full blur-2xl"></div>
        </div>
      </div>

      <!-- Team Section -->
      <div class="border-t border-gray-100 pt-16">
        <h3 class="text-2xl font-bold text-gray-900 text-center mb-12">L'equip darrere de Grupify</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8">
          <!-- Team Member 1 -->
          <div class="text-center group">
            <div class="relative inline-block mb-4">
              <div class="w-32 h-32 rounded-full overflow-hidden group-hover:scale-110 transition-transform duration-300">
                <img src="/img/lucas.jpg" alt="Lucas Benitez" class="w-full h-full object-cover" />
              </div>
              <div class="absolute bottom-0 right-0 w-8 h-8 bg-green-500 rounded-full border-4 border-white"></div>
            </div>
            <h4 class="font-semibold text-gray-900">Lucas Benitez</h4>
            <p class="text-sm text-gray-600">Co-fundador & CEO</p>
            <p class="text-xs text-gray-500 mt-1">Desenvolupador Web</p>
          </div>

          <!-- Team Member 2 -->
          <div class="text-center group">
            <div class="relative inline-block mb-4">
              <div class="w-32 h-32 rounded-full overflow-hidden group-hover:scale-110 transition-transform duration-300">
                <img src="/img/ara.jpg" alt="Araceli Pacheco" class="w-full h-full object-cover" />
              </div>
              <div class="absolute bottom-0 right-0 w-8 h-8 bg-green-500 rounded-full border-4 border-white"></div>
            </div>
            <h4 class="font-semibold text-gray-900">Araceli Pacheco</h4>
            <p class="text-sm text-gray-600">Co-fundadora & CEO</p>
            <p class="text-xs text-gray-500 mt-1">Desenvolupadora Web</p>
          </div>

          <!-- Team Member 3 -->
          <div class="text-center group">
            <div class="relative inline-block mb-4">
              <div class="w-32 h-32 rounded-full overflow-hidden group-hover:scale-110 transition-transform duration-300">
                <img src="/img/jos.jpg" alt="Joselyn Ninahuaman" class="w-full h-full object-cover" />
              </div>
              <div class="absolute bottom-0 right-0 w-8 h-8 bg-green-500 rounded-full border-4 border-white"></div>
            </div>
            <h4 class="font-semibold text-gray-900">Joselyn Ninahuaman</h4>
            <p class="text-sm text-gray-600">Co-fundadora & CEO</p>
            <p class="text-xs text-gray-500 mt-1">Desenvolupadora Web</p>
          </div>

          <!-- Team Member 4 -->
          <div class="text-center group">
            <div class="relative inline-block mb-4">
              <div class="w-32 h-32 rounded-full overflow-hidden group-hover:scale-110 transition-transform duration-300">
                <img src="/img/ale.jpg" alt="Aleiram Minaya" class="w-full h-full object-cover" />
              </div>
              <div class="absolute bottom-0 right-0 w-8 h-8 bg-green-500 rounded-full border-4 border-white"></div>
            </div>
            <h4 class="font-semibold text-gray-900">Aleiram Minaya</h4>
            <p class="text-sm text-gray-600">Co-fundadora & CEO</p>
            <p class="text-xs text-gray-500 mt-1">Desenvolupadora Web</p>
          </div>

          <!-- Team Member 5 -->
          <div class="text-center group">
            <div class="relative inline-block mb-4">
              <div class="w-32 h-32 rounded-full overflow-hidden group-hover:scale-110 transition-transform duration-300">
                <img src="/img/adri.jpg" alt="Adrian Estevez" class="w-full h-full object-cover" />
              </div>
              <div class="absolute bottom-0 right-0 w-8 h-8 bg-green-500 rounded-full border-4 border-white"></div>
            </div>
            <h4 class="font-semibold text-gray-900">Adrian Estevez</h4>
            <p class="text-sm text-gray-600">Co-fundador & CEO</p>
            <p class="text-xs text-gray-500 mt-1">Desenvolupador Web</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<style scoped>
/* Timeline connector line */
.relative::before {
  content: '';
  position: absolute;
  left: 2px;
  top: 20px;
  height: calc(100% - 40px);
  width: 2px;
  background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
}

/* Last item should not have a line */
.relative:last-child::before {
  display: none;
}

/* Custom gradient for team avatars */
@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}

.group:hover .relative {
  animation: float 3s ease-in-out infinite;
}

/* Avatar image styles */
.rounded-full.overflow-hidden img {
  object-position: center;
  transition: transform 0.5s ease;
}

.group:hover .rounded-full.overflow-hidden img {
  transform: scale(1.05);
}
</style>
