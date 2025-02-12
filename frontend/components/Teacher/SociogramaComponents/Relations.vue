<script setup>
import { computed, ref, watchEffect, onMounted } from "vue";
import VChart from "vue-echarts";
import { GraphChart } from "echarts/charts";
import { TooltipComponent, LegendComponent } from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";
import { use } from "echarts/core";

// Configurar ECharts
use([GraphChart, TooltipComponent, LegendComponent, CanvasRenderer]);

const props = defineProps({
  relationships: {
    type: Array,
    required: true,
    default: () => [],
  },
});

const graphData = ref({ nodes: [], links: [] });
const highlightedNodeId = ref(null); // Almacena el ID del nodo resaltado
const chartInstance = ref(null); // Referencia a la instancia del gráfico

watchEffect(() => {
  const nodesMap = new Map();
  const links = [];

  props.relationships.forEach((rel) => {
    if (rel.user_id && rel.user_name && rel.user_last_name) {
      nodesMap.set(rel.user_id, {
        id: String(rel.user_id),
        name: `${rel.user_name} ${rel.user_last_name}`,
        symbolSize: 25,
        itemStyle: { color: "#3b82f6" },
      });
    }
    if (rel.peer_id && rel.peer_name && rel.peer_last_name) {
      nodesMap.set(rel.peer_id, {
        id: String(rel.peer_id),
        name: `${rel.peer_name} ${rel.peer_last_name}`,
        symbolSize: 25,
        itemStyle: { color: "#3b82f6" },
      });
    }

    if (nodesMap.has(rel.user_id) && nodesMap.has(rel.peer_id)) {
      const color = rel.relationship_type === "negative" ? "#EF4444" : "#10B981";
      links.push({
        source: String(rel.user_id),
        target: String(rel.peer_id),
        lineStyle: { color: color, width: 3, opacity: 0 }, // Líneas ocultas inicialmente
        label: {
          show: true,
          formatter: rel.relationship_type === "negative" ? "" : "",
          color: color,
          fontSize: 14,
          fontWeight: "bold",
        },
      });
    }
  });

  graphData.value = { nodes: Array.from(nodesMap.values()), links };
});

// Función para manejar el evento "mouseover" en los nodos
const onChartMouseOver = (params) => {
  if (params.dataType === "node") {
    highlightedNodeId.value = params.data.id; // Almacena el ID del nodo resaltado
  }
};

// Función para manejar el evento "mouseout" en los nodos
const onChartMouseOut = () => {
  highlightedNodeId.value = null; // Restablece el nodo resaltado
};

// Opciones del gráfico
const chartOptions = computed(() => ({
  tooltip: {
    trigger: "item",
    formatter: (params) => {
      if (params.dataType === "node") return `<b>${params.data.name}</b>`;
      if (params.dataType === "edge");
    },
  },
  animation: false, // Desactivar animaciones para evitar movimientos innecesarios
  series: [
    {
      type: "graph",
      layout: "force",
      force: {
        initLayout: "circular", // Diseño inicial circular para distribuir los nodos
        repulsion: 1000, // Reducir la repulsión para que los nodos no se alejen demasiado
        edgeLength: 300, // Reducir la longitud de los enlaces
        gravity: 0.5, // Aumentar la gravedad para que los nodos se agrupen más
        friction: 0.6, // Añadir fricción para reducir la velocidad de movimiento
        layoutAnimation: false, // Desactivar animación del diseño de fuerza
      },
      roam: true,
      draggable: true,
      data: graphData.value.nodes,
      links: graphData.value.links.map((link) => ({
        ...link,
        lineStyle: {
          curveness: 0.2,
          ...link.lineStyle,
          opacity: highlightedNodeId.value
            ? link.source === highlightedNodeId.value || link.target === highlightedNodeId.value
              ? 1 // Mostrar líneas relacionadas con el nodo resaltado
              : 0 // Ocultar otras líneas
            : 0, // Ocultar todas las líneas si no hay nodo resaltado
        },
      })),
      edgeSymbol: ["circle", "arrow"],
      edgeSymbolSize: [4, 10],
      label: {
        show: true,
        position: "right",
        fontSize: 12,
      },
    },
  ],
}));

// Detener el diseño de fuerza después de que los nodos se hayan posicionado
const stopForceLayout = () => {
  if (chartInstance.value) {
    const chart = chartInstance.value.getEchartsInstance();
    chart.setOption({
      series: [
        {
          type: "graph",
          layout: "none", // Desactivar el diseño de fuerza
        },
      ],
    });
  }
};

// Inicializar el gráfico y detener el diseño de fuerza después de un tiempo
onMounted(() => {
  setTimeout(stopForceLayout, 1000); // Detener el diseño de fuerza después de 1 segundo
});
</script>

<template>
  <div class="space-y-8 mt-8">
      <div v-if="graphData.nodes.length > 0" class="w-full h-[600px] bg-white rounded-2xl shadow-xl p-8 transform transition-all duration-300 hover:shadow-2xl hover:scale-105 backdrop-blur-sm bg-opacity-90">
        <VChart
          ref="chartInstance"
          class="w-full h-full"
          :option="chartOptions"
          @mouseover="onChartMouseOver"
          @mouseout="onChartMouseOut"
        />
      </div>
      
      <div
        v-else
        class="bg-white rounded-3xl shadow-xl p-12 text-center transform transition-all duration-500 hover:shadow-2xl backdrop-blur-sm bg-opacity-90"
      >
        <svg
          class="w-20 h-20 mx-auto text-gray-400 mb-4"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <p class="text-sm text-gray-600 font-medium">
          No hi ha dades de rols disponibles
        </p>
      </div>
    </div>
  
</template>