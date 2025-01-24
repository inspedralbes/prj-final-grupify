<script setup>
import { TrashIcon, ClockIcon, XMarkIcon } from "@heroicons/vue/24/outline";
const { connect, sendNotification, socket } = useSocket();
const notificationText = ref("");
const sentNotifications = ref([]);
const showConfirmModal = ref(false);
const selectedPriority = ref("normal");
const scheduledDateTime = ref("");
const pendingNotification = ref(null);
const showClearConfirm = ref(false);
const showDeleteModal = ref(false);
const notificationToDelete = ref(null);

const formatDate = dateString => {
  return new Date(dateString).toLocaleDateString("ca-ES", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
};

onMounted(() => {
  connect();
  loadStoredNotifications();
  startScheduleChecker();
});

const loadStoredNotifications = () => {
  const saved = localStorage.getItem("professorNotifications");
  if (saved) sentNotifications.value = JSON.parse(saved);

  const scheduled = localStorage.getItem("scheduledNotifications");
  if (scheduled) scheduledNotifications.value = JSON.parse(scheduled);
};

const scheduledNotifications = ref([]);
const startScheduleChecker = () => {
  setInterval(() => {
    const now = new Date().getTime();
    scheduledNotifications.value = scheduledNotifications.value.filter(
      notification => {
        if (new Date(notification.scheduledAt).getTime() <= now) {
          sendScheduledNotification(notification);
          return false;
        }
        return true;
      }
    );
    localStorage.setItem(
      "scheduledNotifications",
      JSON.stringify(scheduledNotifications.value)
    );
  }, 60000);
};

const sendScheduledNotification = notification => {
  sendNotification(notification.message, notification.priority);
  sentNotifications.value = sentNotifications.value.map(n =>
    n.id === notification.id ? { ...n, status: "delivered" } : n
  );
  saveNotifications();
};

const saveNotifications = () => {
  localStorage.setItem(
    "professorNotifications",
    JSON.stringify(sentNotifications.value)
  );
};

const prepareSend = () => {
  if (!notificationText.value.trim()) return;

  pendingNotification.value = {
    message: notificationText.value.trim(),
    priority: selectedPriority.value,
    scheduledAt: scheduledDateTime.value,
  };

  showConfirmModal.value = true;
};

const confirmSend = async () => {
  showConfirmModal.value = false;

  const notificationData = {
    ...pendingNotification.value,
    id: `sent-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    timestamp: new Date().toISOString(),
    status: "pending",
    deliveredCount: 0,
  };

  if (notificationData.scheduledAt) {
    scheduledNotifications.value = [
      notificationData,
      ...scheduledNotifications.value,
    ];
    localStorage.setItem(
      "scheduledNotifications",
      JSON.stringify(scheduledNotifications.value)
    );
    notificationData.status = "scheduled";
  } else {
    await sendNotification(notificationData.message, notificationData.priority);
    notificationData.status = "delivered";
  }

  sentNotifications.value = [notificationData, ...sentNotifications.value];
  saveNotifications();

  notificationText.value = "";
  selectedPriority.value = "normal";
  scheduledDateTime.value = "";
};

const handleClearHistory = () => {
  sentNotifications.value = [];
  scheduledNotifications.value = [];
  localStorage.removeItem("professorNotifications");
  localStorage.removeItem("scheduledNotifications");
  showClearConfirm.value = false;
};

const deleteScheduledNotification = notificationId => {
  sentNotifications.value = sentNotifications.value.filter(
    n => n.id !== notificationId
  );
  scheduledNotifications.value = scheduledNotifications.value.filter(
    n => n.id !== notificationId
  );

  localStorage.setItem(
    "professorNotifications",
    JSON.stringify(sentNotifications.value)
  );
  localStorage.setItem(
    "scheduledNotifications",
    JSON.stringify(scheduledNotifications.value)
  );

  showDeleteModal.value = false;
};

const priorityOptions = [
  { value: "low", label: "Baixa", color: "bg-gray-200" },
  { value: "normal", label: "Normal", color: "bg-blue-200" },
  { value: "high", label: "Alta", color: "bg-red-200" },
];
</script>

<template>
  <div class="notification-panel max-w-2xl mx-auto">
    <div class="bg-white p-6 rounded-lg shadow-md relative">
      <h3 class="text-xl font-bold mb-4">Enviar Notificació</h3>

      <textarea
        v-model="notificationText"
        placeholder="Escriu el teu missatge..."
        class="w-full p-2 border rounded mb-4 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        rows="4"
      ></textarea>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block text-sm font-medium mb-2">Prioritat</label>
          <select v-model="selectedPriority" class="w-full p-2 border rounded">
            <option v-for="option in priorityOptions" :value="option.value">
              {{ option.label }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2"
            >Programar notificació (opcional)</label
          >
          <input
            v-model="scheduledDateTime"
            type="datetime-local"
            class="w-full p-2 border rounded"
          />
        </div>
      </div>

      <button
        @click="prepareSend"
        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors disabled:opacity-50"
        :disabled="!socket?.connected || !notificationText.trim()"
      >
        Enviar notificació
      </button>

      <!-- Modal de confirmació per enviar-->
      <div
        v-if="showConfirmModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-bold mb-4">Confirmar</h3>
          <p class="mb-2">
            <strong>Prioritat:</strong>
            {{
              priorityOptions.find(
                o => o.value === pendingNotification.priority
              )?.label
            }}
          </p>
          <p v-if="pendingNotification.scheduledAt" class="mb-4">
            <strong>Programat per:</strong>
            {{ formatDate(pendingNotification.scheduledAt) }}
          </p>
          <pre
            class="bg-gray-100 p-4 rounded mb-6 max-h-60 overflow-auto whitespace-pre-wrap break-words text-sm"
            >{{ pendingNotification.message }}</pre
          >

          <div class="flex justify-end gap-2">
            <button
              @click="showConfirmModal = false"
              class="px-4 py-2 border rounded hover:bg-gray-50 transition-colors"
            >
              Cancel·lar
            </button>
            <button
              @click="confirmSend"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
            >
              Confirmar
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de confirmació d'esborrat total -->
      <div
        v-if="showClearConfirm"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-bold mb-4">Esborrar tot l'historial?</h3>
          <p class="text-gray-600 mb-6">Aquesta acció no es pot desfer.</p>
          <div class="flex justify-end gap-2">
            <button
              @click="showClearConfirm = false"
              class="px-4 py-2 border rounded hover:bg-gray-50 transition-colors"
            >
              Cancel·lar
            </button>
            <button
              @click="handleClearHistory"
              class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors"
            >
              Esborrar tot
            </button>
          </div>
        </div>
      </div>

      <!-- Modal de confirmació d'esborrat individual -->
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-bold mb-4">
            Eliminar notificació programada?
          </h3>
          <p class="text-gray-600 mb-6">Aquesta acció no es pot desfer.</p>
          <div class="flex justify-end gap-2">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 border rounded hover:bg-gray-50 transition-colors"
            >
              Cancel·lar
            </button>
            <button
              @click="deleteScheduledNotification(notificationToDelete.id)"
              class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors"
            >
              Eliminar
            </button>
          </div>
        </div>
      </div>

      <!-- Historial -->
      <div class="mt-8">
        <div class="flex justify-between items-center mb-4">
          <h4 class="text-lg font-semibold">Notificacions Programades</h4>
          <button
            v-if="sentNotifications.length > 0"
            @click="showClearConfirm = true"
            class="text-red-500 hover:text-red-700 text-sm flex items-center"
          >
            <TrashIcon class="h-4 w-4 mr-1" />
            Esborrar tot
          </button>
        </div>

        <div
          v-if="sentNotifications.length === 0"
          class="text-center text-gray-500 py-4"
        >
          <div class="space-y-2">
            <ClockIcon class="h-8 w-8 mx-auto text-gray-400" />
            <p>No hi ha notificacions programades</p>
          </div>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="notification in sentNotifications"
            :key="notification.id"
            class="p-4 bg-gray-50 rounded-lg border hover:shadow transition-shadow relative group"
          >
            <button
              v-if="notification.status === 'scheduled'"
              @click="
                notificationToDelete = notification;
                showDeleteModal = true;
              "
              class="absolute top-2 right-2 text-red-500 hover:text-red-700"
            >
              <XMarkIcon class="h-5 w-5" />
            </button>

            <div class="flex items-center gap-2 mb-2">
              <span
                :class="[
                  'px-2 py-1 text-xs rounded-full',
                  priorityOptions.find(o => o.value === notification.priority)
                    ?.color,
                ]"
              >
                {{
                  priorityOptions.find(o => o.value === notification.priority)
                    ?.label
                }}
              </span>
              <span
                v-if="notification.scheduledAt"
                class="text-xs text-blue-600 flex items-center"
              >
                <ClockIcon class="h-4 w-4 mr-1" />
                {{ formatDate(notification.scheduledAt) }}
              </span>
            </div>

            <p class="text-gray-800 whitespace-pre-wrap pr-8">
              {{ notification.message }}
            </p>
            <p class="text-sm text-gray-500 mt-2">
              {{ formatDate(notification.timestamp) }}
              <span
                :class="{
                  'text-green-600': notification.status === 'delivered',
                  'text-gray-500': notification.status !== 'delivered',
                }"
              >
                {{
                  notification.status === "delivered"
                    ? "✓ Lliurada"
                    : "⏳ Programada"
                }}
              </span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.notification-panel {
  min-height: calc(100vh - 128px);
  padding: 2rem 1rem;
}

pre {
  max-width: 100%;
  overflow-x: auto;
  font-family: inherit;
  line-height: 1.5;
  padding: 0.75rem;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.group:hover .group-hover\:opacity-100 {
  transition: opacity 0.2s ease-in-out;
}
</style>
