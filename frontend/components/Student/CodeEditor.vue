<template>
  <div class="flex flex-col gap-4 h-[calc(100vh-8rem)] relative">
    <!-- Header Section with Home Button and Theme Toggle Button -->
    <div class="flex justify-between items-center">
      <div class="flex items-center space-x-2">
        <NuxtLink to="/alumne/dashboard" class="flex items-center space-x-2">
          <span class="material-icons text-blue-500">home</span>
          <span class="font-medium">Inici</span>
        </NuxtLink>
      </div>
      <button 
        @click="toggleDarkMode" 
        class="px-3 py-1.5 rounded-md text-sm flex items-center gap-2 transition-colors dark:bg-gray-700 dark:text-white bg-gray-100 text-gray-800 hover:bg-gray-200 dark:hover:bg-gray-600"
      >
        <span class="material-icons text-sm">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
        {{ isDark ? 'Light Mode' : 'Dark Mode' }}
      </button>
    </div>

    <!-- Editors Section -->
    <div class="grid grid-cols-3 gap-4 flex-grow">
      <!-- HTML Editor -->
      <div class="rounded-lg overflow-hidden bg-white dark:bg-editor-bg border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
          <span class="text-sm font-medium text-gray-800 dark:text-white">HTML</span>
        </div>
        <ClientOnly>
          <Codemirror 
            v-model="htmlCode" 
            :style="{ height: 'calc(100% - 37px)' }" 
            :autofocus="true"
            :indent-with-tab="true" 
            :tab-size="2" 
            :extensions="getExtensions('html')" 
          />
        </ClientOnly>
      </div>

      <!-- CSS Editor -->
      <div class="rounded-lg overflow-hidden bg-white dark:bg-editor-bg border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
          <span class="text-sm font-medium text-gray-800 dark:text-white">CSS</span>
        </div>
        <ClientOnly>
          <Codemirror 
            v-model="cssCode" 
            :style="{ height: 'calc(100% - 37px)' }" 
            :indent-with-tab="true" 
            :tab-size="2"
            :extensions="getExtensions('css')" 
          />
        </ClientOnly>
      </div>

      <!-- JavaScript Editor -->
      <div class="rounded-lg overflow-hidden bg-white dark:bg-editor-bg border border-gray-200 dark:border-gray-700">
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
          <span class="text-sm font-medium text-gray-800 dark:text-white">JavaScript</span>
        </div>
        <ClientOnly>
          <Codemirror 
            v-model="jsCode" 
            :style="{ height: 'calc(100% - 37px)' }" 
            :indent-with-tab="true" 
            :tab-size="2"
            :extensions="getExtensions('js')" 
          />
        </ClientOnly>
      </div>
    </div>

    <!-- Handle para redimensionar la altura de la preview (oculto en fullscreen) -->
    <div 
      v-if="!isFullscreen" 
      class="resize-handle cursor-row-resize h-2 bg-gray-300 dark:bg-gray-700"
      @mousedown="startResize"
    ></div>

    <!-- Preview Section -->
    <div 
      :class="[
        'rounded-lg overflow-hidden bg-white dark:bg-editor-bg border border-gray-200 dark:border-gray-700',
        isFullscreen ? 'fixed top-0 left-0 w-full h-full z-50' : ''
      ]"
      :style="!isFullscreen ? { height: previewHeight + 'px' } : {}"
    >
      <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-between items-center">
        <span class="text-sm font-medium text-gray-800 dark:text-white">Preview</span>
        <div class="flex items-center gap-2">
          <!-- Botón para pantalla completa -->
          <button 
            @click="toggleFullscreen"
            aria-label="Toggle Fullscreen"
            class="focus:outline-none"
          >
            <span 
              class="material-icons text-lg" 
              :class="{'text-black': !isDark, 'text-white': isDark}"
            >
              {{ isFullscreen ? 'fullscreen_exit' : 'fullscreen' }}
            </span>
          </button>
          <!-- Botón para descargar -->
          <button 
            @click="downloadFile"
            aria-label="Descargar"
            class="focus:outline-none"
          >
            <span 
              class="material-icons text-lg" 
              :class="{'text-black': !isDark, 'text-white': isDark}"
            >
              download
            </span>
          </button>
        </div>
      </div>
      <iframe ref="previewFrame" class="w-full h-[calc(100%-37px)] bg-white" sandbox="allow-scripts"></iframe>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { Codemirror } from 'vue-codemirror'
