<template>
  <div class="h-screen bg-white dark:bg-gray-900 flex text-gray-900 dark:text-white">
    <!-- Sidebar Desktop: Lista de notes visible solo en desktop -->
    <div class="w-64 border-r border-gray-200 dark:border-gray-800 flex flex-col hidden md:flex">
      <!-- Contenido de la lista de notes (igual que antes) -->
      <div class="p-4 space-y-6">
        <div class="flex items-center space-x-2">
          <NuxtLink
            to="/alumne/dashboard"
            class="flex items-center space-x-2 hover:text-blue-600 transition-colors"
          >
            <span class="material-icons text-blue-500">home</span>
            <span class="font-medium">Inici</span>
          </NuxtLink>
        </div>

        <div>
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-medium text-gray-600 dark:text-gray-400">Documents</h2>
            <button class="text-sm text-blue-500 hover:text-blue-600">
              <span class="material-icons text-sm">filter_list</span>
            </button>
          </div>
          <div class="relative mb-4">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
            <input
              type="text"
              placeholder="Buscar"
              class="w-full pl-10 pr-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
            />
          </div>
          <button
            @click="createNewNote()"
            class="w-full flex items-center justify-center space-x-2 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"
          >
            <span class="material-icons text-gray-600 dark:text-gray-400">add</span>
            <span>Nou Document</span>
          </button>
          <div class="mt-4 space-y-2">
            <div
              v-for="note in filteredNotes"
              :key="note.id"
              class="p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer relative group"
              :class="{ 'bg-gray-50 dark:bg-gray-800': currentNote?.id === note.id }"
            >
              <div class="flex items-start space-x-3" @click="selectNote(note)">
                <span class="material-icons text-gray-400">description</span>
                <div class="flex-1">
                  <h3 class="font-medium">{{ note.title }}</h3>
                  <div class="flex items-center mt-1">
                    <span class="text-xs px-2 py-1 rounded-full" :class="getSubjectColor(note.subject)">
                      {{ note.subject }}
                    </span>
                  </div>
                </div>
                <!-- Botón de eliminar -->
                <button
                  @click.stop="confirmDelete(note)"
                  class="hidden group-hover:block p-1 hover:bg-red-100 dark:hover:bg-red-900/20 rounded"
                >
                  <span class="material-icons text-red-500">delete</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dropdown Mobile: Lista de notes (visible solo en mobile) -->
    <transition name="fade">
      <div
        v-if="mobileNotesDropdownOpen"
        class="fixed inset-0 bg-black bg-opacity-50 z-50 md:hidden"
        @click.self="mobileNotesDropdownOpen = false"
      >
        <div class="bg-white dark:bg-gray-900 p-4">
          <div class="p-2">
            <div class="flex items-center justify-end">
              <button @click="mobileNotesDropdownOpen = false" class="p-2">
                <span class="material-icons">close</span>
              </button>
            </div>
            <div class="space-y-6">
              <div class="flex items-center space-x-2">
                <NuxtLink
                  to="/alumne/dashboard"
                  class="flex items-center space-x-2 hover:text-blue-600 transition-colors"
                  @click="mobileNotesDropdownOpen = false"
                >
                  <span class="material-icons text-blue-500">home</span>
                  <span class="font-medium">Inici</span>
                </NuxtLink>
              </div>
              <div>
                <div class="flex items-center justify-between mb-4">
                  <h2 class="text-sm font-medium text-gray-600 dark:text-gray-400">Documents</h2>
                  <button class="text-sm text-blue-500 hover:text-blue-600">
                    <span class="material-icons text-sm">filter_list</span>
                  </button>
                </div>
                <div class="relative mb-4">
                  <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                  <input
                    type="text"
                    placeholder="Buscar"
                    class="w-full pl-10 pr-4 py-2 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                  />
                </div>
                <!-- Importante: se corrige la invocación de la función -->
                <button
                  @click="createNewNote(); mobileNotesDropdownOpen = false"
                  class="w-full flex items-center justify-center space-x-2 py-2 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800"
                >
                  <span class="material-icons text-gray-600 dark:text-gray-400">add</span>
                  <span>Nou Document</span>
                </button>
                <div class="mt-4 space-y-2">
                  <div
                    v-for="note in filteredNotes"
                    :key="note.id"
                    class="p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer relative group"
                    :class="{ 'bg-gray-50 dark:bg-gray-800': currentNote?.id === note.id }"
                  >
                    <div class="flex items-start space-x-3" @click="selectNote(note, true)">
                      <span class="material-icons text-gray-400">description</span>
                      <div class="flex-1">
                        <h3 class="font-medium">{{ note.title }}</h3>
                        <div class="flex items-center mt-1">
                          <span class="text-xs px-2 py-1 rounded-full" :class="getSubjectColor(note.subject)">
                            {{ note.subject }}
                          </span>
                        </div>
                      </div>
                      <button
                        @click.stop="confirmDelete(note)"
                        class="hidden group-hover:block p-1 hover:bg-red-100 dark:hover:bg-red-900/20 rounded"
                      >
                        <span class="material-icons text-red-500">delete</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
      <!-- Top Bar -->
      <div class="h-14 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-6 flex-shrink-0">
        <div class="flex items-center space-x-4">
          <!-- Botón para abrir el dropdown de notas en mobile -->
          <button class="md:hidden p-2" @click="mobileNotesDropdownOpen = true">
            <span class="material-icons text-gray-700 dark:text-gray-300">menu</span>
          </button>
          <!-- Botón para abrir el modal del assistente de IA en mobile -->
          <button class="md:hidden p-2" @click="mobileAIChatOpen = true">
            <span class="material-icons text-gray-700 dark:text-gray-300">smart_toy</span>
          </button>
          <select
            v-model="selectedSubject"
            class="px-3 py-1.5 rounded-md bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
          >
            <option v-for="subject in subjects" :key="subject" :value="subject">
              {{ subject }}
            </option>
          </select>
        </div>
        <div class="flex items-center space-x-4">
          <button @click="toggleDarkMode()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
            <span class="material-icons text-gray-700 dark:text-gray-300">
              {{ isDark ? 'light_mode' : 'dark_mode' }}
            </span>
          </button>
          <div v-if="currentNote" class="flex space-x-2">
  <button
    @click="exportNoteDocx(currentNote)"
    class="flex items-center space-x-2 px-4 py-2 rounded-lg bg-transparent text-black dark:text-white hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      stroke-width="1.5"
      stroke="currentColor"
      class="w-6 h-6"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"
      />
    </svg>
  </button>
