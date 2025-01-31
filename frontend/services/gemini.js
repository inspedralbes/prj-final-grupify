import { GoogleGenerativeAI } from "@google/generative-ai";

const genAI = new GoogleGenerativeAI("AIzaSyC0NI-xnqWHJy-0XoJl7cVo63MYpqC1r9E");

const MAX_RETRIES = 3;
const RETRY_DELAY = 1000;

const sleep = ms => new Promise(resolve => setTimeout(resolve, ms));

const parseJSONSafely = text => {
  try {
    // Find JSON object in text (handles cases where there might be additional text)
    const jsonMatch = text.match(/\{[\s\S]*\}/);
    if (!jsonMatch) {
      throw new Error("No s'ha trobat cap objecte JSON");
    }
    return JSON.parse(jsonMatch[0]);
  } catch (error) {
    throw new Error(`Error al analitzar la resposta: ${error.message}`);
  }
};

const validateQuestion = (question, index) => {
  if (
    !question.type ||
    !["text", "multiple", "checkbox", "number"].includes(question.type)
  ) {
    throw new Error(`Tipus de pregunta invàlid en la pregunta ${index + 1}`);
  }

  if (!question.title?.trim()) {
    throw new Error(`Títul invàlid en la pregunta ${index + 1}`);
  }

  if (["multiple", "checkbox"].includes(question.type)) {
    if (!Array.isArray(question.options) || question.options.length < 2) {
      throw new Error(
        `Obligatori almenys dues opcións en la pregunta ${index + 1}`
      );
    }

    question.options = question.options.map((option, optionIndex) => ({
      text: option.text || `Opción ${optionIndex + 1}`,
      value: typeof option.value === "number" ? option.value : optionIndex,
    }));
  }

  return question;
};

const validateResponse = response => {
  if (!response?.title?.trim()) {
    throw new Error("El títul del formulario es obligatori");
  }

  if (!response?.description?.trim()) {
    throw new Error("La descripció del formularii es obligatoria");
  }

  if (!Array.isArray(response.questions) || response.questions.length === 0) {
    throw new Error("El formulari ha de tenir mínim una pregunta");
  }

  response.questions = response.questions.map((q, i) => validateQuestion(q, i));
  return response;
};

export async function generateFormQuestions(prompt, retryCount = 0) {
  if (!prompt?.trim()) {
    throw new Error(
      "Por favor, proporciona una descripción del formulario que necesitas."
    );
  }

  try {
    const model = genAI.getGenerativeModel({ model: "gemini-pro" });

    const systemPrompt = `Actúa como un experto en diseño de formularios educativos. Genera un formulario completo basado en la descripción proporcionada.

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
    }
  ]
}

Los tipos de pregunta permitidos son: "text", "multiple", "checkbox", "number"`;

    const result = await model.generateContent([
      { text: systemPrompt },
      { text: `Descripción del formulario: ${prompt}` },
    ]);

    if (!result?.response?.text()) {
      throw new Error("No se recibió respuesta del modelo de IA.");
    }

    const parsedResponse = parseJSONSafely(result.response.text());
    return validateResponse(parsedResponse);
  } catch (error) {
    console.error("Error en generateFormQuestions:", error);

    if (retryCount < MAX_RETRIES) {
      await sleep(RETRY_DELAY);
      return generateFormQuestions(prompt, retryCount + 1);
    }

    throw new Error(
      "No se pudo generar el formulario. " +
        "Por favor, intenta con una descripción más clara o específica."
    );
  }
}
