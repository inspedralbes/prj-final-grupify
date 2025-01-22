<script setup>
import {
  PlusIcon,
  UserGroupIcon,
  EyeIcon,
  EyeSlashIcon,
} from "@heroicons/vue/24/outline";

const router = useRouter();
const searchQuery = ref("");
const selectedDivision = ref("all");
const selectedDate = ref("all");
const showAssignModal = ref(false);
const selectedForm = ref(null);
const forms = ref([]);

const toastMessage = ref("");
const toastType = ref("success"); // Tipo de toast (success, error, info, etc.)
const showToast = ref(false); // Variable para controlar la visibilidad del toast

onMounted(async () => {
  try {
    const response = await fetch("http://localhost:8000/api/forms", {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    if (!response.ok) {
      throw new Error("Error obteniendo los datos.");
    }

    forms.value = await response.json();
  } catch (error) {
    console.error("Error:", error);
  }
});

const updateFormStatus = async (formId, newStatus) => {
  try {
    const response = await fetch(
      `http://localhost:8000/api/forms/${formId}/status`,
      {
        method: "PATCH",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
          Authorization: `Bearer ${localStorage.getItem("auth_token")}`,
        },
        body: JSON.stringify({
          status: newStatus,
        }),
      }
    );

    if (!response.ok) {
      throw new Error("Error al actualizar el estado del formulario.");
    }

    forms.value = forms.value.map(form =>
      form.id === formId ? { ...form, status: newStatus } : form
    );
  } catch (error) {
    console.error("Error:", error);
  }
};

const viewUsersAnswered = formId => {
  navigateTo(`/professor/formularis/respostes/${formId}`);
};

const openAssignModal = form => {
  selectedForm.value = form;
  showAssignModal.value = true;
};

const handleFormAssigned = assignments => {
  // console.log('Form assigned to students:', assignments);

  // Actualiza el mensaje y tipo de toast
  toastMessage.value =
    "Formulario asignado correctamente a los estudiantes seleccionados";
  toastType.value = "success"; // Tipo de mensaje (éxito)

  // Muestra el toast
  showToast.value = true;

  // Ocultar el toast después de 3 segundos (simulando un timeout)
  setTimeout(() => {
    showToast.value = false;
    toastMessage.value = ""; // Limpiar el mensaje
    toastType.value = ""; // Limpiar tipo
  }, 3000);
};
</script>

<template>
  <div class="p-6">
    <!-- Contenedor del título y botón de volver -->
    <div class="relative flex items-center mb-6">
      <button
        class="absolute left-0 flex items-center space-x-1 text-gray-700 hover:text-gray-900"
        @click="navigateTo('/professor/dashboard')"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-5 w-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M15 19l-7-7 7-7"
          />
        </svg>
        <span>Tornar</span>
      </button>

      <h1 class="flex-grow text-center text-2xl font-bold">Formularis</h1>

      <button
        class="absolute right-0 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 flex items-center space-x-2"
        @click="navigateTo('/professor/formularis/nou')"
      >
        <PlusIcon class="w-5 h-5" />
        <span>Nou Formulari</span>
      </button>
    </div>

    <!-- Mostrar el Toast si showToast es verdadero -->
    <CommonToast v-if="showToast" :message="toastMessage" :type="toastType" />

    <!-- El resto del contenido sigue igual -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Buscar formularis..."
            class="w-full px-4 py-2 border rounded-lg"
          />
        </div>
        <div class="flex space-x-4">
          <select
            v-model="selectedDivision"
            class="px-4 py-2 border rounded-lg"
          >
            <option value="all">Tots los estats</option>
            <option value="active">Actius</option>
            <option value="draft">Borradors</option>
            <option value="closed">Tancats</option>
          </select>
          <select v-model="selectedDate" class="px-4 py-2 border rounded-lg">
            <option value="all">Totes les dates</option>
            <option value="today">Avui</option>
            <option value="week">Aquesta setmana</option>
            <option value="month">Aquest mes</option>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Títol
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Estat
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Respostes
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Data
              </th>
              <th
                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Accions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="form in forms" :key="form.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">
                  {{ form.title }}
                </div>
                <div class="text-sm text-gray-500">{{ form.description }}</div>
              </td>
              <td class="px-6 py-4">
                <span
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="{
                    'bg-green-100 text-green-800': form.status === 1,
                    'bg-red-100 text-red-800': form.status === 0,
                  }"
                >
                  {{ form.status === 1 ? "actiu" : "inactiu" }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ form.responses_count }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ new Date(form.created_at).toLocaleDateString() }}
              </td>
              <td class="px-6 py-4 text-right text-sm font-medium">
                <div class="flex justify-end space-x-3">
                  <button
                    class="flex items-center space-x-1 px-3 py-1 bg-primary text-white rounded-md hover:bg-primary/90 transition-colors"
                    title="Asignar a estudiantes"
                    @click="openAssignModal(form)"
                  >
                    <UserGroupIcon class="w-4 h-4" />
                    <span>Assignar</span>
                  </button>
                  <button
                    class="text-gray-400 hover:text-primary"
                    @click="
                      updateFormStatus(form.id, form.status === 1 ? 0 : 1)
                    "
                  >
                    <!-- Cambiar el ícono dependiendo del estado del formulario -->
                    <component
                      :is="form.status === 0 ? EyeIcon : EyeSlashIcon"
                      class="w-5 h-5"
                    />
                  </button>
                  <button @click="viewUsersAnswered(form.id)">
                    Ver respuestas
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <FormsAssignFormModal
      v-model="showAssignModal"
      :form="selectedForm || {}"
      @assigned="handleFormAssigned"
    />
  </div>
</template>
