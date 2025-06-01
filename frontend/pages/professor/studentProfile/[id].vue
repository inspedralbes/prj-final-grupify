<script setup>
import { useRoute } from "vue-router";
import { onMounted, ref, nextTick, watch } from "vue";
import DashboardNavTeacher from "@/components/Teacher/DashboardNavTeacher.vue";
import { useStudentsStore } from "@/stores/studentsStore"; // Asegúrate de tener esta store configurada correctamente

const route = useRoute();
const studentsStore = useStudentsStore();
const student = ref(null);
const isLoading = ref(true);
const error = ref(null);
const studentId = route.params.id;
const storedUser = localStorage.getItem("user"); // Obtener el usuario almacenado en localStorage
// Estado para manejar el modal de "Donar de Baixa" y los motivos seleccionados
const showBajaModal = ref(false);
const selectedReason = ref("");
const reasons = ["Falta de assistència", "Baixa voluntària", "Altres motius"];
const comments = ref([]);
const newComment = ref(""); // Ensure newComment is defined
const editingComment = ref(null); // Ensure editingComment is defined
// Esta variable se inicia en false para que el gráfico esté oculto por defecto
const isFormVisible = ref(false);
// Variable para almacenar los datos de la respuesta y poder usarlos cuando se despliega el gráfico
const datosRespuestas = ref(null);
const hasAnsweredForm4 = ref(false); // Estado para saber si ha respondido el formulario 4
const isLoadingGraph = ref(false); // Estado para mostrar un indicador de carga durante la obtención de datos del gráfico

const competences = [
  { id: 22, name: "Responsabilitat" },
  { id: 23, name: "Treball en equip" },
  { id: 24, name: "Gestió del temps" },
  { id: 25, name: "Comunicació" },
  { id: 26, name: "Adaptabilitat" },
  { id: 27, name: "Lideratge" },
  { id: 28, name: "Creativitat" },
  { id: 29, name: "Proactivitat" },
];

let teacherId = null;

if (storedUser) {
  try {
    const parsedUser = JSON.parse(storedUser);
    teacherId = parsedUser.id; // Capturar teacherId global
  } catch (e) {
    console.error("Error al analizar el JSON:", e);
  }
}

