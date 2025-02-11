import { defineNuxtPlugin } from '#app'
import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { BarChart, LineChart } from 'echarts/charts'
import { GridComponent, TooltipComponent, LegendComponent } from 'echarts/components'

export default defineNuxtPlugin(() => {
  use([CanvasRenderer, BarChart, LineChart, GridComponent, TooltipComponent, LegendComponent])
})
