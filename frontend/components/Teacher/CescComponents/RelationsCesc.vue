<script setup>
import { computed, ref, watchEffect, onMounted } from "vue";
import VChart from "vue-echarts";
import { GraphChart } from "echarts/charts";
import { TooltipComponent, LegendComponent, GridComponent } from "echarts/components";
import { CanvasRenderer } from "echarts/renderers";
import { use } from "echarts/core";

use([GraphChart, TooltipComponent, LegendComponent, GridComponent, CanvasRenderer]);

const props = defineProps({
  evaluations: {
    type: Array,
    required: true,
    default: () => [],
  },
  tags: {
    type: Array,
    required: true,
    default: () => [],
  }
});

const selectedTag = ref(null);
const peerScores = ref(new Map());

// Procesar las evaluaciones para obtener las puntuaciones por peer_id
watchEffect(() => {
  if (!selectedTag.value && props.tags.length > 0) {
    selectedTag.value = props.tags[0].id;
  }

  const scoreMap = new Map();

  props.evaluations.forEach((evaluation) => {
    if (evaluation.peer_id && evaluation.tag_scores && evaluation.tag_scores[selectedTag.value]) {
      const currentScore = scoreMap.get(evaluation.peer_id) || {
        totalScore: 0,
        count: 0,
        name: evaluation.peer_name || `Student ${evaluation.peer_id}`,
      };

      currentScore.totalScore += evaluation.tag_scores[selectedTag.value];
      currentScore.count += 1;
      scoreMap.set(evaluation.peer_id, currentScore);
    }
  });

  peerScores.value = scoreMap;
});

// Datos para la visualización
const chartData = computed(() => {
  const data = [];
  peerScores.value.forEach((score, peerId) => {
    data.push({
      value: score.totalScore,
      name: score.name,
      itemStyle: {
        color: getScoreColor(score.totalScore)
      }
    });
  });
  return data.sort((a, b) => b.value - a.value); // Ordenar por puntuación
});

// Función para determinar el color basado en la puntuación
function getScoreColor(score) {
  if (score >= 8) return '#10B981'; // Verde para puntuaciones altas
  if (score >= 4) return '#3B82F6'; // Azul para puntuaciones medias
  return '#6B7280'; // Gris para puntuaciones bajas
}

// Opciones del gráfico
const chartOptions = computed(() => ({
  tooltip: {
    trigger: 'axis',
    axisPointer: {
      type: 'shadow'
    }
  },
  grid: {
    left: '3%',
    right: '4%',
    bottom: '3%',
    containLabel: true
  },
  xAxis: {
    type: 'value',
    boundaryGap: [0, 0.01]
  },
  yAxis: {
    type: 'category',
    data: chartData.value.map(item => item.name)
  },
  series: [
    {
      type: 'bar',
      data: chartData.value,
      label: {
        show: true,
        position: 'right',
        formatter: '{c}'
      }
    }
  ]
}));

// Manejar cambio de tag
const onTagChange = (tagId) => {
  selectedTag.value = tagId;
};
</script>

<template>
  <div class="max-w-6xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
      <!-- Selector de tag -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          Seleccionar Tag CESC:
        </label>
        <select 
          v-model="selectedTag"
          @change="onTagChange"
          class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        >
          <option 
            v-for="tag in tags" 
            :key="tag.id" 
            :value="tag.id"
          >
            {{ tag.name }}
          </option>
        </select>
      </div>

      <!-- Visualización de puntuaciones -->
      <div v-if="chartData.length > 0" class="w-full h-[600px]">
        <VChart
          class="w-full h-full"
          :option="chartOptions"
          autoresize
        />
      </div>

      <!-- Tabla de resultados -->
      <div class="mt-8">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Estudiante
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Puntuación Total
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Número de Evaluaciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="[peerId, score] in Array.from(peerScores)" :key="peerId">
              <td class="px-6 py-4 whitespace-nowrap">
                {{ score.name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ score.totalScore }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                {{ score.count }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="chartData.length === 0" class="p-12 text-center text-gray-600">
        <p class="text-xl font-medium">No hay datos de evaluación disponibles</p>
      </div>
    </div>
  </div>
</template>