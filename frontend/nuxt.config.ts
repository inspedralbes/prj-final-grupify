// https://nuxt.com/docs/api/configuration/nuxt-config
import { defineNuxtConfig } from 'nuxt/config'

export default defineNuxtConfig({
  modules: ["@pinia/nuxt", "@nuxtjs/tailwindcss", "nuxt-vue3-google-signin"],
  compatibilityDate: "2024-11-01",
  devtools: { enabled: true },
  css: ["/assets/css/main.css"],
  ssr: false,
  runtimeConfig: {
    public: {
      googleClientId: process.env.GOOGLE_CLIENT_ID,
      apiBaseUrl: process.env.API_BASE_URL,
    },
  },
  googleSignIn: {
    clientId: process.env.GOOGLE_CLIENT_ID,
  },
  vite: {
    define: {
      global: 'globalThis'
    },
    resolve: {
      alias: {
        stream: 'stream-browserify'
      }
    }
  }
});
