// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: ["@pinia/nuxt", "@nuxtjs/tailwindcss"],
  compatibilityDate: "2024-11-01",
  devtools: { enabled: true },
  css: ["/assets/css/main.css"],
  ssr: false,
  runtimeConfig: {
    public: {
      SOCKET_URL: process.env.SOCKET_URL || 'http://localhost:5000'
    }
  }
});
