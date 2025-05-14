<template>
  <div class="min-h-screen bg-gray-50">
    <DashboardNavTeacher class="w-full" />

    <!-- Capçalera -->
    <div class="bg-gradient-to-r from-[#00ADEC] to-[#0080C0] text-white py-8 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-3xl font-bold">
              Autoavaluació de Competències
            </h1>
            <p class="mt-2 text-blue-100">
              Seguiment gràfic de l'evolució de les competències dels estudiants
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Contingut principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Targetes informatives -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-[#00ADEC] flex items-start">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#00ADEC]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-800 text-lg">Seguiment Personalitzat</h3>
            <p class="text-gray-600 mt-1">Visualitza l'evolució de cada alumne en les competències avaluades.</p>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-amber-500 flex items-start">
          <div class="bg-amber-100 p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-800 text-lg">Anàlisi Visual</h3>
            <p class="text-gray-600 mt-1">Gràfics intuïtius que permeten detectar canvis i progressos en les competències.</p>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 flex items-start">
          <div class="bg-green-100 p-3 rounded-lg mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="font-semibold text-gray-800 text-lg">Millora Educativa</h3>
            <p class="text-gray-600 mt-1">Utilitza els resultats per adaptar estratègies i potenciar les competències.</p>
          </div>
        </div>
      </div>

      <!-- Secció de gràfic -->
      <div class="grid grid-cols-1 gap-8">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="p-1">
            <GraficAutoavaluacio />
          </div>
        </div>
      </div>

      <!-- Secció explicativa -->
      <div class="mt-8 bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Com interpretar les competències</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="font-semibold text-gray-700 mb-2">Competències avaluades</h3>
            <ul class="space-y-2">
              <li class="flex items-start">
                <span class="inline-block w-3 h-3 bg-blue-500 rounded-full mt-1.5 mr-2"></span>
                <div>
                  <span class="font-medium">Responsabilitat:</span> 
                  <span class="text-gray-600">Capacitat per complir amb les obligacions i presa de decisions de manera conscient.</span>
                </div>
              </li>
              <li class="flex items-start">
                <span class="inline-block w-3 h-3 bg-green-500 rounded-full mt-1.5 mr-2"></span>
                <div>
                  <span class="font-medium">Treball en equip:</span> 
                  <span class="text-gray-600">Habilitat per col·laborar eficaçment amb altres persones per assolir objectius comuns.</span>
                </div>
              </li>
              <li class="flex items-start">
                <span class="inline-block w-3 h-3 bg-amber-500 rounded-full mt-1.5 mr-2"></span>
                <div>
                  <span class="font-medium">Gestió del temps:</span> 
                  <span class="text-gray-600">Capacitat per organitzar i prioritzar tasques de manera eficient.</span>
                </div>
              </li>
              <li class="flex items-start">
                <span class="inline-block w-3 h-3 bg-pink-500 rounded-full mt-1.5 mr-2"></span>
                <div>
                  <span class="font-medium">Comunicació:</span> 
                  <span class="text-gray-600">Habilitat per expressar idees de manera clara i efectiva, tant oralment com per escrit.</span>
                </div>
              </li>
            </ul>
          </div>
          <div>
            <h3 class="font-semibold text-gray-700 mb-2">Més competències</h3>
            <ul class="space-y-2">
              <li class="flex items-start">
                <span class="inline-block w-3 h-3 bg-purple-500 rounded-full mt-1.5 mr-2"></span>
                <div>
                  <span class="font-medium">Adaptabilitat:</span> 
                  <span class="text-gray-600">Capacitat per ajustar-se a noves situacions i entorns canviants.</span>
                </div>
              </li>
              <li class="flex items-start">
                <span class="inline-block w-3 h-3 bg-red-500 rounded-full mt-1.5 mr-2"></span>
                <div>
                  <span class="font-medium">Lideratge:</span> 
                  <span class="text-gray-600">Habilitat per guiar i motivar a altres persones cap a l'assoliment d'objectius comuns.</span>
                </div>
              </li>
              <li class="flex items-start">
                <span class="inline-block w-3 h-3 bg-indigo-500 rounded-full mt-1.5 mr-2"></span>
                <div>
                  <span class="font-medium">Creativitat:</span> 
                  <span class="text-gray-600">Capacitat per generar idees originals i innovadores.</span>
                </div>
              </li>
              <li class="flex items-start">
                <span class="inline-block w-3 h-3 bg-teal-500 rounded-full mt-1.5 mr-2"></span>
                <div>
                  <span class="font-medium">Proactivitat:</span> 
                  <span class="text-gray-600">Capacitat per prendre la iniciativa i actuar de manera anticipada.</span>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import DashboardNavTeacher from '@/components/Teacher/DashboardNavTeacher.vue';
import GraficAutoavaluacio from '@/components/Teacher/GraficAutoavaluacio.vue';

// Dades de l'usuari
const userData = ref(null);

// Carregar dades des de localStorage
onMounted(() => {
  const storedUser = localStorage.getItem('user');
  if (storedUser) {
    userData.value = JSON.parse(storedUser);
  }
});
</script>
