<script setup>
import { ref, defineProps, computed, onMounted, watch } from "vue";
import VChart from "vue-echarts";
import { ScatterChart } from "echarts/charts";
import { TooltipComponent, LegendComponent } from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";
import { use } from "echarts/core";

// Configurar ECharts
use([ScatterChart, TooltipComponent, LegendComponent, CanvasRenderer]);

// Recibir datos de los roles
const props = defineProps({
  filteredRoles: {
    type: Array,
    required: true,
  },
});

// Variable reactiva para almacenar los datos de roles
const rolesData = ref([]);
const chartKey = ref(0); // Clave reactiva para forzar la actualización del gráfico

// Guardar datos en el almacenamiento local
const saveToLocalStorage = (roles) => {
  localStorage.setItem("rolesData", JSON.stringify(roles));
};

// Cargar datos del almacenamiento local
const loadFromLocalStorage = () => {
  const storedData = localStorage.getItem("rolesData");
  if (storedData) {
    rolesData.value = JSON.parse(storedData);
  } else if (props.filteredRoles.length > 0) {
    rolesData.value = props.filteredRoles;
    saveToLocalStorage(props.filteredRoles);
  }
};

// Función para determinar la categoría según las puntuaciones
const getCategory = (role) => {
  if (role.aïllament > role.popularitat) return "Aïllament";
  if (role.popularitat > role.aïllament) return "Popularitat";
  return "Neutral";
};

// Transformar datos para el gráfico con categorías separadas
const chartOptions = computed(() => {
  if (rolesData.value.length === 0) return {}; // Evitar errores si no hay datos

  return {
    tooltip: {
      trigger: "item",
      formatter: (params) => `${params.name}`,
    },
    legend: {
      data: ["Aïllament", "Popularitat", "Neutral"],
      bottom: -5, // Posición de la leyenda
    },
    grid: {
      bottom: 100, // Aumenta el espacio debajo del gráfico para los nombres
    },
    xAxis: {
      type: "category",
      data: rolesData.value.map((role) => `${role.peer_name} ${role.peer_last_name}`),
      axisLabel: { rotate: 45 },
    },
    yAxis: { type: "value" },
    series: [
      {
        name: "Aïllament",
        type: "scatter",
        symbolSize: (data) => data[2] * 17,
        itemStyle: { color: "red" },
        data: rolesData.value
          .filter((role) => getCategory(role) === "Aïllament")
          .map((role) => ({
            name: `${role.peer_name} ${role.peer_last_name}`,
            value: [`${role.peer_name} ${role.peer_last_name}`, Math.max(role.aïllament, role.popularitat), Math.max(role.aïllament, role.popularitat)],
          })),
      },
      {
        name: "Popularitat",
        type: "scatter",
        symbolSize: (data) => data[2] * 17,
        itemStyle: { color: "green" },
        data: rolesData.value
          .filter((role) => getCategory(role) === "Popularitat")
          .map((role) => ({
            name: `${role.peer_name} ${role.peer_last_name}`,
            value: [`${role.peer_name} ${role.peer_last_name}`, Math.max(role.aïllament, role.popularitat), Math.max(role.aïllament, role.popularitat)],
          })),
      },
      {
        name: "Neutral",
        type: "scatter",
        symbolSize: (data) => data[2] * 17,
        itemStyle: { color: "yellow" },
        data: rolesData.value
          .filter((role) => getCategory(role) === "Neutral")
          .map((role) => ({
            name: `${role.peer_name} ${role.peer_last_name}`,
            value: [`${role.peer_name} ${role.peer_last_name}`, Math.max(role.aïllament, role.popularitat), Math.max(role.aïllament, role.popularitat)],
          })),
      },
    ],
  };
});

// Cargar los datos al montar el componente
onMounted(() => {
  loadFromLocalStorage();
});

// Actualizar rolesData cuando filteredRoles cambie
watch(() => props.filteredRoles, (newRoles) => {
  if (newRoles.length > 0) {
    rolesData.value = newRoles;
    saveToLocalStorage(newRoles);
  }
}, { immediate: true });

// Forzar la actualización del gráfico cuando rolesData cambie
watch(() => rolesData.value, () => {
  chartKey.value++; // Incrementar la clave para forzar la actualización
}, { deep: true });
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-xl p-8">
      <client-only>
        <VChart
          v-if="rolesData.length > 0"
          :key="chartKey"
          class="w-full h-96"
          :option="chartOptions"
        />
      </client-only>
    </div>
  </div>
</template>