import { javascript } from '@codemirror/lang-javascript'
import { html } from '@codemirror/lang-html'
import { css } from '@codemirror/lang-css'
import { oneDark } from '@codemirror/theme-one-dark'
import { basicSetup } from 'codemirror'
import { EditorView } from '@codemirror/view'

// Juejin light theme definition
const juejinTheme = EditorView.theme({
  "&": {
    backgroundColor: "#ffffff",
    color: "#333"
  },
  ".cm-content": {
    caretColor: "#0084ff"
  },
  "&.cm-focused .cm-cursor": {
    borderLeftColor: "#0084ff"
  },
  "&.cm-focused .cm-selectionBackground, ::selection": {
    backgroundColor: "#0084ff33"
  },
  ".cm-gutters": {
    backgroundColor: "#ffffff",
    color: "#999",
    border: "none",
    borderRight: "1px solid #eee"
  },
  ".cm-activeLineGutter": {
    backgroundColor: "#f8f9fa"
  },
  ".cm-line": {
    fontFamily: "Menlo, Monaco, Consolas, 'Courier New', monospace"
  },
  ".ͼb": { color: "#333" },
  ".ͼc": { color: "#999" },
  ".ͼd": { color: "#0084ff" },
  ".ͼe": { color: "#476582" },
  ".ͼf": { color: "#476582" },
  ".ͼ2": { color: "#c41d7f" },
  ".ͼ4": { color: "#50a14f" },
  ".ͼ5": { color: "#e45649" },
  ".ͼ7": { color: "#ff9800" },
  ".ͼ8": { color: "#0084ff" },
  ".ͼ9": { color: "#6f42c1" },
  ".ͼa": { color: "#a626a4" },
})

const isDark = ref(false)

onMounted(() => {
  if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }

  const savedTheme = localStorage.getItem('theme')
  if (savedTheme) {
    isDark.value = savedTheme === 'dark'
    if (isDark.value) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }
})

const toggleDarkMode = () => {
  isDark.value = !isDark.value
  if (isDark.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

const htmlCode = ref('<div class="container">\n  <h1>Hello World</h1>\n</div>')
const cssCode = ref('.container {\n  padding: 20px;\n}')
const jsCode = ref('console.log("Hello World!");')

const getExtensions = (lang: 'html' | 'css' | 'js') => {
  const theme = isDark.value ? [oneDark] : [basicSetup, juejinTheme]
  switch (lang) {
    case 'html':
      return [html(), ...theme]
    case 'css':
      return [css(), ...theme]
    case 'js':
      return [javascript(), ...theme]
  }
}

const previewFrame = ref<HTMLIFrameElement | null>(null)

watch([htmlCode, cssCode, jsCode], () => {
  if (!previewFrame.value) return

  const preview = `
    <!DOCTYPE html>
    <html>
      <head>
        <style>${cssCode.value}</style>
      </head>
      <body>
        ${htmlCode.value}
        <script>${jsCode.value}<\/script>
      </body>
    </html>
  `
  previewFrame.value.srcdoc = preview
}, { immediate: true })

watch(isDark, () => {
  htmlCode.value = htmlCode.value
  cssCode.value = cssCode.value
  jsCode.value = jsCode.value
})

const downloadFile = () => {
  const combinedCode = `
    <!DOCTYPE html>
    <html>
      <head>
        <style>${cssCode.value}</style>
      </head>
      <body>
        ${htmlCode.value}
        <script>${jsCode.value}<\/script>
      </body>
    </html>
  `

  const blob = new Blob([combinedCode], { type: 'text/html' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = 'index.html'
  link.click()
  URL.revokeObjectURL(link.href)
}

const isFullscreen = ref(false)
const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
}

const previewHeight = ref(300)
let isResizing = false
let startY = 0
let startHeight = 0

const startResize = (e: MouseEvent) => {
  isResizing = true
  startY = e.clientY
  startHeight = previewHeight.value
  window.addEventListener('mousemove', resize)
  window.addEventListener('mouseup', stopResize)
}

const resize = (e: MouseEvent) => {
  if (!isResizing) return
  const dy = e.clientY - startY
  const newHeight = startHeight - dy
  previewHeight.value = Math.max(newHeight, 100)
}

const stopResize = () => {
  isResizing = false
  window.removeEventListener('mousemove', resize)
  window.removeEventListener('mouseup', stopResize)
}
</script>

<style>
@import 'material-icons/iconfont/material-icons.css';

.material-icons {
  font-size: 20px;
}

/* Transiciones suaves para cambios de color y borde */
* {
  transition: background-color 0.3s ease, border-color 0.3s ease;
}
</style>
