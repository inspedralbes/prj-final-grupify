import { GoogleGenerativeAI } from '@google/generative-ai';
import { ref } from 'vue';

const genAI = new GoogleGenerativeAI('AIzaSyC0NI-xnqWHJy-0XoJl7cVo63MYpqC1r9E');

export const useGemini = () => {
  const generating = ref(false);
  const error = ref<string | null>(null);

  const generateNotes = async (prompt: string, subject: string) => {
    try {
      generating.value = true;
      error.value = null;
      
      const model = genAI.getGenerativeModel({ model: "gemini-pro" });
      
      const fullPrompt = `Generate detailed study notes about ${prompt} for the subject ${subject}. 
        Structure the content in a clean, formatted way using HTML tags.
        
        Follow these formatting rules:
        1. Use proper HTML structure
        2. Main topics should be wrapped in <h1> tags
        3. Subtopics should be wrapped in <h2> tags
        4. Use <p> tags for paragraphs
        5. Use <ul> and <li> for bullet points
        6. Important concepts should be wrapped in <strong> tags
        7. Use <em> for emphasis
        8. Examples should be wrapped in a <div class="example"> tag
        
        DO NOT use markdown symbols like #, *, or -. Use proper HTML tags instead.
        
        Make sure the content is well-structured and easy to read, with clear headings, 
        concise explanations, and relevant examples.`;
      
      const result = await model.generateContent(fullPrompt);
      const response = result.response;
      let text = response.text();
      
      // Clean up any potential markdown symbols that might have slipped through
      text = text.replace(/#{1,6}\s/g, '') // Remove heading hashes
             .replace(/\*\*/g, '') // Remove bold asterisks
             .replace(/\*/g, '') // Remove italic asterisks
             .replace(/^-\s/gm, '') // Remove bullet point dashes
             .replace(/^>\s/gm, ''); // Remove blockquote symbols
      
      return text;
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'An error occurred';
      return null;
    } finally {
      generating.value = false;
    }
  };

  return {
    generateNotes,
    generating,
    error
  };
};