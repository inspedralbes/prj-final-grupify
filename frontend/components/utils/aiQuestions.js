import { generateFormQuestions } from "@/services/openai";

/**
 * Regenerates a question based on the original question and context
 * 
 * @param {Object} originalQuestion - The original question object
 * @param {string} context - The form context/description
 * @returns {Promise<Object>} - The regenerated question
 */
export async function regenerateQuestion(originalQuestion, context) {
  const MAX_RETRIES = 3;
  const RETRY_DELAY = 1000; // 1 second delay between retries
  
  const tryRegenerate = async (retryCount = 0) => {
    try {
      const prompt = `Genera una nova pregunta per substituir l'original, mantenint el mateix tipus i propòsit però amb una formulació diferent.
      
      Pregunta original: "${originalQuestion.title}"
      Contexte del formulari: "${context}"
      Tipus de pregunta: ${originalQuestion.type}
      ${originalQuestion.options ? `Opcions actuals: ${JSON.stringify(originalQuestion.options)}` : ''}
      
      Normes importants:
      1. La nova pregunta ha de tenir el mateix propòsit educatiu
      2. Manté el mateix tipus de pregunta: ${originalQuestion.type}
      3. Si la pregunta és de tipus "multiple" o "checkbox", genera noves opcions de resposta
      4. La nova pregunta ha de fer sentit en el context general del formulari
      5. Respon només amb un JSON vàlid`;

      const response = await generateFormQuestions(prompt);
      
      if (!response || !response.questions || response.questions.length === 0) {
        throw new Error("La resposta de l'IA no té el format esperat");
      }
      
      const newQuestion = response.questions[0];
      
      // Validate the question has the necessary properties
      if (!newQuestion.title || !newQuestion.type) {
        throw new Error("La pregunta generada no té tots els camps necessaris");
      }
      
      // For multiple choice questions, ensure options are present
      if (['multiple', 'checkbox'].includes(newQuestion.type) && 
          (!newQuestion.options || newQuestion.options.length < 2)) {
        throw new Error("La pregunta de tipus selecció necessita almenys dues opcions");
      }
      
      // Return the new question with the original ID
      return {
        ...newQuestion,
        id: originalQuestion.id,
      };
    } catch (error) {
      // If we haven't reached max retries, try again
      if (retryCount < MAX_RETRIES) {
        console.log(`Reintentant regenerar la pregunta (intent ${retryCount + 1}/${MAX_RETRIES})...`);
        await new Promise(resolve => setTimeout(resolve, RETRY_DELAY));
        return tryRegenerate(retryCount + 1);
      }
      
      // If all retries failed, throw the error
      console.error("Error al regenerar la pregunta després de múltiples intents:", error);
      throw new Error("No s'ha pogut regenerar la pregunta. Intenta de nou més tard.");
    }
  };
  
  // Start the retry process
  return tryRegenerate();
}

/**
 * Edits a question based on the provided prompt
 * 
 * @param {Object} question - The question to edit
 * @param {string} newPrompt - Instructions for editing
 * @returns {Promise<Object>} - The edited question
 */
export async function editQuestion(question, newPrompt) {
  const MAX_RETRIES = 3;
  const RETRY_DELAY = 1000; // 1 second delay between retries
  
  const tryEdit = async (retryCount = 0) => {
    try {
      const prompt = `Modifica aquesta pregunta segons les següents instruccions: "${newPrompt}"
      
      Pregunta actual: "${question.title}"
      Tipus de pregunta: ${question.type}
      ${question.options ? `Opcions actuals: ${JSON.stringify(question.options)}` : ''}
      
      Normes importants:
      1. Manté el mateix tipus de pregunta: ${question.type}
      2. Si la pregunta és de tipus "multiple" o "checkbox", manté l'estructura de les opcions
      3. Respon només amb un JSON vàlid que contingui la pregunta modificada`;

      const response = await generateFormQuestions(prompt);
      
      if (!response || !response.questions || response.questions.length === 0) {
        throw new Error("La resposta de l'IA no té el format esperat");
      }
      
      const editedQuestion = response.questions[0];
      
      // Validate the question has the necessary properties
      if (!editedQuestion.title || !editedQuestion.type) {
        throw new Error("La pregunta editada no té tots els camps necessaris");
      }
      
      // For multiple choice questions, ensure options are present
      if (['multiple', 'checkbox'].includes(editedQuestion.type) && 
          (!editedQuestion.options || editedQuestion.options.length < 2)) {
        throw new Error("La pregunta de tipus selecció necessita almenys dues opcions");
      }
      
      // Return the edited question with the original ID
      return {
        ...editedQuestion,
        id: question.id,
      };
    } catch (error) {
      // If we haven't reached max retries, try again
      if (retryCount < MAX_RETRIES) {
        console.log(`Reintentant editar la pregunta (intent ${retryCount + 1}/${MAX_RETRIES})...`);
        await new Promise(resolve => setTimeout(resolve, RETRY_DELAY));
        return tryEdit(retryCount + 1);
      }
      
      // If all retries failed, throw the error
      console.error("Error al editar la pregunta després de múltiples intents:", error);
      throw new Error("No s'ha pogut editar la pregunta. Intenta de nou més tard.");
    }
  };
  
  // Start the retry process
  return tryEdit();
}