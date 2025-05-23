import { Configuration, OpenAIApi } from "openai";
import { useRuntimeConfig } from '#app';

// Configure OpenAI
// Obtiene la API Key desde las variables de entorno del runtime config
const config = useRuntimeConfig();
const OPENAI_API_KEY = config.public.openaiApiKey || '';
const configuration = new Configuration({
  apiKey: OPENAI_API_KEY,
});
const openai = new OpenAIApi(configuration);

// Constantes para el mecanismo de reintentos
const MAX_RETRIES = 3;
const RETRY_DELAY = 2000; // 2 segundos entre reintentos
const REQUEST_TIMEOUT = 40000; // 40 segundos de timeout

/**
 * Función auxiliar para pausar la ejecución
 * @param {number} ms - Milisegundos de retraso
 * @returns {Promise} - Se resuelve después del retraso especificado
 */
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

/**
 * Analiza JSON de forma segura desde la respuesta de texto, manejando varios formatos
 * @param {string} text - Texto que contiene JSON
 * @returns {Object} - Objeto JSON analizado
 */
const parseJSONSafely = text => {
  try {
    // Primero intenta analizar directamente en caso de que la respuesta ya sea JSON limpio
    try {
      return JSON.parse(text);
    } catch (e) {
      // Si el análisis directo falla, intenta extraer un objeto JSON del texto
      const jsonMatch = text.match(/\{[\s\S]*\}/);
      if (!jsonMatch) {
        throw new Error("No s'ha trobat cap objecte JSON en la resposta");
      }
      return JSON.parse(jsonMatch[0]);
    }
  } catch (error) {
    console.error("Error al analizar JSON:", error, "Texto:", text);
    throw new Error(`Error al analitzar la resposta JSON: ${error.message}`);
  }
};

/**
 * Valida el formato de una pregunta
 * @param {Object} question - Objeto de pregunta a validar
 * @param {number} index - Índice de la pregunta para informes de error
 * @returns {Object} - Objeto de pregunta validado y limpiado
 */
const validateQuestion = (question, index) => {
  // Verificar tipo de pregunta
  if (
    !question.type ||
    !["text", "multiple", "checkbox", "number"].includes(question.type)
  ) {
    throw new Error(`Tipus de pregunta invàlid en la pregunta ${index + 1}`);
  }

  // Verificar título de la pregunta
  if (!question.title?.trim()) {
    throw new Error(`Títul invàlid en la pregunta ${index + 1}`);
  }

  // Para preguntas de opción múltiple, validar opciones
  if (["multiple", "checkbox"].includes(question.type)) {
    if (!Array.isArray(question.options) || question.options.length < 2) {
      throw new Error(
        `La pregunta ${index + 1} de tipus selecció necessita almenys dues opcions`
      );
    }

    // Normalizar formato de opciones
    question.options = question.options.map((option, optionIndex) => ({
      text: option.text || `Opción ${optionIndex + 1}`,
      value: typeof option.value === "number" ? option.value : optionIndex,
    }));
  }

  return question;
};

/**
 * Valida la respuesta del formulario completo
 * @param {Object} response - Respuesta del formulario para validar
 * @returns {Object} - Respuesta del formulario validada
 */
const validateResponse = response => {
  // Verificar título del formulario
  if (!response?.title?.trim()) {
    throw new Error("El títul del formulari és obligatori");
  }

  // Verificar descripción del formulario
  if (!response?.description?.trim()) {
    throw new Error("La descripció del formulari és obligatòria");
  }

  // Verificar array de preguntas
  if (!Array.isArray(response.questions) || response.questions.length === 0) {
    throw new Error("El formulari ha de tenir almenys una pregunta");
  }

  // Validar cada pregunta
  response.questions = response.questions.map((q, i) => validateQuestion(q, i));
  
  return response;
};

/**
 * Genera preguntas de formulario usando OpenAI
 * @param {string} prompt - Descripción del formulario deseado
 * @param {number} retryCount - Contador de reintentos (uso interno)
 * @returns {Promise<Object>} - Formulario generado con preguntas
 */
