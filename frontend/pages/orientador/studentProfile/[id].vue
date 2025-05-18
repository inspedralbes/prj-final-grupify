<script setup>
import { useRoute } from "vue-router";
import { onMounted, ref, nextTick } from "vue";
import OrientadorDashboardNavOrientador from "@/components/Orientador/DashboardNavOrientador.vue";
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
const isFormVisible = ref(false); // Inicialmente cerrado
const hasAnsweredForm5 = ref(false); // Estado para saber si ha respondido el formulario 5

// Definir todas las competencias, incluso las que están en el formulario equivocado
const competences = [
  { id: 34, name: "Responsabilitat" },
  { id: 35, name: "Treball en equip" },
  { id: 36, name: "Gestió del temps" },
  { id: 37, name: "Comunicació" },
  { id: 38, name: "Adaptabilitat" },
  { id: 39, name: "Lideratge" },
  { id: 40, name: "Creativitat" },
  { id: 41, name: "Proactivitat" },
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

// Función para obtener las respuestas del alumno
async function obtenerDatosAlumno(studentId) {
  try {
    console.log("Obteniendo datos del estudiante con ID:", studentId);
    
    // Array para almacenar todas las preguntas y respuestas
    let allQuestionsWithAnswers = [];
    
    // Obtener preguntas y respuestas del formulario 5 (contiene las competencias 34 y 35)
    try {
      const form5Response = await fetch(`http://localhost:8000/api/forms/5/questions`);
      
      if (form5Response.ok) {
        const form5Data = await form5Response.json();
        allQuestionsWithAnswers = [...allQuestionsWithAnswers, ...(form5Data.questions || [])];
        console.log("Datos obtenidos del formulario 5");
      }
    } catch (form5Error) {
      console.error("Error al obtener datos del formulario 5:", form5Error);
    }
    
    // Obtener preguntas y respuestas del formulario 4 (contiene las competencias 36-41)
    try {
      const form4Response = await fetch(`http://localhost:8000/api/forms/4/questions`);
      
      if (form4Response.ok) {
        const form4Data = await form4Response.json();
        allQuestionsWithAnswers = [...allQuestionsWithAnswers, ...(form4Data.questions || [])];
        console.log("Datos obtenidos del formulario 4");
      }
    } catch (form4Error) {
      console.error("Error al obtener datos del formulario 4:", form4Error);
    }
    
    console.log(`Se obtuvieron ${allQuestionsWithAnswers.length} preguntas de los formularios`);
    
    // Mapear las respuestas para el estudiante específico y las 8 competencias
    const studentResponses = competences.map(competence => {
      // Buscar la pregunta correspondiente a esta competencia en todas las preguntas obtenidas
      const question = allQuestionsWithAnswers.find(q => q.id === competence.id);
      
      if (!question) {
        console.log(`No se encontró la pregunta para la competencia ${competence.name} (ID ${competence.id})`);
        return {
          ...competence,
          rating: 0 // Si no hay pregunta, asignar 0
        };
      }
      
      // Buscar la respuesta del estudiante específico
      const studentAnswer = question.answers?.find(a => Number(a.user_id) === Number(studentId));
      
      if (!studentAnswer) {
        console.log(`El estudiante ${studentId} no ha respondido a la pregunta ${competence.id} (${competence.name})`);
        return {
          ...competence,
          rating: 0 // Si no hay respuesta, asignar 0
        };
      }
      
      // Si hay respuesta, devolver el rating
      console.log(`Respuesta encontrada para ${competence.name}: ${studentAnswer.rating}`);
      return {
        ...competence,
        rating: parseInt(studentAnswer.rating)
      };
    });
    
    console.log("Respuestas procesadas del estudiante:", studentResponses);
    return studentResponses;
  } catch (error) {
    console.error("Error general al obtener datos del alumno:", error);
    return competences.map(comp => ({...comp, rating: 0})); // Devolver competencias con rating 0 en caso de error
  }
}
// Función para actualizar el gráfico
// Función para cargar un estudiante directamente por su ID
const fetchStudentById = async (id) => {
  try {
    console.log("Obteniendo estudiante directamente por ID:", id);
    const response = await fetch(`http://localhost:8000/api/users/${id}`);
    
    if (!response.ok) {
      console.error(`Error al obtener estudiante: ${response.status}`);
      return null;
    }
    
    const data = await response.json();
    console.log("Datos del estudiante recibidos:", data);
    
    // Asegurarse de que el estudiante tiene todos los campos necesarios
    return {
      id: data.id,
      name: data.name,
      last_name: data.last_name,
      email: data.email,
      image: data.image,
      course: data.course || "",
      division: data.division || "",
      status: data.status !== undefined ? data.status : 1
    };
  } catch (error) {
    console.error("Error en fetchStudentById:", error);
    return null;
  }
};

// Variable para almacenar las respuestas para el gráfico
const datosGrafico = ref(null);

function actualizarGrafico(respuestas) {
  console.log("Actualizando gráfico con respuestas:", respuestas);
  
  // Almacenar los datos para usarlos cuando el SVG esté disponible
  datosGrafico.value = respuestas;
  
  // Intentar actualizar el gráfico varias veces si es necesario
  const intentarActualizar = (intentos = 0) => {
    // Buscar el SVG en el DOM
    const svg = document.getElementById("radial-graph");
    if (!svg) {
      console.error("No se encontró el elemento SVG principal (intento " + (intentos + 1) + ")");
      if (intentos < 3) {
        // Intentar nuevamente después de un pequeño retraso
        setTimeout(() => intentarActualizar(intentos + 1), 100);
      }
      return;
    }
    
    console.log("SVG encontrado, actualizando elementos...");
    
    const dataPointsElement = svg.querySelector("#data-points");
    const areaDataElement = svg.querySelector("#area-data");
    
    if (!dataPointsElement || !areaDataElement) {
      console.error("No se encontraron los elementos necesarios del SVG (intento " + (intentos + 1) + ")");
      if (intentos < 3) {
        setTimeout(() => intentarActualizar(intentos + 1), 100);
      }
      return;
    }
    
    console.log("Elementos del SVG encontrados, dibujando gráfico...");

  dataPointsElement.innerHTML = "";
  let areaPath = "";

  // Ajustar el radio base y el factor de escala
  const baseRadius = 80; // Radio máximo del gráfico
  const centerPoint = 100; // Centro del SVG
  
  // Mostrar un mensaje si no hay datos
  if (!respuestas || respuestas.length === 0 || respuestas.every(r => r.rating === 0)) {
    console.warn("No hay datos de competencias para mostrar en el gráfico");
    
    // Crear un elemento de texto para indicar que no hay datos
    const noDataText = document.createElementNS("http://www.w3.org/2000/svg", "text");
    noDataText.setAttribute("x", centerPoint);
    noDataText.setAttribute("y", centerPoint);
    noDataText.setAttribute("text-anchor", "middle");
    noDataText.setAttribute("fill", "#888");
    noDataText.setAttribute("font-size", "12");
    noDataText.textContent = "No hi ha dades disponibles";
    
    dataPointsElement.appendChild(noDataText);
    areaDataElement.setAttribute("d", ""); // Limpiar el área
    return;
  }

  // Coordenadas base para cada dirección (normalizada)
  const coordenadas = [
    { x: 0, y: -1 }, // Responsabilitat (arriba)
    { x: 0.707, y: -0.707 }, // Treball equip (arriba-derecha)
    { x: 1, y: 0 }, // Gestió temps (derecha)
    { x: 0.707, y: 0.707 }, // Comunicació (abajo-derecha)
    { x: 0, y: 1 }, // Adaptabilitat (abajo)
    { x: -0.707, y: 0.707 }, // Lideratge (abajo-izquierda)
    { x: -1, y: 0 }, // Creativitat (izquierda)
    { x: -0.707, y: -0.707 }, // Proactivitat (arriba-izquierda)
  ];

  const competenciasOrdenadas = [
    "Responsabilitat",
    "Treball en equip",
    "Gestió del temps",
    "Comunicació",
    "Adaptabilitat",
    "Lideratge",
    "Creativitat",
    "Proactivitat",
  ];

  const respuestasOrdenadas = competenciasOrdenadas.map(nombreCompetencia => {
    return (
      respuestas.find(r => r.name === nombreCompetencia) || {
        name: nombreCompetencia,
        rating: 0,
      }
    );
  });

  respuestasOrdenadas.forEach((respuesta, index) => {
    // Comprobar si el índice está dentro del rango de coordenadas
    if (index >= coordenadas.length) {
      console.warn(`Índice ${index} fuera de rango para las coordenadas`);
      return;
    }
    
    const rating = respuesta.rating || 0;
    // Si el rating es 0, significa que no hay datos para esta competencia, así que no dibujamos el punto
    if (rating === 0) {
      console.log(`La competencia ${respuesta.name} no tiene datos (rating 0)`);
      return;
    }
    
    // Asegurar que el rating esté entre 1 y 5
    const ratingNormalizado = Math.max(1, Math.min(5, rating));
    console.log(`Rating normalizado para ${respuesta.name}: ${ratingNormalizado}`);
    
    // Calcular el radio exacto basado en una escala de 1-5
    const radio = (ratingNormalizado / 5) * baseRadius;

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
  }
  
    console.log("Gráfico actualizado correctamente");
  };
  
  // Iniciar el proceso de actualización
  intentarActualizar();
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
      `http://localhost:8000/api/user/${student.value.id}/status`,
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
      `http://localhost:8000/api/user/${student.value.id}/status`,
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

// Función para actualizar el gráfico cuando el formulario se hace visible
const mostrarGrafico = () => {
  isFormVisible.value = !isFormVisible.value;
  
  // Si el formulario está visible y tenemos datos, actualizamos el gráfico
  if (isFormVisible.value && datosGrafico.value) {
    // Esperamos a que el DOM se actualice antes de intentar manipular el SVG
    nextTick(() => {
      // Añadimos varios intentos con retrasos incrementales para asegurar que el SVG esté listo
      const maxIntentos = 5;
      const intentarActualizar = (intento = 1) => {
        setTimeout(() => {
          console.log(`Intento ${intento} de actualizar gráfico`);
          actualizarGrafico(datosGrafico.value);
          
          // Verificar si el gráfico se dibujó correctamente
          const svg = document.getElementById("radial-graph");
          const dataPoints = svg?.querySelector("#data-points");
          
          // Si no hay puntos de datos y aún hay intentos, intentar de nuevo
          if ((!dataPoints || dataPoints.children.length === 0) && intento < maxIntentos) {
            console.log(`Reintentar actualización del gráfico (intento ${intento + 1})`);
            intentarActualizar(intento + 1);
          }
        }, 200 * intento); // Retraso incremental: 200ms, 400ms, 600ms, 800ms, 1000ms
      };
      
      intentarActualizar();
    });
  }
};

// Función que se llama cuando el DOM del formulario se ha montado
const onFormMounted = () => {
  if (isFormVisible.value && datosGrafico.value) {
    console.log("El formulario está montado y visible");
    
    // Implementar múltiples intentos para asegurar que el gráfico se renderice
    const maxIntentos = 5;
    
    const intentarActualizar = (intento = 1) => {
      setTimeout(() => {
        console.log(`Intento ${intento} de actualizar gráfico desde onFormMounted`);
        actualizarGrafico(datosGrafico.value);
        
        // Verificar si el gráfico se dibujó correctamente
        const svg = document.getElementById("radial-graph");
        const dataPoints = svg?.querySelector("#data-points");
        
        // Si no hay puntos de datos y aún hay intentos, intentar de nuevo
        if ((!dataPoints || dataPoints.children.length === 0) && intento < maxIntentos) {
          console.log(`Reintentar actualización del gráfico (intento ${intento + 1})`);
          intentarActualizar(intento + 1);
        }
      }, 100 * intento); // Retraso incremental: 100ms, 200ms, 300ms, etc.
    };
    
    intentarActualizar();
  }
};

onMounted(async () => {
  console.log("Componente principal montado");
  try {
    isLoading.value = true;
    error.value = null;
    
    // Intento 1: Ver si el estudiante ya está en la tienda
    if (studentsStore.students.length > 0) {
      student.value = studentsStore.getStudentById(Number(studentId));
      console.log("Búsqueda en tienda existente:", student.value ? "encontrado" : "no encontrado");
    }
    
    // Intento 2: Si no lo encontramos, intentar cargarlo de la tienda después de refrescar
    if (!student.value) {
      console.log("Cargando estudiantes de la tienda...");
      await studentsStore.fetchStudents(true);
      student.value = studentsStore.getStudentById(Number(studentId));
      console.log("Búsqueda después de refrescar tienda:", student.value ? "encontrado" : "no encontrado");
    }
    
    // Intento 3: Como último recurso, obtener directamente del backend
    if (!student.value) {
      console.log("Obteniendo estudiante directamente de la API...");
      student.value = await fetchStudentById(studentId);
      
      // Si se obtuvo correctamente, actualizar la tienda
      if (student.value) {
        console.log("Estudiante obtenido directamente, actualizando tienda");
        studentsStore.updateStudent(student.value);
      } else {
        error.value = "Estudiant no trobat";
        isLoading.value = false;
        return;
      }
    }
    
    console.log("Estudiante cargado correctamente:", student.value);
    
    // Proceder con la carga de datos adicionales
    await Promise.all([
      fetchComments(studentId),
      checkForm5Status(studentId)
    ]).catch(err => {
      console.error("Error al cargar comentarios o estado del formulario:", err);
      // No detener el proceso por datos adicionales
    });
    
    // Obtener las respuestas de las competencias
    try {
      const respuestas = await obtenerDatosAlumno(studentId);
      console.log("Respuestas recibidas en onMounted:", respuestas);
      
      if (respuestas && respuestas.length > 0) {
        // Guardar las respuestas para el gráfico
        datosGrafico.value = respuestas;
        console.log("Datos del gráfico guardados, esperando a que el usuario active el gráfico");
        
        // Opcional: mostrar automáticamente el gráfico
        // isFormVisible.value = true;
        // nextTick(() => {
        //   setTimeout(() => actualizarGrafico(respuestas), 100);
        // });
      } else {
        console.warn("No se encontraron respuestas para actualizar el gráfico.");
      }
    } catch (compErr) {
      console.error("Error al obtener datos de competencias:", compErr);
      // No detener el proceso por error en competencias
    }
  } catch (err) {
    console.error("Error general en onMounted:", err);
    error.value = "Error al cargar els estudiants";
  } finally {
    isLoading.value = false;
  }
});
const checkForm5Status = async studentId => {
  try {
    console.log(
      "Verificando respuesta del formulario 5 para el estudiante:",
      studentId
    );

    // Llamada a la ruta correcta para obtener los usuarios que han respondido el formulario 5
    const response = await fetch(`http://localhost:8000/api/forms/5/users`, {
      method: "GET",
      headers: {
        "Content-Type": "application/json",
      },
    });

    if (!response.ok) {
      throw new Error(
        `Error al comprobar la respuesta del formulario (Código ${response.status})`
      );
    }

    const data = await response.json();
    console.log("Respuesta del servidor para el formulario 5:", data); // Imprime la respuesta

    // Asegúrate de que estamos comparando los IDs correctamente (convirtiéndolos a números si es necesario)
    const hasAnswered = data.some(
      user => Number(user.id) === Number(studentId)
    ); // Aseguramos que ambas sean del mismo tipo

    console.log("¿El estudiante ha respondido?", hasAnswered); // Depuración adicional

    // Verifica si el estudiante ha respondido y actualiza el estado
    hasAnsweredForm5.value = hasAnswered;
  } catch (error) {
    console.error("Error en checkForm5Status:", error);
    hasAnsweredForm5.value = false; // En caso de error, asumimos que no ha respondido
  }
};

// Funciones para comentarios
const fetchComments = async studentId => {
  try {
    const response = await fetch(
      `http://localhost:8000/api/comments/students/${studentId}`,
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
    const response = await fetch(`http://localhost:8000/api/comments`, {
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
      ` http://localhost:8000/api/comments/${commentId}`,
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
      `http://localhost:8000/api/comments/${commentId}`,
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
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <OrientadorDashboardNavOrientador />

    <main class="max-w-4xl mx-auto p-6 space-y-6">
      <!-- Loading State -->
      <div
        v-if="isLoading"
        class="flex flex-col items-center justify-center min-h-[400px] bg-white rounded-2xl shadow-lg"
      >
        <div
          class="animate-spin rounded-full h-16 w-16 border-4 border-primary border-t-transparent"
        ></div>
        <p class="mt-4 text-gray-600 font-medium text-lg">
          Cargant perfil del estudiant...
        </p>
      </div>

      <!-- Error State -->
      <div
        v-else-if="error"
        class="bg-red-50 border-l-6 border-red-500 p-8 rounded-2xl shadow-md"
      >
        <div class="flex items-center">
          <svg
            class="h-8 w-8 text-red-500 mr-4"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <p class="text-red-700 font-semibold text-lg">{{ error }}</p>
        </div>
      </div>

      <!-- Student Profile -->
      <div v-else class="bg-white rounded-2xl shadow-lg p-8">
        <div class="flex items-center space-x-6 mb-8 border-b-2 pb-6">
          <!-- Avatar del estudiante -->
          <div
            class="w-24 h-24 bg-primary/10 rounded-full flex items-center justify-center text-primary font-bold text-4xl shadow-inner"
          >
            <!-- Comprobar si student.image existe. Si no, mostrar las iniciales del nombre -->
            <img
              v-if="student.image"
              :src="student.image"
              alt="Imagen de perfil"
              class="w-full h-full object-cover rounded-full"
            />
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
              <h3
                class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider"
              >
                Curs
              </h3>
              <p class="text-xl font-semibold text-gray-900">
                {{ student.course }}
              </p>
            </div>
            <div class="bg-gray-50 p-5 rounded-xl hover:shadow-sm transition">
              <h3
                class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider"
              >
                Divisió
              </h3>
              <p class="text-xl font-semibold text-gray-900">
                {{ student.division }}
              </p>
            </div>
          </div>
          <div class="space-y-4">
            <div class="bg-gray-50 p-5 rounded-xl hover:shadow-sm transition">
              <h3
                class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider"
              >
                Email
              </h3>
              <p class="text-xl font-semibold text-gray-900 break-words">
                {{ student.email }}
              </p>
            </div>
            <div class="bg-gray-50 p-5 rounded-xl hover:shadow-sm transition">
              <h3
                class="text-xs font-medium text-gray-500 mb-1 uppercase tracking-wider"
              >
                Estat
              </h3>
              <div class="flex items-center space-x-4">
                <span
                  :class="{
                    'bg-green-100 text-green-800 px-4 py-2 inline-flex text-base leading-6 font-semibold rounded-full':
                      student.status,
                    'bg-red-100 text-red-800 px-4 py-2 inline-flex text-base leading-6 font-semibold rounded-full':
                      !student.status,
                  }"
                >
                  {{ student.status ? "Actiu" : "Inactiu" }}
                </span>

                <button
                  v-if="student.status"
                  @click="showBajaModal = true"
                  class="group relative px-5 py-2 bg-red-500 text-white text-sm font-semibold rounded-full overflow-hidden transition-all duration-300 hover:bg-red-600 hover:shadow-lg"
                >
                  <span class="relative z-10">Donar de Baixa</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"
                  ></span>
                </button>

                <button
                  v-else
                  @click="handleAlta"
                  class="group relative px-5 py-2 bg-green-500 text-white text-sm font-semibold rounded-lg overflow-hidden transition-all duration-300 hover:bg-green-600 hover:shadow-lg"
                >
                  <span class="relative z-10">Donar d'Alta</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"
                  ></span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal para seleccionar motivo de baja -->
        <div
          v-if="showBajaModal"
          class="mt-8 bg-gray-50 p-6 rounded-xl border-2 border-red-100 shadow-lg"
        >
          <h2 class="text-xl font-bold text-gray-800 mb-4">
            Selecciona un motiu per donar de baixa
          </h2>
          <div class="space-y-3">
            <label
              v-for="reason in reasons"
              :key="reason"
              class="flex items-center space-x-3 hover:bg-gray-100 p-2 rounded-lg transition"
            >
              <input
                type="radio"
                :value="reason"
                v-model="selectedReason"
                class="text-primary focus:ring-primary"
              />
              <span class="text-gray-700 font-medium">{{ reason }}</span>
            </label>
          </div>
          <div class="mt-6 flex space-x-4">
            <button
              @click="handleBaja"
              class="group relative px-6 py-3 bg-primary text-white text-sm font-semibold rounded-lg overflow-hidden transition-all duration-300 hover:bg-primary-dark hover:shadow-lg"
            >
              <span class="relative z-10">Confirmar Baixa</span>
              <span
                class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"
              ></span>
            </button>
            <button
              @click="showBajaModal = false"
              class="group relative px-6 py-3 bg-gray-300 text-gray-700 text-sm font-semibold rounded-lg overflow-hidden transition-all duration-300 hover:bg-gray-400 hover:shadow-lg"
            >
              <span class="relative z-10">Cancel·lar</span>
              <span
                class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"
              ></span>
            </button>
          </div>
        </div>
      </div>
      <!-- Gràfic d'autoavaluació -->
      <div class="mb-8">
        <div class="bg-white rounded-xl shadow-sm p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">
              Gràfic d'autoavaluació
            </h3>
            <div class="flex items-center">
              <span class="text-sm text-gray-500 mr-2">
                {{ isFormVisible ? '' : '' }}
              </span>
              <button
                @click="mostrarGrafico"
                :class="{
                  'bg-[rgb(0,173,238)]': isFormVisible,
                  'bg-gray-200': !isFormVisible,
                }"
                class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none"
              >
                <span
                  :class="{
                    'translate-x-6': isFormVisible,
                    'translate-x-1': !isFormVisible,
                  }"
                  class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"
                ></span>
              </button>
            </div>
          </div>
        </div>

        <!-- Contenido desplegable para resultados de autoevaluación -->
        <div
          class="bg-white rounded-xl shadow-sm p-6 mt-4"
          v-if="isFormVisible"
          v-show="isFormVisible"
          key="grafico-container"
          @vue:mounted="onFormMounted"
        >
          <!-- Actualiza esta parte en tu template -->
          <svg
            id="radial-graph"
            viewBox="-10 -10 231 231"
            xmlns="http://www.w3.org/2000/svg"
          >
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

            <path
              id="area-data"
              fill="#00ADEE"
              fill-opacity="0.2"
              stroke="#00ADEE"
              stroke-width="1"
            />
            <g id="data-points"></g>
          </svg>

          <!-- Mostrar si el formulario ha sido contestado o no -->
          <div class="mt-4 text-center text-gray-700">
            <span v-if="hasAnsweredForm5" class="text-green-600 font-semibold"
              >Contestat ✅</span
            >
            <span v-else class="text-red-600 font-semibold"
              >No contestat ❌</span
            >
          </div>
          
          <!-- Botón temporal para depuración (puedes quitar esto después) -->
          <div class="mt-4 text-center">
            
            <p class="text-xs text-gray-500 mt-1">
              Estat del gràfic: {{ datosGrafico ? `Dades disponibles (${datosGrafico.length} competències)` : 'Sense dades' }}
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
          <textarea
            v-model="newComment"
            placeholder="Escriu un nou comentari"
            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/50 transition"
            rows="4"
          ></textarea>
          <button
            @click="addComment"
            class="group relative mt-3 w-full px-6 py-3 bg-primary text-white text-sm font-semibold rounded-lg overflow-hidden transition-all duration-300 hover:bg-primary-dark hover:shadow-lg"
          >
            <span class="relative z-10">Desar comentari</span>
            <span
              class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"
            ></span>
          </button>
        </div>

        <ul class="space-y-4">
          <li
            v-for="comment in comments"
            :key="comment.id"
            class="bg-gray-100 p-4 rounded-lg shadow-sm hover:bg-gray-200 transition"
          >
            <div class="flex justify-between items-start">
              <p v-if="editingComment !== comment.id">{{ comment.content }}</p>
              <textarea
                v-else
                v-model="comment.content"
                rows="3"
                class="w-full p-2 border border-gray-300 rounded-lg"
              ></textarea>
              <div class="ml-4 flex space-x-2">
                <button
                  v-if="editingComment === comment.id"
                  @click="updateComment(comment.id)"
                  class="group relative px-4 py-2 bg-green-600 text-white text-xs font-medium rounded-lg overflow-hidden transition-all duration-300 hover:bg-green-700 hover:shadow-lg"
                >
                  <span class="relative z-10">Desar</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"
                  ></span>
                </button>
                <button
                  v-if="editingComment === comment.id"
                  @click="cancelEdit"
                  class="group relative px-4 py-2 bg-gray-300 text-gray-700 text-xs font-medium rounded-lg overflow-hidden transition-all duration-300 hover:bg-gray-400 hover:shadow-lg"
                >
                  <span class="relative z-10">Cancel·lar</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"
                  ></span>
                </button>
                <button
                  v-else
                  @click="() => (editingComment = comment.id)"
                  class="group relative px-4 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg overflow-hidden transition-all duration-300 hover:bg-blue-700 hover:shadow-lg"
                >
                  <span class="relative z-10">Editar</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"
                  ></span>
                </button>
                <button
                  @click="deleteComment(comment.id)"
                  class="group relative px-4 py-2 bg-red-600 text-white text-xs font-medium rounded-lg overflow-hidden transition-all duration-300 hover:bg-red-700 hover:shadow-lg"
                >
                  <span class="relative z-10">Eliminar</span>
                  <span
                    class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity duration-300"
                  ></span>
                </button>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </main>
  </div>
</template>
