<template>
  <div class="flex flex-col gap-4 h-[calc(100vh-8rem)]">
    <!-- Theme Toggle Button -->
    <div class="flex justify-end">
      <button 
        @click="toggleDarkMode" 
        class="px-3 py-1.5 rounded-md text-sm flex items-center gap-2 transition-colors dark:bg-gray-700 dark:text-white bg-gray-100 text-gray-800 hover:bg-gray-200 dark:hover:bg-gray-600"
      >
        <span class="material-icons text-sm">{{ isDark ? 'light_mode' : 'dark_mode' }}</span>
        {{ isDark ? 'Light Mode' : 'Dark Mode' }}
      </button>
    </div>

    <!-- Editors Section -->
    <div class="grid grid-cols-3 gap-4 h-1/2">
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

    <!-- Preview Section -->
    <div class="rounded-lg overflow-hidden bg-white dark:bg-editor-bg border border-gray-200 dark:border-gray-700">
      <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
        <span class="text-sm font-medium text-gray-800 dark:text-white">Preview</span>
      </div>
      <iframe ref="previewFrame" class="w-full h-[calc(100%-37px)] bg-white" sandbox="allow-scripts"></iframe>
    </div>

    <!-- Download Button -->
    <div class="flex justify-end">
      <button 
        class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-500 transition-colors" 
        @click="downloadFile"
      >
        Download Combined Code
      </button>
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
  // Syntax highlighting
  ".ͼb": { color: "#333" },              // Default text
  ".ͼc": { color: "#999" },              // Comments
  ".ͼd": { color: "#0084ff" },           // Keywords
  ".ͼe": { color: "#476582" },           // Types, definitions
  ".ͼf": { color: "#476582" },           // Properties
  ".ͼ2": { color: "#c41d7f" },           // Strings
  ".ͼ4": { color: "#50a14f" },           // Numbers
  ".ͼ5": { color: "#e45649" },           // Booleans
  ".ͼ7": { color: "#ff9800" },           // Function calls
  ".ͼ8": { color: "#0084ff" },           // Variables
  ".ͼ9": { color: "#6f42c1" },           // Classes
  ".ͼa": { color: "#a626a4" },           // Special characters
})

// Dark mode state management
const isDark = ref(false)

// Initialize dark mode on mount
onMounted(() => {
  // Check if user prefers dark mode
  if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    isDark.value = true
    document.documentElement.classList.add('dark')
  }
  
  // Check if there's a saved preference
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

// Toggle dark mode
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

// Dynamic extensions based on dark mode
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

// Update preview when code changes
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

// Watch dark mode changes to update CodeMirror theme
watch(isDark, () => {
  // Force CodeMirror to update its theme
  htmlCode.value = htmlCode.value
  cssCode.value = cssCode.value
  jsCode.value = jsCode.value
})

// Download combined file
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
</script>

<style>
@import 'material-icons/iconfont/material-icons.css';

.material-icons {
  font-size: 20px;
}

/* Smooth transitions */
* {
  transition: background-color 0.3s ease, border-color 0.3s ease;
}
</style>