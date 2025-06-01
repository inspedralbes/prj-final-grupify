<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher class="w-full" />

    <!-- Cabecera con información -->
    <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white py-8 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold">
              Gràfiques Comparatives CESC
            </h1>
            <p class="mt-2 text-blue-100">
              Analitza i compara diferents aspectes del CESC entre classes
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenido principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Card Comparativa de Rols -->
        <div class="rounded-xl shadow-lg overflow-hidden bg-white transition-transform transform hover:scale-105">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <div class="bg-blue-100 p-3 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                </svg>
              </div>
              <div class="ml-4">
                <h2 class="text-xl font-semibold text-gray-800">Comparativa de Rols</h2>
                <p class="text-gray-600 mt-1">Analitza i compara nivells de popularitat, aïllament i neutralitat entre classes</p>
              </div>
            </div>
          </div>
          <div class="px-6 py-4">
            <div class="flex flex-wrap gap-2 justify-center mb-4">
              <div class="px-3 py-2 rounded-full bg-green-100 text-green-700 flex items-center">
                <span class="font-bold mx-1">P</span>
                <span>Populars</span>
              </div>
              <div class="px-3 py-2 rounded-full bg-red-100 text-red-700 flex items-center">
                <span class="font-bold mx-1">A</span>
                <span>Aïllats</span>
              </div>
              <div class="px-3 py-2 rounded-full bg-blue-100 text-blue-700 flex items-center">
                <span class="font-bold mx-1">N</span>
                <span>Neutrals</span>
              </div>
            </div>
          </div>
          <div class="px-6 pb-6">
            <nuxt-link to="/professor/grafiques/rols" class="block w-full py-3 px-4 text-center bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
              Visualitzar comparativa
            </nuxt-link>
          </div>
        </div>

        <!-- Card Comparativa d'Habilitats -->
        <div class="rounded-xl shadow-lg overflow-hidden bg-white transition-transform transform hover:scale-105">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <div class="bg-yellow-100 p-3 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <div class="ml-4">
                <h2 class="text-xl font-semibold text-gray-800">Comparativa d'Habilitats</h2>
                <p class="text-gray-600 mt-1">Analitza i compara les habilitats de lideratge, creativitat i organització entre classes</p>
              </div>
            </div>
          </div>
          <div class="px-6 py-4">
            <div class="flex flex-wrap gap-2 justify-center mb-4">
              <div class="px-3 py-2 rounded-full bg-indigo-100 text-indigo-700 flex items-center">
                <span class="font-bold mx-1">L</span>
                <span>Lideratge</span>
              </div>
              <div class="px-3 py-2 rounded-full bg-purple-100 text-purple-700 flex items-center">
                <span class="font-bold mx-1">C</span>
                <span>Creativitat</span>
              </div>
              <div class="px-3 py-2 rounded-full bg-cyan-100 text-cyan-700 flex items-center">
                <span class="font-bold mx-1">O</span>
                <span>Organització</span>
              </div>
            </div>
          </div>
          <div class="px-6 pb-6">
            <nuxt-link to="/professor/grafiques/habilitats" class="block w-full py-3 px-4 text-center bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors">
              Visualitzar comparativa
            </nuxt-link>
          </div>
        </div>

        <!-- Card Comparativa SOCIAL (A) -->
        <div class="rounded-xl shadow-lg overflow-hidden bg-white transition-transform transform hover:scale-105">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <div class="bg-green-100 p-3 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                </svg>
              </div>
              <div class="ml-4">
                <h2 class="text-xl font-semibold text-gray-800">SOCIAL</h2>
                <p class="text-gray-600 mt-1">Analitza i compara els perfils socials positius entre classes</p>
              </div>
            </div>
          </div>
          <div class="px-6 py-4">
            <div class="flex flex-wrap gap-2 justify-center mb-4">
              <div class="px-3 py-2 rounded-full bg-green-100 text-green-700 flex items-center">
                <span class="font-bold mx-1">P</span>
                <span>Popular</span>
              </div>
              <div class="px-3 py-2 rounded-full bg-green-100 text-green-700 flex items-center">
                <span class="font-bold mx-1">PS</span>
                <span>Prosocial</span>
              </div>
            </div>
          </div>
          <div class="px-6 pb-6">
            <nuxt-link to="/orientador/cesc/comparative?categoria=social" class="block w-full py-3 px-4 text-center bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
              Visualitzar comparativa
            </nuxt-link>
          </div>
        </div>
        
        <!-- Card Comparativa VIOLENTO (B) -->
        <div class="rounded-xl shadow-lg overflow-hidden bg-white transition-transform transform hover:scale-105">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <div class="bg-red-100 p-3 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-4">
                <h2 class="text-xl font-semibold text-gray-800">VIOLENTO</h2>
                <p class="text-gray-600 mt-1">Analitza i compara els perfils agressius entre classes</p>
              </div>
            </div>
          </div>
          <div class="px-6 py-4">
            <div class="flex flex-wrap gap-2 justify-center mb-4">
              <div class="px-3 py-2 rounded-full bg-red-100 text-red-700 flex items-center">
                <span class="font-bold mx-1">A</span>
                <span>Agressiu</span>
              </div>
            </div>
          </div>
          <div class="px-6 pb-6">
            <nuxt-link to="/orientador/cesc/comparative?categoria=violento" class="block w-full py-3 px-4 text-center bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
              Visualitzar comparativa
            </nuxt-link>
          </div>
        </div>
        
        <!-- Card Comparativa AFECTADO (C) -->
        <div class="rounded-xl shadow-lg overflow-hidden bg-white transition-transform transform hover:scale-105 md:col-span-2">
          <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
              <div class="bg-yellow-100 p-3 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-4">
                <h2 class="text-xl font-semibold text-gray-800">AFECTADO</h2>
                <p class="text-gray-600 mt-1">Analitza i compara els perfils afectats entre classes</p>
              </div>
            </div>
          </div>
          <div class="px-6 py-4">
            <div class="flex flex-wrap gap-2 justify-center mb-4">
              <div class="px-3 py-2 rounded-full bg-blue-100 text-blue-700 flex items-center">
                <span class="font-bold mx-1">R</span>
                <span>Rebutjat</span>
              </div>
              <div class="px-3 py-2 rounded-full bg-yellow-100 text-yellow-700 flex items-center">
                <span class="font-bold mx-1">V</span>
                <span>Víctima</span>
              </div>
            </div>
          </div>
          <div class="px-6 pb-6">
            <nuxt-link to="/orientador/cesc/comparative?categoria=afectado" class="block w-full py-3 px-4 text-center bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors">
              Visualitzar comparativa
            </nuxt-link>
          </div>
        </div>
        
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue';

// Datos del usuario
const userData = ref(null);

// Cargar datos del usuario desde localStorage
onMounted(() => {
  const storedUser = localStorage.getItem('user');
  if (storedUser) {
    userData.value = JSON.parse(storedUser);
  }
});
</script>
