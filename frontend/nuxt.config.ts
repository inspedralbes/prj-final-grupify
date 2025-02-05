// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: ["@pinia/nuxt", "@nuxtjs/tailwindcss", "nuxt-vue3-google-signin"],
  compatibilityDate: "2024-11-01",
  devtools: { enabled: true },
  css: ["/assets/css/main.css"],
  ssr: false,
  plugins: [
    { src: '~/plugins/register-sw.js', mode: 'client' } // Solo en el cliente
  ],
  runtimeConfig: {
    public: {
      googleClientId: process.env.GOOGLE_CLIENT_ID,
      apiBaseUrl: process.env.API_BASE_URL,
      publicVapidKey: 'BJfWWy6SFL83diyANaMQIiYHHmAYbZUPEGbi7t_R3WOpp7bQO1R9XGROJoiRPe30k5JtWE_N6MnR-IW6lFZpOkg'
    },
  },
  googleSignIn: {
    clientId: process.env.GOOGLE_CLIENT_ID,
  },
});
