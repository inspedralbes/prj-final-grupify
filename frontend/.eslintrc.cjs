module.exports = {
  extends: [
    '@nuxtjs', // Configuración estándar de Nuxt
    'plugin:vue/vue3-essential', // Configuración para Vue 3
    'eslint:recommended', // Reglas recomendadas por ESLint
    'plugin:prettier/recommended' // Formateo de código con Prettier
  ],
  parserOptions: {
    ecmaVersion: 2020, // Usa la versión ECMAScript 2020
    sourceType: 'module', // El código es un módulo
  },
  rules: {
    // Aquí puedes personalizar reglas adicionales
    'vue/multi-word-component-names': 'off', // Permitir nombres de componente de una sola palabra
    'no-undef': 'off', // Permitir variables no definidas en plantillas de Vue
    'no-console': 'off', // Permitir console.log, console.error, etc.
  },
}
