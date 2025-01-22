<template>
    <div>
      <h1>Panel de Administración</h1>
      <div v-if="loading">Cargando...</div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        loading: true,
        adminData: null,
      };
    },
    methods: {
      redirectToDashboard() {
        // Verifica si hay token o alguna condición para redirigir
        const token = localStorage.getItem('auth_token');
        if (token) {
          // Redirigir a la URL externa (el backend)
          window.location.href = 'http://localhost:8000/dashboard';
        } else {
          // Si no está autenticado, redirige a login o alguna otra URL
          window.location.href = 'http://localhost:8000/login';
        }
      },
    },
    created() {
      this.redirectToDashboard();
    },
  };
  </script>
  