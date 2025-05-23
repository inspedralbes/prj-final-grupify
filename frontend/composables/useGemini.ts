import { GoogleGenerativeAI } from '@google/generative-ai';
import { ref } from 'vue';
import { useRuntimeConfig } from '#app';

const config = useRuntimeConfig();
const genAI = new GoogleGenerativeAI(config.public.geminiApiKey);

export const useGemini = () => {
  const generating = ref(false);
  const error = ref<string | null>(null);

  const generateNotes = async (prompt: string, subject: string) => {
    try {
      generating.value = true;
      error.value = null;
      
      // Verificar que tenemos una API key
      if (!config.public.geminiApiKey) {
        throw new Error("Error de configuració: No s'ha trobat la clau d'API de Gemini");
      }
      
      const model = genAI.getGenerativeModel({ model: "gemini-pro" });
      
      const fullPrompt = `Generar notes d'estudi detallades sobre ${prompt} per al tema ${subject}. 
        Estructureu el contingut d'una manera neta i formatada mitjançant etiquetes HTML.
        
        Seguiu aquestes regles de format:
        1. Utilitzeu una estructura HTML adequada
        2. Els temes principals s'han d'embolicar en etiquetes <h1>
        3. Els subtemes s'han d'embolicar en etiquetes <h2>
        4. Utilitzeu etiquetes <p> per als paràgrafs
        5. Utilitzeu <ul> i <li> per a les vinyetes
        6. Els conceptes importants s'han d'embolicar en etiquetes <strong>
        7. Utilitzeu <em> per emfatitzar
        8. Els exemples s'han d'embolicar en una etiqueta <div class="example">
        
        NO utilitzeu símbols de reducció com #, * o -. Utilitzeu etiquetes HTML adequades.
        
        Assegureu-vos que el contingut estigui ben estructurat i fàcil de llegir, amb encapçalaments clars, 
        explicacions concises i exemples rellevants. Assegureu-vos que el contingut sigui escrit en català.`;
      
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
      console.error("Error al generar notes:", e);
      
      // Proporcionar mensajes de error específicos para errores comunes
      if (e instanceof Error) {
        const errorMsg = e.message;
        if (errorMsg.includes("quota") || errorMsg.includes("429") || errorMsg.includes("exceeded")) {
          error.value = "S'ha excedit la quota d'API de Gemini. Si us plau, intenta-ho més tard.";
        } else if (errorMsg.includes("network") || errorMsg.includes("connection")) {
          error.value = "Error de connexió amb l'API de Gemini. Comprova la teva connexió a Internet.";
        } else {
          error.value = errorMsg;
        }
      } else {
        error.value = "S'ha produït un error inesperat en generar les notes.";
      }
      
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