</div>

        </div>
      </div>

      <!-- Área del editor y panel de IA en desktop -->
      <div class="flex-1 flex overflow-hidden">
        <div class="flex-1 overflow-y-auto">
          <div class="p-6">
            <template v-if="currentNote">
              <input
                v-model="currentNote.title"
                class="text-3xl font-bold mb-6 w-full bg-transparent border-none outline-none"
                placeholder="Document Title"
                @input="updateNote(currentNote.id, { title: currentNote.title })"
              />
              <StudentRichTextEditor
                v-model="currentNote.content"
                @update:modelValue="updateNote(currentNote.id, { content: $event })"
              />
            </template>
            <div v-else class="h-full flex items-center justify-center text-gray-500">
              <div class="text-center">
                <span class="material-icons text-4xl mb-2">description</span>
                <p>Seleccioneu un document o creeu-ne un de nou per començar a escriure</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel de assistent de IA en desktop (visible solo en md y superiores) -->
        <div class="hidden md:flex w-80 border-l border-gray-200 dark:border-gray-800 flex-col">
          <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex-shrink-0">
            <h3 class="text-lg font-medium flex items-center gap-2 dark:text-white">
              <span class="material-icons text-blue-500">smart_toy</span>
              Assistent IA
            </h3>
          </div>
          <div class="flex-1 p-4 overflow-y-auto" style="max-height: calc(100vh - 14rem)">
            <div
              v-if="generating"
              class="flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
            >
              <div class="flex items-center gap-1">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
              </div>
              <span class="text-sm text-blue-600 dark:text-blue-400">Generant notes...</span>
            </div>
            <div
              v-if="error"
              class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800"
            >
              <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                <span class="material-icons text-sm">error</span>
                <span class="text-sm font-medium">Error</span>
              </div>
              <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
            </div>
            <div v-if="previewContent" class="chat-preview">
              <div class="relative p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <div class="prose dark:prose-invert max-w-none text-sm">
                  <div v-html="displayedContent" class="typing-animation"></div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                  <button
                    @click="insertToDocument"
                    class="flex items-center gap-2 px-3 py-1.5 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                  >
                    <span class="material-icons text-sm">add_circle</span>
                    Insertar al Document
                  </button>
                  <button
                    @click="previewContent = ''"
                    class="flex items-center gap-2 px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                  >
                    <span class="material-icons text-sm">close</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="p-4 border-t border-gray-200 dark:border-gray-800 flex-shrink-0">
            <textarea
              v-model="prompt"
              placeholder="Demana'm que generi notes sobre qualsevol tema..."
              class="w-full h-32 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            ></textarea>
            <button
              @click="generateAINotes"
              :disabled="generating || !prompt"
              class="mt-3 w-full py-2 px-4 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-colors"
            >
              <span class="material-icons text-sm">auto_awesome</span>
              <span>Generar notes</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Mobile per al assistent de IA -->
  <transition name="fade">
    <div
      v-if="mobileAIChatOpen"
      class="fixed inset-0 bg-black bg-opacity-50 z-50 md:hidden"
      @click.self="mobileAIChatOpen = false"
    >
      <div class="bg-white dark:bg-gray-900 p-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium flex items-center gap-2 dark:text-white">
            <span class="material-icons text-blue-500">smart_toy</span>
            Assistent IA
          </h3>
          <button @click="mobileAIChatOpen = false" class="p-2">
            <span class="material-icons">close</span>
          </button>
        </div>
        <div class="mb-4">
          <div
            v-if="generating"
            class="flex items-center gap-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
          >
            <div class="flex items-center gap-1">
              <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
              <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
              <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
            </div>
            <span class="text-sm text-blue-600 dark:text-blue-400">Generant notes...</span>
          </div>
          <div
            v-if="error"
            class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800"
          >
            <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
              <span class="material-icons text-sm">error</span>
              <span class="text-sm font-medium">Error</span>
            </div>
            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
          </div>
          <div v-if="previewContent" class="chat-preview">
            <div class="relative p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
              <div class="prose dark:prose-invert max-w-none text-sm">
                <div v-html="displayedContent" class="typing-animation"></div>
              </div>
              <div class="mt-4 flex justify-end gap-2">
                <button
                  @click="insertToDocument"
                  class="flex items-center gap-2 px-3 py-1.5 text-sm bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                >
                  <span class="material-icons text-sm">add_circle</span>
                  Insertar al Document
                </button>
                <button
                  @click="previewContent = ''"
                  class="flex items-center gap-2 px-3 py-1.5 text-sm bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                >
                  <span class="material-icons text-sm">close</span>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div>
          <textarea
            v-model="prompt"
            placeholder="Demana'm que generi notes sobre qualsevol tema..."
            class="w-full h-32 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all dark:text-white"
          ></textarea>
          <button
            @click="generateAINotes"
            :disabled="generating || !prompt"
            class="mt-3 w-full py-2 px-4 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 transition-colors"
          >
            <span class="material-icons text-sm">auto_awesome</span>
            <span>Generar notes</span>
          </button>
        </div>
      </div>
    </div>
  </transition>

  <!-- Modal de Confirmación para eliminar -->
  <div
    v-if="showDeleteModal"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
  >
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full mx-4">
      <h3 class="text-lg font-medium mb-4">Confirmar eliminación</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">
        ¿Estás seguro de que quieres eliminar "{{ noteToDelete?.title }}"? Esta acción no se puede deshacer.
      </p>
      <div class="flex justify-end space-x-4">
        <button
          @click="showDeleteModal = false"
          class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
        >
          Cancelar
        </button>
        <button
          @click="deleteConfirmed"
          class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
        >
          Eliminar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useDark, useToggle } from '@vueuse/core';
