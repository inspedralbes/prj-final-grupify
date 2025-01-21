import { generateFormQuestions } from "@/services/gemini";

export async function regenerateQuestion(originalQuestion, context) {
  try {
    const prompt = `Genera una nova pregunta basada en la pregunta original i el mateix context. 
    Pregunta original: "${originalQuestion.title}"
    Contexte del formulari: "${context}"
    Tipus de pregunta: ${originalQuestion.type}`;

    const response = await generateFormQuestions(prompt);
    const newQuestion = response.questions[0];
    return {
      ...newQuestion,
      id: originalQuestion.id,
    };
  } catch (error) {
    throw new Error("No es pot regenerar la pregunta. Intenta de nou.");
  }
}

export async function editQuestion(question, newPrompt) {
  try {
    const prompt = `Modifica aquesta pregunta segons les seguents descripcions: "${newPrompt}"
    Pregunta actual: "${question.title}"
    Tipo de pregunta: ${question.type}`;

    const response = await generateFormQuestions(prompt);
    const editedQuestion = response.questions[0];
    return {
      ...editedQuestion,
      id: question.id,
    };
  } catch (error) {
    throw new Error("No es pot editar la pregunta. Intenta de nou.");
  }
}
