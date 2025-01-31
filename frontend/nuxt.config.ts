// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: ["@pinia/nuxt", "@nuxtjs/tailwindcss", "nuxt-vue3-google-signin"],
  compatibilityDate: "2024-11-01",
  devtools: { enabled: true },
  css: ["/assets/css/main.css"],
  ssr: false,
  runtimeConfig:{
    googleClientId: process.env.GOOGLE_CLIENT_ID,
    public: {
      apiBaseUrl: process.env.API_BASE_URL,
    }
  },
  googleSignIn: {
    clientId: process.env.GOOGLE_CLIENT_ID,
  },
});