// Función de respaldo para obtener las respuestas del alumno (Formulario de autoavaliación id4)
async function obtenerDatosAlumno(studentId) {
  try {
    console.log("Método de respaldo: Obteniendo datos del formulario 4 para el estudiante ID:", studentId);

    // URL principal según el controlador del backend (AnswerController)
    // La ruta correcta debe ser: /api/forms/{formId}/users/{userId}/answers
    const url = `https://api.grupify.cat/api/forms/4/users/${studentId}/answers`;

    console.log("Realizando petición a:", url);

    // Realizar petición con opciones optimizadas
    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json"
      },
      cache: "no-cache"
    });

    if (!response.ok) {
      console.error(`Error en la respuesta: ${response.status} - ${response.statusText}`);
      return [];
    }

    // Procesar la respuesta
    const data = await response.json();
    console.log("Datos recibidos del backend:", data);

    // Extraer las respuestas
    let answers = [];
    if (data && data.answers) {
      answers = data.answers;
    } else if (Array.isArray(data)) {
      answers = data;
    }

    // Si no hay respuestas, devolver array vacío
    if (!answers || answers.length === 0) {
      console.log("No se encontraron respuestas para el estudiante:", studentId);
      return [];
    }

    // Mapear las respuestas a las competencias
    const mappedAnswers = competences.map(competence => {
      // Buscar la respuesta correspondiente a esta competencia específica
      const answer = answers.find(a =>
        a.question_id === competence.id ||
        a.questionId === competence.id
      );

      // Obtener el valor de calificación
      let rating = 0;
      if (answer) {
        // Intentar obtener el valor de diferentes propiedades posibles
        if (answer.rating !== undefined) {
          rating = Number(answer.rating);
        } else if (answer.answer !== undefined) {
          rating = Number(answer.answer);
        } else if (answer.value !== undefined) {
          rating = Number(answer.value);
        }
      }

      // Validar que el rating sea un número válido
      if (isNaN(rating) || rating < 0) {
        rating = 0;
      } else if (rating > 5) {
        rating = 5;
      }

      return {
        ...competence,
        rating: rating
      };
    });

    console.log("Respuestas mapeadas:", mappedAnswers);

    // Verificar si hay respuestas válidas (al menos una con valor > 0)
    const hasAnswers = mappedAnswers.some(answer => answer.rating > 0);

    // Actualizar el estado global
    hasAnsweredForm4.value = hasAnswers;

    return mappedAnswers;
  } catch (error) {
    console.error("Error en el método de respaldo para obtener datos:", error);
    return [];
  }
}
// Función para actualizar el gráfico de autoavaluación
function actualizarGrafico(respuestas) {
  console.log("Actualizando gráfico con respuestas para estudiante ID:", studentId);
  console.log("Datos de respuestas:", respuestas);

  // Usamos setTimeout para dar tiempo a que el DOM esté listo
  setTimeout(() => {
    try {
      // Obtener referencias al SVG y sus elementos
      const svg = document.getElementById("radial-graph");
      if (!svg) {
        console.error("No se encontró el elemento SVG principal (id: radial-graph)");
        return;
      }

      const dataPointsElement = svg.querySelector("#data-points");
      const areaDataElement = svg.querySelector("#area-data");

      if (!dataPointsElement || !areaDataElement) {
        console.error("No se encontraron los elementos necesarios del SVG (#data-points o #area-data)");
        return;
      }

      // Limpiar elementos anteriores
      dataPointsElement.innerHTML = "";
      let areaPath = "";

      // Ajustar el radio base y el factor de escala
      const baseRadius = 80; // Radio máximo del gráfico
      const centerPoint = 100; // Centro del SVG

      // Coordenadas base para cada dirección (normalizada)
      const coordenadas = [
        { x: 0, y: -1 }, // Responsabilitat (arriba) - 22
        { x: 0.707, y: -0.707 }, // Treball equip (arriba-derecha) - 23
        { x: 1, y: 0 }, // Gestió temps (derecha) - 24
        { x: 0.707, y: 0.707 }, // Comunicació (abajo-derecha) - 25
        { x: 0, y: 1 }, // Adaptabilitat (abajo) - 26
        { x: -0.707, y: 0.707 }, // Lideratge (abajo-izquierda) - 27
        { x: -1, y: 0 }, // Creativitat (izquierda) - 28
        { x: -0.707, y: -0.707 }, // Proactivitat (arriba-izquierda) - 29
      ];

      // Definición de competencias con sus IDs y nombres
      const competenciasDefinidas = [
        { id: 22, name: "Responsabilitat", index: 0 },
        { id: 23, name: "Treball en equip", index: 1 },
        { id: 24, name: "Gestió del temps", index: 2 },
        { id: 25, name: "Comunicació", index: 3 },
        { id: 26, name: "Adaptabilitat", index: 4 },
        { id: 27, name: "Lideratge", index: 5 },
        { id: 28, name: "Creativitat", index: 6 },
        { id: 29, name: "Proactivitat", index: 7 },
      ];

      // Asegurarse de que cada competencia definida tenga un valor
      const respuestasOrdenadas = competenciasDefinidas.map(competenciaDefinida => {
        // Buscar esta competencia en las respuestas recibidas
        const respuesta = respuestas.find(r => {
          // Intentar hacer coincidir por ID
          if (r.id === competenciaDefinida.id ||
            r.question_id === competenciaDefinida.id) {
            return true;
          }

          // O por nombre si no hay coincidencia por ID
          return r.name === competenciaDefinida.name;
        });

        // Obtener el valor de rating (0 si no hay respuesta)
        let rating = 0;

        if (respuesta) {
          // Intentar obtener el rating de diferentes propiedades
          if (respuesta.rating !== undefined) {
            rating = Number(respuesta.rating);
          } else if (respuesta.answer !== undefined) {
            rating = Number(respuesta.answer);
          } else if (respuesta.value !== undefined) {
            rating = Number(respuesta.value);
          }

          // Validar el rating (entre 0 y 5)
          if (isNaN(rating) || rating < 0) {
            rating = 0;
          } else if (rating > 5) {
            rating = 5;
          }
        }

        // Devolver la competencia con su valor de rating
        return {
          ...competenciaDefinida,
          rating: rating
        };
      });

      // Log para depuración específico para este estudiante
      console.log(`Valores finales del gráfico para estudiante ${studentId}:`);
      respuestasOrdenadas.forEach((r, idx) => {
        console.log(`${idx + 1}: ${r.name} (ID: ${r.id}) = ${r.rating}`);
      });

      // Dibujar los puntos y el área
      respuestasOrdenadas.forEach((respuesta, index) => {
        const rating = respuesta.rating || 0;
        // Calcular el radio exacto basado en una escala de 1-5
        const radio = (rating / 5) * baseRadius;

        // Calcular las coordenadas exactas desde el centro
        const x = centerPoint + coordenadas[index].x * radio;
        const y = centerPoint + coordenadas[index].y * radio;

        // Crear punto
        const point = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "circle"
        );
        point.setAttribute("cx", x);
        point.setAttribute("cy", y);
        point.setAttribute("r", "4");
        point.setAttribute("fill", "#00ADEE");

        // Añadir título al punto con el valor exacto
        const title = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "title"
        );
        title.textContent = `${respuesta.name}: ${rating}`;
        point.appendChild(title);

        dataPointsElement.appendChild(point);

        // Construir el path para el área
        if (index === 0) {
          areaPath = `M ${x},${y}`;
        } else {
          areaPath += ` L ${x},${y}`;
        }
      });

      // Cerrar el path del área
      if (areaPath) {
        areaPath += " Z";
        areaDataElement.setAttribute("d", areaPath);
        console.log(`Área del gráfico actualizada correctamente para el estudiante ${studentId}`);
      } else {
        console.warn(`No se generó path para el área del gráfico del estudiante ${studentId}`);
      }
    } catch (error) {
      console.error(`Error al actualizar el gráfico para estudiante ${studentId}:`, error);
    }
  }, 300); // Más tiempo para garantizar que el DOM esté listo
}
// Función para confirmar la baja
const handleBaja = async () => {
  if (!selectedReason.value) {
    alert("Selecciona un motiu per donar de baixa.");
    return;
  }

  try {
    // Enviar el estado actualizado al backend
    const response = await fetch(
      `https://api.grupify.cat/api/user/${student.value.id}/status`,
      {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          status: 0, // 0 para inactivo
        }),
      }
    );

    if (!response.ok) {
      throw new Error("Error al actualizar el estado del estudiante.");
    }

    // Cambiar el estado localmente después de la respuesta
    student.value.status = 0;

    // Actualizar el estado en la tienda si es necesario
    studentsStore.updateStudent({
      ...student.value,
      status: 0,
      reason: selectedReason.value, // Guardar motivo si necesario
    });

    // Cerrar el modal y reiniciar el motivo
    showBajaModal.value = false;
    selectedReason.value = "";
  } catch (err) {
    console.error(err);
    alert("Hubo un error al cambiar el estado del estudiante.");
  }
};