export async function generateFormQuestions(prompt, retryCount = 0) {
  // Validar entrada
  if (!prompt?.trim()) {
    throw new Error(
      "Si us plau, proporciona una descripció del formulari que necessites."
    );
  }

  // Intentar importar plantillas de respaldo primero en caso de que la API falle
  let fallbackTemplates;
  try {
    const { selectFallbackTemplate } = await import("@/components/utils/formFallbacks");
    const template = selectFallbackTemplate(prompt);
    fallbackTemplates = template;
  } catch (fallbackError) {
    console.log("No se pudieron cargar las plantillas de respaldo:", fallbackError);
  }

  try {
    // Instrucciones del sistema detalladas
    const systemPrompt = `Actúa como un experto en diseño de formularios educativos para profesores. Genera un formulario completo basado en la descripción proporcionada.

IMPORTANTE: Debes responder ÚNICAMENTE con un objeto JSON válido que siga esta estructura exacta:

{
  "title": "Título del formulario",
  "description": "Descripción detallada del propósito del formulario",
  "questions": [
    {
      "type": "multiple",
      "title": "¿Pregunta de opción múltiple?",
      "options": [
        {"text": "Opción 1", "value": 0},
        {"text": "Opción 2", "value": 1}
      ]
    },
    {
      "type": "text",
      "title": "Pregunta que requiere respuesta de texto"
    },
    {
      "type": "checkbox",
      "title": "Pregunta de selección múltiple",
      "options": [
        {"text": "Opción A", "value": 0},
        {"text": "Opción B", "value": 1}
      ]
    },
    {
      "type": "number",
      "title": "Pregunta que requiere respuesta numérica"
    }
  ]
}

Los tipos de pregunta permitidos son únicamente: "text", "multiple", "checkbox", "number".
Asegura que cada pregunta tenga un título claro y conciso.
Las preguntas de tipo "multiple" y "checkbox" DEBEN tener al menos 2 opciones.
Utiliza solo la estructura JSON que te he proporcionado, sin añadir campos adicionales.
El JSON debe ser válido y estar correctamente formateado.
Genera un total de 4 a 8 preguntas, dependiendo de la complejidad del formulario requerido.`;

    // Mensaje del usuario con el prompt
    const userMessage = `Necesito un formulario sobre: ${prompt}
- Prioriza preguntas relevantes para profesores
- Adapta el nivel educativo según el contexto
- Usa lenguaje y terminología apropiados para educación
- Si se trata de un test de comprensión lectora, incluye un breve texto y preguntas sobre ese texto
- Genera formularios más sencillos con instrucciones claras`;

    // Crear un controlador de tiempo límite
    const timeoutController = new AbortController();
    const timeoutId = setTimeout(() => timeoutController.abort(), REQUEST_TIMEOUT);

    // Llamada a la API de OpenAI
    const response = await openai.createChatCompletion({
      model: "gpt-3.5-turbo",
      messages: [
        { role: "system", content: systemPrompt },
        { role: "user", content: userMessage }
      ],
      temperature: 0.7,
      max_tokens: 2048,
    }, { signal: timeoutController.signal });

    // Limpiar el timeout
    clearTimeout(timeoutId);

    // Verificar si la respuesta es válida
    if (!response?.data?.choices?.[0]?.message?.content) {
      throw new Error("No s'ha rebut resposta del model d'IA");
    }

    // Analizar y validar respuesta
    const responseText = response.data.choices[0].message.content;
    const parsedResponse = parseJSONSafely(responseText);
    return validateResponse(parsedResponse);
  } catch (error) {
    console.error("Error en generateFormQuestions:", error);
    console.log("Detalles del prompt:", prompt);
    console.log("Intento número:", retryCount + 1);

    // Si hay un error de red o API, proporciona un mensaje específico
    if (error.message.includes("network") || error.message.includes("API") || 
        error.message.includes("timeout") || error.name === "AbortError") {
      console.error("Problema de conexión con la API de OpenAI:", error.message);
      
      // Si tenemos un template de respaldo disponible, lo devolvemos en lugar de fallar
      if (fallbackTemplates) {
        console.log("Usando plantilla de respaldo debido a error de API");
        return fallbackTemplates;
      }
    }

    // Si no alcanzamos el máximo de reintentos, intentamos de nuevo
    if (retryCount < MAX_RETRIES) {
      console.log(`Reintentant generar formulari (intent ${retryCount + 1}/${MAX_RETRIES})...`);
      await sleep(RETRY_DELAY * (retryCount + 1)); // Aumentamos el tiempo entre intentos progresivamente
      return generateFormQuestions(prompt, retryCount + 1);
    }

    // Si todos los reintentos fallaron, intentamos usar la plantilla de respaldo
    if (fallbackTemplates) {
      console.log("Usando plantilla de respaldo después de agotar reintentos");
      return fallbackTemplates;
    }

    // Si no hay plantilla de respaldo, lanzamos un error amigable para el usuario
    let errorMsg = "No s'ha pogut generar el formulari. ";
    
    // Personaliza el mensaje de error según el tipo de formulario
    if (prompt.toLowerCase().includes("comprensió lectora") || prompt.toLowerCase().includes("test")) {
      errorMsg += "Intenta amb una descripció més detallada, com per exemple: 'Formulari de comprensió lectora sobre una història curta per a alumnes de 2n ESO'.";
    } else {
      errorMsg += "Si us plau, intenta amb una descripció més clara o específica.";
    }
    
    throw new Error(errorMsg);
  }
}

/**
 * Genera una sola pregunta de formulario
 * @param {string} prompt - Descripción de la pregunta a generar
 * @param {string} type - Tipo de pregunta (text, multiple, checkbox, number)
 * @returns {Promise<Object>} - Objeto de pregunta generada
 */
export async function generateSingleQuestion(prompt, type = "text") {
  // Validar tipo de pregunta
  if (!["text", "multiple", "checkbox", "number"].includes(type)) {
    throw new Error("Tipus de pregunta invàlid");
  }

  try {
    const questionPrompt = `Genera una sola pregunta de tipus "${type}" sobre: ${prompt}`;
    
    const response = await generateFormQuestions(questionPrompt);
    
    // Devolver la primera pregunta de la respuesta
    if (response.questions && response.questions.length > 0) {
      return response.questions[0];
    } else {
      throw new Error("No s'ha pogut generar la pregunta");
    }
  } catch (error) {
    console.error("Error en generateSingleQuestion:", error);
    throw error;
  }
}