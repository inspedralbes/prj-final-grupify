<template>
  <div
    v-if="isVisible"
    class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center"
    @click.self="$emit('close')"
  >
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-xl w-full">
      <h2 class="text-3xl font-semibold text-gray-900 text-center mb-8">
        Formulario de Contacto
      </h2>
      <form class="space-y-6" @submit.prevent="handleSubmit">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div>
            <label
              for="first-name"
              class="block text-sm font-medium text-gray-700"
              >Nom</label
            >
            <input
              id="first-name"
              v-model="form.firstName"
              type="text"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
            />
          </div>
          <div>
            <label
              for="last-name"
              class="block text-sm font-medium text-gray-700"
              >Cognom</label
            >
            <input
              id="last-name"
              v-model="form.lastName"
              type="text"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
            />
          </div>
        </div>
        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700"
            >Número de Telèfon</label
          >
          <input
            id="phone"
            v-model="form.phone"
            type="tel"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
          />
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700"
            >Email</label
          >
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
          />
        </div>
        <div class="flex justify-center">
          <button
            type="submit"
            class="rounded-md bg-primary px-6 py-2 text-white rounded font-semibold hover:bg-primary/90 transition-colors"
          >
            Enviar
          </button>
        </div>
      </form>
      <button
        class="absolute top-4 right-4 text-gray-500 hover:text-gray-700"
        @click="$emit('close')"
      >
        <span class="text-xl">×</span>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: "ContactForm",
  props: {
    isVisible: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      form: {
        firstName: "",
        lastName: "",
        phone: "",
        email: "",
      },
    };
  },
  methods: {
    handleSubmit() {
      alert("Formulari enviat correctament!");
      this.$emit("close");
    },
  },
};
</script>