// Función para activar nuevamente al estudiante
const handleAlta = async () => {
  try {
    // Enviar el estado actualizado al backend
    const response = await fetch(
      `https://api.grupify.cat/api/user/${student.value.id}/status`,
      {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          status: 1, // 1 para activo
        }),
      }
    );

    if (!response.ok) {
      throw new Error("Error al actualizar el estado del estudiante.");
    }

    // Cambiar el estado localmente después de la respuesta
    student.value.status = 1;

    // Actualizar el estado en la tienda si es necesario
    studentsStore.updateStudent({
      ...student.value,
      status: 1,
    });

    // Reiniciar cualquier selección previa de motivo y ocultar el modal
    showBajaModal.value = false;
    selectedReason.value = "";
  } catch (err) {
    console.error(err);
    alert("Hubo un error al cambiar el estado del estudiante.");
  }
};

// Esta función se ha eliminado para evitar crear datos ficticios
// Ahora solo usaremos datos del backend

onMounted(async () => {
  try {
    isLoading.value = true;
    console.log("Iniciando carga del perfil de estudiante, ID:", studentId);

    // Verificar si tenemos estudiantes cargados
    if (!studentsStore.students.length) {
      console.log("Cargando lista de estudiantes...");
      await studentsStore.fetchStudents();
    }

    // Obtener datos del estudiante
    student.value = studentsStore.getStudentById(Number(studentId));

    if (!student.value) {
      console.error("Estudiante no encontrado con ID:", studentId);
      error.value = "Estudiant no trobat";
    } else {
      console.log("Estudiante encontrado:", student.value.name, student.value.last_name);

      try {
        // Cargar comentarios del estudiante
        await fetchComments(studentId);
      } catch (commentError) {
        console.error("Error al cargar comentarios:", commentError);
      }

      // NO cambiar el estado del desplegable, para mantenerlo oculto por defecto
      // isFormVisible se mantiene en false inicialmente

      // Esperar a que el DOM esté listo
      await nextTick();

      try {
        console.log(`Cargando respuestas de autoavaluación para el estudiante ${studentId}`);

        // Usar la ruta completamente pública para autoavaluaciones
        const url = `https://api.grupify.cat/api/public/forms/autoavaluacion/${studentId}`;

        console.log("Consultando URL:", url);

        const response = await fetch(url, {
          method: "GET",
          headers: {
            "Accept": "application/json",
            "Content-Type": "application/json"
          },
          cache: "no-cache"
        });

        if (!response.ok) {
          throw new Error(`Error al obtener respuestas: ${response.status} - ${response.statusText}`);
        }

        const data = await response.json();
        console.log("Datos de autoavaluación (ruta pública):", data);

        // Actualizar el estado basado en los datos recibidos
        hasAnsweredForm4.value = data.has_answered === true;

        // Almacenar las respuestas mapeadas para usarlas cuando el gráfico sea visible
        datosRespuestas.value = data.answers || [];
        console.log("Competencias mapeadas para el gráfico:", datosRespuestas.value);

        // Si el gráfico está visible, actualizarlo inmediatamente
        if (isFormVisible.value) {
          // Esperar a que el DOM esté listo
          await nextTick();

          // Actualizar el gráfico con las competencias mapeadas
          actualizarGrafico(datosRespuestas.value);
        }
      } catch (error) {
        console.error("Error al cargar datos del gráfico:", error);

        console.log("Intentando método alternativo...");
        try {
          // Intento alternativo con otra URL
          const altUrl = `https://api.grupify.cat/api/forms/4/users/${studentId}/answers`;
          console.log("Intentando con URL alternativa:", altUrl);

          const altResponse = await fetch(altUrl);
          if (altResponse.ok) {
            const altData = await altResponse.json();
            console.log("Datos obtenidos con URL alternativa:", altData);

            // Extraer respuestas
            const altAnswers = altData.answers || [];

            // Mapear respuestas
            const altMappedAnswers = competences.map(competence => {
              const answer = altAnswers.find(a => a.question_id === competence.id);
              const rating = answer ? (answer.rating || answer.answer || 0) : 0;
              return { ...competence, rating: Number(rating) };
            });

            hasAnsweredForm4.value = altAnswers.length > 0;
            actualizarGrafico(altMappedAnswers);
          } else {
            throw new Error("También falló la URL alternativa");
          }
        } catch (altError) {
          console.error("Error en método alternativo:", altError);
          // Última opción: mostrar gráfico vacío
          const respuestasVacias = competences.map(comp => ({ ...comp, rating: 0 }));
          datosRespuestas.value = respuestasVacias;

          // Solo actualizar el gráfico si está visible
          if (isFormVisible.value) {
            actualizarGrafico(respuestasVacias);
          }
        }
      }
    }
  } catch (err) {
    console.error("Error general en onMounted:", err);
    error.value = "Error al cargar els estudiants";
  } finally {
    isLoading.value = false;
  }
});
// Verificar si el estudiante ha respondido al formulario de autoavaluación (id 4)
const checkForm4Status = async studentId => {
  try {
    console.log(
      "Verificando si el estudiante", studentId, "ha respondido al formulario ID 4"
    );

    // Intentar usar el endpoint principal para obtener la lista de usuarios que han respondido
    const url = `https://api.grupify.cat/api/forms/4/users`;

    console.log("Intentando obtener lista de usuarios que han respondido:", url);

    const response = await fetch(url, {
      method: "GET",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
      },
      cache: "no-cache"
    });

    if (!response.ok) {
      console.error(`Error al verificar estado: ${response.status} - ${response.statusText}`);

      // Si falla, intentamos verificar directamente obteniendo las respuestas del estudiante
      return await checkDirectResponses(studentId);
    }

    const data = await response.json();
    console.log("Datos obtenidos del endpoint de usuarios:", data);

    // Extraer lista de usuarios que han respondido
    const usersList = Array.isArray(data) ? data : (data.users || data.data || []);

    // Verificar si el estudiante está en la lista
    const hasAnswered = usersList.some(user => {
      const userId = user.id || user.user_id || user.student_id;
      return Number(userId) === Number(studentId);
    });

    console.log(`Resultado de la verificación: El estudiante ${studentId} ${hasAnswered ? 'SÍ' : 'NO'} ha respondido`);

    // Actualizar el estado global
    hasAnsweredForm4.value = hasAnswered;

    return hasAnswered;
  } catch (error) {
    console.error("Error al verificar estado del formulario:", error);

    // Si hay un error general, intentamos el método directo como último recurso
    return await checkDirectResponses(studentId);
  }
};

