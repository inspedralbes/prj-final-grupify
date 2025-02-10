<template>
  <div class="flex flex-col gap-4 h-[calc(100vh-8rem)]">
    <!-- Editors Section -->
    <div class="grid grid-cols-3 gap-4 h-1/2">
      <!-- HTML Editor -->
      <div class="bg-editor-bg rounded-lg overflow-hidden">
        <div class="bg-gray-800 px-4 py-2 border-b border-gray-700">
          <span class="text-sm font-medium">HTML</span>
        </div>
        <ClientOnly>
          <Codemirror v-model="htmlCode" :style="{ height: 'calc(100% - 37px)' }" :autofocus="true"
            :indent-with-tab="true" :tab-size="2" :extensions="htmlExtensions" />
        </ClientOnly>
      </div>

      <!-- CSS Editor -->
      <div class="bg-editor-bg rounded-lg overflow-hidden">
        <div class="bg-gray-800 px-4 py-2 border-b border-gray-700">
          <span class="text-sm font-medium">CSS</span>
        </div>
        <ClientOnly>
          <Codemirror v-model="cssCode" :style="{ height: 'calc(100% - 37px)' }" :indent-with-tab="true" :tab-size="2"
            :extensions="cssExtensions" />
        </ClientOnly>
      </div>

      <!-- JavaScript Editor -->
      <div class="bg-editor-bg rounded-lg overflow-hidden">
        <div class="bg-gray-800 px-4 py-2 border-b border-gray-700">
          <span class="text-sm font-medium">JavaScript</span>
        </div>
        <ClientOnly>
          <Codemirror v-model="jsCode" :style="{ height: 'calc(100% - 37px)' }" :indent-with-tab="true" :tab-size="2"
            :extensions="jsExtensions" />
        </ClientOnly>
      </div>
    </div>

    <!-- Preview Section -->
    <div class="bg-white rounded-lg overflow-hidden h-1/2">
      <div class="bg-gray-800 px-4 py-2 border-b border-gray-700">
        <span class="text-sm font-medium">Preview</span>
      </div>
      <iframe ref="previewFrame" class="w-full h-[calc(100%-37px)] bg-white" sandbox="allow-scripts"></iframe>
    </div>

    <!-- Download Button -->
    <div class="flex justify-end">
      <button class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-500" @click="downloadFile">
        Download Combined Code
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Codemirror } from 'vue-codemirror'
import { javascript } from '@codemirror/lang-javascript'
import { html } from '@codemirror/lang-html'
import { css } from '@codemirror/lang-css'
import { oneDark } from '@codemirror/theme-one-dark'

const htmlCode = ref('<div class="container">\n  <h1>Hello World</h1>\n</div>')
const cssCode = ref('.container {\n  padding: 20px;\n}')
const jsCode = ref('console.log("Hello World!");')

const htmlExtensions = [html(), oneDark]
const cssExtensions = [css(), oneDark]
const jsExtensions = [javascript(), oneDark]

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
