// Este script debe ser ejecutado desde el navegador para probar los datos
// Copia este código en la consola del navegador mientras estás en la página del perfil del estudiante

async function testGetStudentResponses(studentId) {
  console.log('======== TEST DE RESPUESTAS DEL ESTUDIANTE ========');
  console.log(`Probando para el estudiante ID: ${studentId}`);

  // Intentar todas las rutas posibles
  const urls = [
    `https://api.basebrutt.com/api/autoavaluacion/estudiante/${studentId}`,
    `https://api.basebrutt.com/api/forms/4/users/${studentId}/answers`,
    `https://api.basebrutt.com/api/forms/4/student/${studentId}/responses`
  ];

  for (const url of urls) {
    try {
      console.log(`\nIntentando con URL: ${url}`);

      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      });

      console.log(`Código de estado: ${response.status}`);

      if (!response.ok) {
        console.log(`❌ Error: ${response.statusText}`);
        continue;
      }

      const data = await response.json();
      console.log(`✅ Datos obtenidos:`, data);

      // Verificar respuestas
      if (data.answers && data.answers.length > 0) {
        console.log(`Encontradas ${data.answers.length} respuestas:`);

        data.answers.forEach((answer, index) => {
          const questionId = answer.question_id;
          const rating = answer.rating || answer.answer || 0;
          console.log(`${index + 1}. Question ID: ${questionId}, Rating: ${rating}`);
        });
      } else {
        console.log('⚠️ No se encontraron respuestas');
      }
    } catch (error) {
      console.error(`❌ Error al consultar ${url}:`, error);
    }
  }

  console.log('\n======== FIN DEL TEST ========');
}

// Ejecutar el test para el estudiante actual
const studentId = new URL(window.location.href).pathname.split('/').pop();
testGetStudentResponses(studentId);