// Método alternativo para verificar si hay respuestas directamente
const checkDirectResponses = async (studentId) => {
  try {
    console.log("Intentando verificación directa de respuestas para estudiante:", studentId);

    // Intentar obtener directamente las respuestas del estudiante
    const directUrl = `https://api.grupify.cat/api/forms/4/users/${studentId}/answers`;

    const directResponse = await fetch(directUrl, {
      method: "GET",
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json",
      },
      cache: "no-cache"
    });

    // Si la petición falla, asumimos que no hay respuestas
    if (!directResponse.ok) {
      console.log("No se pudieron obtener respuestas directamente");
      hasAnsweredForm4.value = false;
      return false;
    }

    const directData = await directResponse.json();
    console.log("Datos de verificación directa:", directData);

    // Extraer respuestas
    let answers = [];
    if (directData && directData.answers) {
      answers = directData.answers;
    } else if (Array.isArray(directData)) {
      answers = directData;
    }

    // Verificar si hay al menos una respuesta
    const hasResponses = answers && answers.length > 0;

    console.log(`Verificación directa: ${hasResponses ? 'SÍ' : 'NO'} tiene respuestas`);

    // Actualizar estado global
    hasAnsweredForm4.value = hasResponses;

    return hasResponses;
  } catch (error) {
    console.error("Error en verificación directa:", error);
    hasAnsweredForm4.value = false;
    return false;
  }
};