import { useNotes } from '~/composables/useNotes';
import { useGemini } from '~/composables/useGemini';
import { Note } from '~/types';
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';
import htmlToDocx from 'html-to-docx';

const isDark = useDark({
  selector: 'html',
  attribute: 'class',
  valueDark: 'dark',
  valueLight: 'light',
});
const toggleDarkMode = useToggle(isDark);

const { notes, currentNote, createNote, updateNote, deleteNote, exportNotePDF, exportNoteDocx } =
  useNotes();
const { generateNotes, generating, error } = useGemini();

const subjects = [
  'Matemàtiques',
  'Física',
  'Química',
  'Biologia',
  'Història',
  'Literatura',
];

const selectedSubject = ref(subjects[0]);
const prompt = ref('');
const previewContent = ref('');
const displayedContent = ref('');
const showDeleteModal = ref(false);
const noteToDelete = ref<Note | null>(null);
const mobileNotesDropdownOpen = ref(false);
const mobileAIChatOpen = ref(false); // Variable para controlar el modal del assistente en mobile
const typingSpeed = 20; // ms por carácter

const filteredNotes = computed(() => {
  return notes.value.filter((note) => note.subject === selectedSubject.value);
});

onMounted(() => {
  const htmlElement = document.documentElement;
  if (isDark.value) {
    htmlElement.classList.add('dark');
  } else {
    htmlElement.classList.remove('dark');
  }
});

