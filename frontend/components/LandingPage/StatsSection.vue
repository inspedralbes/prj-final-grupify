<template>
  <div class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Main Stats Grid -->


    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

// Animated stats
const animatedStats = ref({
  users: 0,
  schools: 0,
  timeSaved: 0,
  satisfaction: 0
})

// Final values
const finalStats = {
  users: 15347,
  schools: 52,
  timeSaved: 480,
  satisfaction: 4.8
}

// Animation function
const animateValue = (obj, key, start, end, duration) => {
  let startTimestamp = null;
  const step = (timestamp) => {
    if (!startTimestamp) startTimestamp = timestamp;
    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
    
    // Easing function (ease out)
    const easedProgress = 1 - Math.pow(1 - progress, 3);
    
    obj[key] = Math.floor(easedProgress * (end - start) + start);
    
    if (progress < 1) {
      window.requestAnimationFrame(step);
    }
  };
  window.requestAnimationFrame(step);
}

// Start animations on mount
onMounted(() => {
  // Stagger the animations slightly
  setTimeout(() => animateValue(animatedStats.value, 'users', 0, finalStats.users, 2000), 100);
  setTimeout(() => animateValue(animatedStats.value, 'schools', 0, finalStats.schools, 1500), 300);
  setTimeout(() => animateValue(animatedStats.value, 'timeSaved', 0, finalStats.timeSaved, 1800), 500);
  setTimeout(() => animateValue(animatedStats.value, 'satisfaction', 0, Math.floor(finalStats.satisfaction * 10), 1600), 700);
})
</script>

<style scoped>
.stat-card {
  @apply bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1;
}

.stat-icon {
  @apply w-16 h-16 rounded-xl flex items-center justify-center;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .stat-card {
    @apply text-center;
  }
  
  .stat-icon {
    @apply mx-auto;
  }
}
</style>