// Funciones para comentarios
const fetchComments = async studentId => {
  try {
    const response = await fetch(
      `https://api.grupify.cat/api/comments/students/${studentId}`,
      {
        method: "GET",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
      }
    );

    if (!response.ok) {
      throw new Error("Error al cargar los comentarios.");
    }

    const commentsData = await response.json();
    console.log("Comentarios cargados:", commentsData); // Depuración de la respuesta

    // Asigna los comentarios extraídos del objeto 'comments' en la respuesta
    comments.value = commentsData.comments.reverse() || []; // Asegúrate de que no esté vacío
  } catch (e) {
    console.error("Error al obtener comentarios:", e);
  }
};

// Crear un nuevo comentario
const addComment = async () => {
  if (!newComment.value.trim()) return;

  const newCommentData = {
    teacher_id: teacherId,
    student_id: studentId,
    content: newComment.value.trim(),
    created_at: new Date().toISOString(),
  };

  try {
    const response = await fetch(`https://api.grupify.cat/api/comments`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(newCommentData),
    });

    if (!response.ok) throw new Error("Error al añadir el comentario.");
    await fetchComments(studentId); // Fetch comments after adding a new one
    newComment.value = ""; // Clear the newComment input
  } catch (e) {
    console.error("Error al añadir comentario:", e);
  }
};

// Eliminar un comentario
const deleteComment = async commentId => {
  try {
    const response = await fetch(
      `https://api.grupify.cat/api/comments/${commentId}`,
      { method: "DELETE" }
    );

    if (!response.ok) throw new Error("Error al eliminar el comentario.");
    comments.value = comments.value.filter(comment => comment.id !== commentId);
  } catch (e) {
    console.error("Error al eliminar el comentario:", e);
  }
};

// Actualizar comentario
const updateComment = async commentId => {
  const commentToUpdate = comments.value.find(
    comment => comment.id === commentId
  );
  if (!commentToUpdate || !commentToUpdate.content.trim()) return;

  const updatedComment = { content: commentToUpdate.content.trim() };

  try {
    const response = await fetch(
      `https://api.grupify.cat/api/comments/${commentId}`,
      {
        method: "PUT",
        headers: {
          Accept: "application/json",
          "Content-Type": "application/json",
        },
        body: JSON.stringify(updatedComment),
      }
    );

    if (!response.ok) throw new Error("Error al actualizar comentario.");
    await fetchComments(studentId); // Fetch comments after updating one
    editingComment.value = null;
  } catch (e) {
    console.error("Error al actualizar comentario:", e);
  }
};

