import { GoogleGenerativeAI } from "@google/generative-ai";
import { useRuntimeConfig } from "#app";

// Initialize the Generative AI client with API key from environment variables
const config = useRuntimeConfig();
const API_KEY = config.public.geminiApiKey;
const genAI = new GoogleGenerativeAI(API_KEY);

// Modelo actualizado - usar gemini-1.5-pro en lugar de gemini-pro
const MODEL_NAME = "gemini-1.5-pro"; 

// Constants for retry mechanism
const MAX_RETRIES = 5;
const RETRY_DELAY = 2000; // 2 seconds between retries
const REQUEST_TIMEOUT = 60000; // 60 seconds timeout

/**
 * Helper function to delay execution
 * @param {number} ms - Milliseconds to delay
 * @returns {Promise} - Resolves after the specified delay
 */
const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

/**
 * Safely parses JSON from text response, handling various formats
 * @param {string} text - Text containing JSON
 * @returns {Object} - Parsed JSON object
 */
const parseJSONSafely = text => {
  try {
    // First try direct parsing in case the response is already clean JSON
    try {
      return JSON.parse(text);
    } catch (e) {
      // If direct parsing fails, try to extract JSON object from the text
      const jsonMatch = text.match(/\{[\s\S]*\}/);
      if (!jsonMatch) {
        throw new Error("No s'ha trobat cap objecte JSON en la resposta");
      }
      return JSON.parse(jsonMatch[0]);
    }
  } catch (error) {
    console.error("Error parsing JSON:", error, "Text:", text);
    throw new Error(`Error al analitzar la resposta JSON: ${error.message}`);
  }
};

/**
 * Validate a single question format
 * @param {Object} question - Question object to validate
 * @param {number} index - Index of the question for error reporting
 * @returns {Object} - Validated and cleaned question object
 */
const validateQuestion = (question, index) => {
  // Check question type
  if (
    !question.type ||
    !["text", "multiple", "checkbox", "number"].includes(question.type)
  ) {
    throw new Error(`Tipus de pregunta invàlid en la pregunta ${index + 1}`);
  }

  // Check question title
  if (!question.title?.trim()) {
    throw new Error(`Títul invàlid en la pregunta ${index + 1}`);
  }

  // For multiple choice questions, validate options
  if (["multiple", "checkbox"].includes(question.type)) {
    if (!Array.isArray(question.options) || question.options.length < 2) {
      throw new Error(
        `La pregunta ${index + 1} de tipus selecció necessita almenys dues opcions`
      );
    }

    // Normalize options format
    question.options = question.options.map((option, optionIndex) => ({
      text: option.text || `Opción ${optionIndex + 1}`,
      value: typeof option.value === "number" ? option.value : optionIndex,
    }));
  }

  return question;
};

/**
 * Validate the complete form response
 * @param {Object} response - Form response to validate
 * @returns {Object} - Validated form response
 */
const validateResponse = response => {
  // Check form title
  if (!response?.title?.trim()) {
    throw new Error("El títul del formulari és obligatori");
  }

  // Check form description
  if (!response?.description?.trim()) {
    throw new Error("La descripció del formulari és obligatòria");
  }

  // Check questions array
  if (!Array.isArray(response.questions) || response.questions.length === 0) {
    throw new Error("El formulari ha de tenir almenys una pregunta");
  }

  // Validate each question
  response.questions = response.questions.map((q, i) => validateQuestion(q, i));
  
  return response;
};

/**
 * Generate form questions using Google's Gemini AI
 * @param {string} prompt - User prompt describing the form
 * @param {number} retryCount - Current retry count (used internally)
 * @returns {Promise<Object>} - Generated form with questions
 */
export async function generateFormQuestions(prompt, retryCount = 0) {
  // Validate input
  if (!prompt?.trim()) {
    throw new Error(
      "Si us plau, proporciona una descripció del formulari que necessites."
    );
  }

  // Attempt to import the fallback templates first in case the API fails
  let fallbackTemplates;
  try {
    const { selectFallbackTemplate } = await import("@/components/utils/formFallbacks");
    const template = selectFallbackTemplate(prompt);
    fallbackTemplates = template;
  } catch (fallbackError) {
    console.log("No se pudieron cargar las plantillas de respaldo:", fallbackError);
  }

  // Create a promise that rejects after timeout
  const timeoutPromise = new Promise((_, reject) => {
    setTimeout(() => {
      reject(new Error("La sol·licitud ha excedit el temps d'espera"));
    }, REQUEST_TIMEOUT);
  });

  try {
    // Verificar que tenemos una API key
    if (!API_KEY) {
      console.error("No se ha configurado GEMINI_API_KEY en las variables de entorno");
      throw new Error("Error de configuración: No se ha encontrado la clave de API de Gemini");
    }

    // Get the Gemini model
    const model = genAI.getGenerativeModel({ model: MODEL_NAME });

    // System prompt with detailed instructions
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

    // Create user message with the prompt
    const userMessage = `Necesito un formulario sobre: ${prompt}
- Prioriza preguntas relevantes para profesores
- Adapta el nivel educativo según el contexto
- Usa lenguaje y terminología apropiados para educación
- Si se trata de un test de comprensión lectora, incluye un breve texto y preguntas sobre ese texto
- Genera formularios más sencillos con instrucciones claras`;

    // Race between AI request and timeout
    const result = await Promise.race([
      model.generateContent([
        { text: systemPrompt },
        { text: userMessage }
      ]),
      timeoutPromise
    ]);

    // Check if response is valid
    if (!result?.response?.text()) {
      throw new Error("No s'ha rebut resposta del model d'IA");
    }

    // Parse and validate response
    const responseText = result.response.text();
    const parsedResponse = parseJSONSafely(responseText);
    return validateResponse(parsedResponse);
  } catch (error) {
    console.error("Error en generateFormQuestions:", error);
    console.log("Detalles del prompt:", prompt);
    console.log("Intento número:", retryCount + 1);

    // Si hay un error de cuota o API, proporciona un mensaje específico
    if (error.message.includes("quota") || 
        error.message.includes("429") || 
        error.message.includes("exceeded your current quota")) {
      console.error("Error de cuota en la API de Gemini:", error.message);
      
      // Si tenemos un template de respaldo disponible, lo devolvemos en lugar de fallar
      if (fallbackTemplates) {
        console.log("Usando plantilla de respaldo debido a error de cuota de API");
        return fallbackTemplates;
      }
      
      throw new Error("S'ha excedit la quota d'API. Si us plau, intenta-ho més tard.");
    }
    
    // Si hay un error de red o API, proporciona un mensaje específico
    if (error.message.includes("network") || error.message.includes("API") || error.message.includes("404")) {
      console.error("Problema de conexión con la API de Gemini:", error.message);
      
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
 * Generate a single form question
 * @param {string} prompt - Description of the question to generate
 * @param {string} type - Question type (text, multiple, checkbox, number)
 * @returns {Promise<Object>} - Generated question object
 */
export async function generateSingleQuestion(prompt, type = "text") {
  // Validate question type
  if (!["text", "multiple", "checkbox", "number"].includes(type)) {
    throw new Error("Tipus de pregunta invàlid");
  }

  try {
    const questionPrompt = `Genera una sola pregunta de tipus "${type}" sobre: ${prompt}`;
    
    const response = await generateFormQuestions(questionPrompt);
    
    // Return the first question from the response
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