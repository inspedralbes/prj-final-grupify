<template>
  <!-- Modal Overlay -->
  <Transition name="fade">
    <div 
      v-if="isVisible" 
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title" 
      role="dialog" 
      aria-modal="true"
    >
      <!-- Background overlay -->
      <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="close"></div>
      
      <!-- Modal Panel -->
      <div class="flex min-h-full items-center justify-center p-4">
        <Transition name="scale">
          <div 
            v-if="isVisible"
            class="relative transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all sm:w-full sm:max-w-2xl"
          >
            <!-- Header -->
            <div class="relative bg-gradient-to-r from-primary to-secondary px-6 py-6 sm:px-8">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xl font-semibold text-white">
                    Contacta amb nosaltres
                  </h3>
                  <p class="mt-1 text-sm text-white/80">
                    Estarem encantats d'ajudar-te amb Grupify
                  </p>
                </div>
                <button
                  @click="close"
                  class="rounded-lg p-2 text-white/80 hover:text-white hover:bg-white/10 transition-colors"
                >
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <!-- Decorative elements -->
              <div class="absolute -bottom-1 left-0 right-0 h-2 bg-white rounded-t-2xl"></div>
            </div>

            <!-- Form Content -->
            <div class="px-6 py-8 sm:px-8">
              <form @submit.prevent="submitForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Name Field -->
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                      Nom complet *
                    </label>
                    <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      required
                      class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                      placeholder="Joan García"
                    />
                  </div>

                  <!-- Email Field -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                      Correu electrònic *
                    </label>
                    <input
                      id="email"
                      v-model="form.email"
                      type="email"
                      required
                      class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                      placeholder="joan@exemple.com"
                    />
                  </div>
                </div>

                <!-- Subject Field -->
                <div>
                  <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                    Assumpte
                  </label>
                  <select
                    id="subject"
                    v-model="form.subject"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors"
                  >
                    <option value="">Selecciona un assumpte</option>
                    <option value="demo">Sol·licitud de demostració</option>
                    <option value="info">Informació general</option>
                    <option value="pricing">Preus i plans</option>
                    <option value="support">Suport tècnic</option>
                    <option value="other">Altres</option>
                  </select>
                </div>

                <!-- Message Field -->
                <div>
                  <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    Missatge *
                  </label>
                  <textarea
                    id="message"
                    v-model="form.message"
                    required
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors resize-none"
                    placeholder="Explica'ns com podem ajudar-te..."
                  ></textarea>
                </div>

                <!-- Privacy Checkbox -->
                <div class="flex items-start">
                  <input
                    id="privacy"
                    v-model="form.acceptPrivacy"
                    type="checkbox"
                    required
                    class="mt-1 h-4 w-4 text-primary focus:ring-primary/20 border-gray-300 rounded"
                  />
                  <label for="privacy" class="ml-3 text-sm text-gray-600">
                    Accepto la 
                    <a href="/privacy" class="text-primary hover:text-primary/80 underline">
                      política de privacitat
                    </a> 
                    i permeto que Grupify em contacti *
                  </label>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4">
                  <button
                    type="button"
                    @click="close"
                    class="w-full sm:w-auto px-6 py-3 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                  >
                    Cancel·lar
                  </button>
                  <button
                    type="submit"
                    :disabled="isSubmitting"
                    class="w-full sm:w-auto px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-primary to-secondary hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-all duration-200 transform hover:scale-105"
                  >
                    <span v-if="!isSubmitting">
                      Enviar missatge
                    </span>
                    <span v-else class="flex items-center justify-center gap-2">
                      <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Enviant...
                    </span>
                  </button>
                </div>
              </form>

              <!-- Success Message -->
              <Transition name="slide-up">
                <div v-if="showSuccess" class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                  <div class="flex">
                    <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="ml-3">
                      <h4 class="text-sm font-medium text-green-800">
                        Missatge enviat correctament!
                      </h4>
                      <p class="mt-1 text-sm text-green-700">
                        Et respondrem al més aviat possible.
                      </p>
                    </div>
                  </div>
                </div>
              </Transition>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 sm:px-8 border-t border-gray-100">
              <div class="text-sm text-gray-500 text-center">
                També pots contactar-nos directament a 
                <a href="mailto:contact@grupify.com" class="text-primary hover:text-primary/80">
                  contact@grupify.com
                </a>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, defineProps, defineEmits } from 'vue'

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const form = ref({
  name: '',
  email: '',
  subject: '',
  message: '',
  acceptPrivacy: false
})

const isSubmitting = ref(false)
const showSuccess = ref(false)

const submitForm = async () => {
  isSubmitting.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // Reset form
    form.value = {
      name: '',
      email: '',
      subject: '',
      message: '',
      acceptPrivacy: false
    }
    
    // Show success message
    showSuccess.value = true
    
    // Hide success message after 3 seconds
    setTimeout(() => {
      showSuccess.value = false
      close()
    }, 3000)
  } catch (error) {
    console.error('Error submitting form:', error)
  } finally {
    isSubmitting.value = false
  }
}

const close = () => {
  emit('close')
}
</script>

<style scoped>
/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.scale-enter-active,
.scale-leave-active {
  transition: all 0.3s ease;
}

.scale-enter-from,
.scale-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: all 0.3s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

/* Form styling */
input:focus,
textarea:focus,
select:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
}

/* Custom scrollbar for textarea */
textarea::-webkit-scrollbar {
  width: 6px;
}

textarea::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

textarea::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 3px;
}

textarea::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
</style>