const getSubjectColor = (subject: string) => {
  const colors = {
    'Matemàtiques': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    'Física': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
    'Química': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'Biologia': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    'Història': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    'Literatura': 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
  };
  return colors[subject] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
};

const typeContent = (content: string) => {
  let currentIndex = 0;
  displayedContent.value = '';
  const typeNextChar = () => {
    if (currentIndex < content.length) {
      displayedContent.value += content[currentIndex];
      currentIndex++;
      setTimeout(typeNextChar, typingSpeed);
    }
  };
  typeNextChar();
};

const generateAINotes = async () => {
  if (!prompt.value || generating.value) return;
  const generatedContent = await generateNotes(prompt.value, selectedSubject.value);
  if (generatedContent) {
    previewContent.value = generatedContent;
    typeContent(generatedContent);
  }
};

const insertToDocument = () => {
  if (!currentNote.value) {
    createNewNote();
  }
  const updatedNote = {
    ...currentNote.value,
    content: previewContent.value,
    title: prompt.value.slice(0, 50) + (prompt.value.length > 50 ? '...' : '')
  };
  if (currentNote.value.content) {
    updatedNote.content = currentNote.value.content + '<br><br>' + previewContent.value;
  }
  currentNote.value = updatedNote;
  updateNote(currentNote.value.id, {
    content: updatedNote.content,
    title: updatedNote.title,
  });
  previewContent.value = '';
  prompt.value = '';
};

const createNewNote = () => {
  createNote(selectedSubject.value);
};

const selectNote = (note: Note, closeDropdown = false) => {
  currentNote.value = note;
  if (closeDropdown) mobileNotesDropdownOpen.value = false;
};

const confirmDelete = (note: Note) => {
  noteToDelete.value = note;
  showDeleteModal.value = true;
};

const deleteConfirmed = () => {
  if (noteToDelete.value) {
    deleteNote(noteToDelete.value.id);
    showDeleteModal.value = false;
    noteToDelete.value = null;
  }
};
</script>

<style>
@import 'material-icons/iconfont/material-icons.css';

.material-icons {
  font-size: 20px;
}

.chat-preview {
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.typing-animation {
  border-right: 2px solid transparent;
  animation: blink 1s step-end infinite;
}

@keyframes blink {
  from,
  to {
    border-color: transparent;
  }
  50% {
    border-color: #3b82f6;
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.ProseMirror p {
  margin: 0.5em 0;
  line-height: 1.4;
}
</style>