const cancelEdit = () => {
  editingComment.value = null;
};

// Observar cambios en isFormVisible para actualizar el gráfico cuando se hace visible
watch(isFormVisible, async (newValue) => {
  if (newValue && datosRespuestas.value) {
    console.log("El gráfico se ha hecho visible, actualizando con datos almacenados:", datosRespuestas.value);

    // Esperar a que el DOM se actualice con el gráfico visible
    await nextTick();

    // Dar un poco más de tiempo para asegurar que el DOM esté completamente listo
    setTimeout(() => {
      actualizarGrafico(datosRespuestas.value);
    }, 200);
  }
});
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <DashboardNavTeacher />

    <main class="max-w-4xl mx-auto p-6 space-y-6">
      <!-- Loading State -->
      <div v-if="isLoading"
        class="flex flex-col items-center justify-center min-h-[400px] bg-white rounded-2xl shadow-lg">
        <div class="animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"></div>
        <p class="mt-4 text-gray-600 font-medium text-lg">
          Cargant perfil del estudiant...
        </p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border-l-6 border-red-500 p-8 rounded-2xl shadow-md">
        <div class="flex items-center">
          <svg class="h-8 w-8 text-red-500 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <p class="text-red-700 font-semibold text-lg">{{ error }}</p>
        </div>
      </div>

      <!-- Student Profile -->
      <div v-else class="bg-white rounded-2xl shadow-lg p-8">
        <div class="flex items-center space-x-6 mb-8 border-b-2 pb-6">
          <!-- Avatar del estudiante -->
          <div
            class="w-24 h-24 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold text-4xl shadow-inner">
            <!-- Comprobar si student.image existe. Si no, mostrar las iniciales del nombre -->
            <img v-if="student.image" :src="student.image" alt="Imagen de perfil"
              class="w-full h-full object-cover rounded-full" />
            <span v-else>
              {{
                student.name
                  .split(" ")
                  .map(n => n[0])
                  .join("")
                  .toUpperCase()
              }}
            </span>
          </div>

          <div>
            <h1 class="text-4xl font-bold text-gray-900 tracking-tight">
              {{ student.name }} {{ student.last_name }}
            </h1>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
          <div class="space-y-4">
            <div class="bg-gray-50 p-5 rounded-xl hover:shadow-sm transition">
              <h3 class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider">
                Curs
              </h3>
              <p class="text-xl font-semibold text-gray-900">
                {{ student.course }}
              </p>
            </div>
            <div class="bg-gray-50 p-5 rounded-xl hover:shadow-sm transition">
              <h3 class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider">
                Divisió
              </h3>
              <p class="text-xl font-semibold text-gray-900">
                {{ student.division }}
              </p>
            </div>
          </div>
          <div class="space-y-4">
            <div class="bg-gray-50 p-5 rounded-xl hover:shadow-sm transition">
              <h3 class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider">
                Email
              </h3>
              <p class="text-xl font-semibold text-gray-900 break-words">
                {{ student.email }}
              </p>
            </div>
            <div class="bg-gray-50 p-5 rounded-xl hover:shadow-sm transition">
              <h3 class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider">
                Estat
              </h3>
              <div class="flex items-center space-x-4">
                <span :class="{
                  'bg-green-100 text-green-800 px-4 py-2 inline-flex text-base leading-6 font-semibold rounded-full':
                    student.status,
                  'bg-red-100 text-red-800 px-4 py-2 inline-flex text-base leading-6 font-semibold rounded-full':
                    !student.status,
                }">
                  {{ student.status ? "Actiu" : "Inactiu" }}
                </span>

                <button v-if="student.status" @click="showBajaModal = true"
                  class="group relative px-5 py-2 bg-red-500 text-white text-sm font-semibold rounded-full overflow-hidden transition-all duration-300 hover:bg-red-600 hover:shadow-lg">
                  <span class="relative z-10">Donar de Baixa</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
                </button>

                <button v-else @click="handleAlta"
                  class="group relative px-5 py-2 bg-green-500 text-white text-sm font-semibold rounded-lg overflow-hidden transition-all duration-300 hover:bg-green-600 hover:shadow-lg">
                  <span class="relative z-10">Donar d'Alta</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal para seleccionar motivo de baja -->
        <div v-if="showBajaModal" class="mt-8 bg-gray-50 p-6 rounded-xl border-2 border-red-100 shadow-lg">
          <h2 class="text-xl font-bold text-gray-800 mb-4">
            Selecciona un motiu per donar de baixa
          </h2>
          <div class="space-y-3">
            <label v-for="reason in reasons" :key="reason"
              class="flex items-center space-x-3 hover:bg-gray-100 p-2 rounded-lg transition">
              <input type="radio" :value="reason" v-model="selectedReason" class="text-primary focus:ring-primary" />
              <span class="text-gray-700 font-medium">{{ reason }}</span>
            </label>
          </div>
          <div class="mt-6 flex space-x-4">
            <button @click="handleBaja"
              class="group relative px-6 py-3 bg-primary text-white text-sm font-semibold rounded-lg overflow-hidden transition-all duration-300 hover:bg-primary-dark hover:shadow-lg">
              <span class="relative z-10">Confirmar Baixa</span>
              <span
                class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
            </button>
            <button @click="showBajaModal = false"
              class="group relative px-6 py-3 bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg overflow-hidden transition-all duration-300 hover:bg-gray-400 hover:shadow-lg">
              <span class="relative z-10">Cancel·lar</span>
              <span
                class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
            </button>
          </div>
        </div>
      </div>
      <!-- Gràfic d'autoavaluació (Formulari id 4) -->
      <div class="mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">
              Gràfic d'autoavaluació
            </h3>
            <div class="flex items-center space-x-3">
              <span class="text-sm text-gray-500">{{ isFormVisible ? 'Ocultar' : 'Mostrar' }} gràfic</span>
              <button @click="isFormVisible = !isFormVisible" :class="{
                'bg-[rgb(0,173,238)]': isFormVisible,
                'bg-gray-300': !isFormVisible,
              }" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none">
                <span :class="{
                  'translate-x-6': isFormVisible,
                  'translate-x-1': !isFormVisible,
                }" class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"></span>
              </button>
            </div>
          </div>
        </div>

        <!-- Contenido desplegable para resultados de autoevaluación -->
        <div v-if="isFormVisible" class="bg-white rounded-xl shadow-md p-6 mt-4 transition-all duration-300">
          <div class="text-center mb-4">
            <h4 class="text-lg font-medium text-gray-700 mb-2">Competències Avaluades</h4>
            <div v-if="hasAnsweredForm4"
              class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Formulari contestat
            </div>
            <div v-else
              class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Pendent de resposta
            </div>
          </div>

          <div class="flex justify-center">
            <div class="max-w-md w-full">
              <!-- Gráfico radial SVG -->
              <svg id="radial-graph" viewBox="-10 -10 231 231" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto">
                <!-- Cuadrícula de fondo -->
                <g stroke="#e5e7eb" fill="none">
                  <circle r="16" cx="100" cy="100" />
                  <!-- 1 -->
                  <circle r="32" cx="100" cy="100" />
                  <!-- 2 -->
                  <circle r="48" cx="100" cy="100" />
                  <!-- 3 -->
                  <circle r="64" cx="100" cy="100" />
                  <!-- 4 -->
                  <circle r="80" cx="100" cy="100" />
                  <!-- 5 -->
                </g>

                <!-- Líneas radiales -->
                <g stroke="#e5e7eb" stroke-width="0.5">
                  <line x1="100" y1="100" x2="100" y2="20" />
                  <line x1="100" y1="100" x2="156.6" y2="43.4" />
                  <line x1="100" y1="100" x2="180" y2="100" />
                  <line x1="100" y1="100" x2="156.6" y2="156.6" />
                  <line x1="100" y1="100" x2="100" y2="180" />
                  <line x1="100" y1="100" x2="43.4" y2="156.6" />
                  <line x1="100" y1="100" x2="20" y2="100" />
                  <line x1="100" y1="100" x2="43.4" y2="43.4" />
                </g>

                <!-- Etiquetas de valores -->
                <g fill="#6b7280" font-size="6">
                  <text x="105" y="37">4</text>
                  <text x="105" y="53">3</text>
                  <text x="105" y="69">2</text>
                  <text x="105" y="85">1</text>
                  <text x="105" y="21">5</text>
                </g>

                <!-- Etiquetas de competencias -->
                <g font-size="6" fill="#374151">
                  <text x="100" y="15" text-anchor="middle">Responsabilitat</text>
                  <text x="165" y="43.4" text-anchor="start">Treball equip</text>
                  <text x="185" y="104" text-anchor="start">Gestió temps</text>
                  <text x="165" y="165" text-anchor="middle">Comunicació</text>
                  <text x="100" y="190" text-anchor="middle">Adaptabilitat</text>
                  <text x="35" y="165" text-anchor="end">Lideratge</text>
                  <text x="15" y="104" text-anchor="end">Creativitat</text>
                  <text x="35" y="43.4" text-anchor="end">Proactivitat</text>
                </g>

                <!-- Área de datos y puntos -->
                <path id="area-data" fill="#00ADEE" fill-opacity="0.2" stroke="#00ADEE" stroke-width="1.5" />
                <g id="data-points"></g>
              </svg>
            </div>
          </div>

          <!-- Leyenda de competencias -->
          <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3">
            <div v-for="comp in competences" :key="comp.id" class="flex items-center space-x-2">
              <div class="w-3 h-3 rounded-full bg-[#00ADEE]"></div>
              <span class="text-sm text-gray-700">{{ comp.name }}</span>
            </div>
          </div>

          <!-- Mensaje informativo sobre el formulario -->
          <div class="mt-6 text-center text-sm text-gray-500 bg-gray-50 p-3 rounded-lg">
            <p>Aquest gràfic mostra l'autoavaluació de l'alumne segons el formulari que ha fet</p>
            <p v-if="!hasAnsweredForm4" class="text-red-500 font-medium mt-1">
              L'alumne encara no ha contestat aquest formulari.
            </p>
          </div>
        </div>
      </div>
      <!-- Comentarios -->
      <div class="bg-white rounded-2xl shadow-lg p-8 mt-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800">
          Historial de Comentaris
        </h2>

        <div class="mb-4">
          <textarea v-model="newComment" placeholder="Escriu un nou comentari"
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/50 transition"
            rows="4"></textarea>
          <button @click="addComment"
            class="group relative mt-3 w-full px-6 py-3 bg-primary text-white text-sm font-semibold rounded-lg overflow-hidden transition-all duration-300 hover:bg-primary-dark hover:shadow-lg">
            <span class="relative z-10">Desar comentari</span>
            <span
              class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
          </button>
        </div>

        <ul class="space-y-4">
          <li v-for="comment in comments" :key="comment.id"
            class="bg-gray-100 p-4 rounded-lg shadow-sm hover:bg-gray-200 transition">
            <div class="flex justify-between items-start">
              <p v-if="editingComment !== comment.id">{{ comment.content }}</p>
              <textarea v-else v-model="comment.content" rows="3"
                class="w-full p-2 border border-gray-300 rounded-lg"></textarea>
              <div class="ml-4 flex space-x-2">
                <button v-if="editingComment === comment.id" @click="updateComment(comment.id)"
                  class="group relative px-4 py-2 bg-green-600 text-white text-xs font-medium rounded-lg overflow-hidden transition-all duration-300 hover:bg-green-700 hover:shadow-lg">
                  <span class="relative z-10">Desar</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
                </button>
                <button v-if="editingComment === comment.id" @click="cancelEdit"
                  class="group relative px-4 py-2 bg-gray-300 text-gray-700 text-xs font-medium rounded-lg overflow-hidden transition-all duration-300 hover:bg-gray-400 hover:shadow-lg">
                  <span class="relative z-10">Cancel·lar</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
                </button>
                <button v-else @click="() => (editingComment = comment.id)"
                  class="group relative px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg overflow-hidden transition-all duration-300 hover:bg-blue-700 hover:shadow-lg">
                  <span class="relative z-10">Editar</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
                </button>
                <button @click="deleteComment(comment.id)"
                  class="group relative px-4 py-2 bg-red-600 text-white text-xs font-medium rounded-lg overflow-hidden transition-all duration-300 hover:bg-red-700 hover:shadow-lg">
                  <span class="relative z-10">Eliminar</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"></span>
                </button>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </main>
  </div>
</template>