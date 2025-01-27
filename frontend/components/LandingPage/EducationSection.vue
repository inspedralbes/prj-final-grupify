<template>
  <div class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-semibold text-gray-900 text-center mb-12">
        Graus Educatius
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 justify-items-center">
        <div
          v-for="course in courses"
          :key="course.title"
          class="bg-white p-6 rounded-xl shadow-lg hover:shadow-xl transition-shadow duration-300 max-w-md w-full"
        >
          <div class="aspect-w-16 aspect-h-9 mb-6">
            <img
              :src="course.image"
              :alt="course.title"
              class="w-full h-48 object-cover rounded-lg shadow-md"
            />
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-4">
            {{ course.title }}
          </h3>
          <p class="text-gray-600 mb-6">{{ course.description }}</p>
          <ul class="space-y-2">
            <li
              v-for="(detail, index) in course.details"
              :key="index"
              class="text-gray-600 flex items-center"
            >
              <svg
                class="w-4 h-4 mr-2 text-indigo-600"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd"
                />
              </svg>
              {{ detail }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "EducationSection",
  data() {
    return {
      courses: [
        {
          title: "ESO",
          image: "/img/eso.jpg",
          description: "Descripció del primer grau educatiu.",
          details: ["1º ESO", "2º ESO", "3º ESO", "4º ESO"],
        },
        {
          title: "Batxillerat",
          image: "/img/bachillerato.jpg",
          description:
            "El Batxillerat és l'etapa de l'educació secundària postobligatòria que té per finalitat proporcionar l'alumnat:",
          details: [
            "Formació",
            "Madurez (intel·lectual i humana)",
            "Coneixements",
            "Destreces",
          ],
        },
      ],
    };
  },
};
</script>

<style scoped>
.aspect-w-16 {
  position: relative;
  padding-bottom: 56.25%;
}

.aspect-w-16 > img {
  position: absolute;
  height: 100%;
  width: 100%;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
</style>
