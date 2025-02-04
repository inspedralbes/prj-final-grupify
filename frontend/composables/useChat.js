import { ref, onMounted } from "vue";
import { GoogleGenerativeAI } from "@google/generative-ai";
import { useChatStore } from "@/stores/chatStore";
import { useKnowledgeStore } from "@/stores/knowledgeStore";
import { useStudentsStore } from "@/stores/studentsStore";

export function useChat() {
  const isLoading = ref(false);
  const chatStore = useChatStore();
  const knowledgeStore = useKnowledgeStore();
  const studentsStore = useStudentsStore();
  const students = ref([]);
  const genAI = new GoogleGenerativeAI(
    "AIzaSyC0NI-xnqWHJy-0XoJl7cVo63MYpqC1r9E"
  );

  onMounted(async () => {
    try {
      await studentsStore.fetchStudents();
      students.value = studentsStore.students;
      console.log("Students loaded:", students.value);
    } catch (error) {
      console.error("Error loading students:", error);
    }
  });

  const processFile = (content, chatId) => {
    const documentId = knowledgeStore.addDocument(content);
    chatStore.addDocumentToChat(chatId, documentId);
  };

  const sendMessage = async (content, chatId) => {
    if (!content.trim()) return;

    const userMessage = {
      type: "user",
      content,
      timestamp: new Date().toISOString(),
    };

    chatStore.addMessage(chatId, userMessage);
    isLoading.value = true;

    try {
      const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash" });

      // Obtener documentos asociados al chat
      const chat = chatStore.chats.find(c => c.id === chatId);
      const chatDocuments = chat.documents
        .map(docId => knowledgeStore.getDocumentById(docId))
        .filter(Boolean);

      const chatHistory = chat ? chat.messages : [];

      const conversationHistory = chatHistory
        .map(
          msg =>
            `${msg.type === "user" ? "Usuario" : "Asistente"}: ${msg.content}`
        )
        .join("\n");

      // Prompt base con instrucciones y rol
      const basePrompt = `You are an experienced psychologist, group manager, friendly, and conversational AI assistant for Institut Pedralbes that helps teachers manage class groups and prevent isolation or bullying situations. Your main language is Catalan, ALWAYS ANSWER IN CATALAN, but answer in the language in which they ask you. You can engage in natural conversations about any topic while maintaining your role. You should understand and respond in both Spanish and English, matching the language of the user's input.`;

      // Construir el historial de conversación
      const conversationContext = conversationHistory
        ? `\n\nThis is our complete conversation so far:\n${conversationHistory}\n\n`
        : "";

      let contextPrompt = "";

      if (chatDocuments.length > 0) {
        contextPrompt +=
          `\n\nAdditionally, here is some specific context that might be relevant:\n` +
          chatDocuments.map(doc => doc.content).join("\n---\n") +
          "\n\nKeeping this context in mind if relevant, ";
      }

      if (students.value.length > 0) {
        contextPrompt +=
          `\n\nHere is the list of students:\n` +
          students.value.map(student => student.name).join(", ") +
          "\n\n";
      }

      // buscamos si en el mensaje hay un estudiante
      const studentMatch = students.value.find(student =>
        content.toLowerCase().includes(student.name.toLowerCase())
      );

      if (studentMatch) {
        // Agregamos información detallada del estudiante encontrado
        contextPrompt += `\n\nInformación del estudiante encontrado:
        Nombre: ${studentMatch.name}
        Apellido: ${studentMatch.last_name}
        Email: ${studentMatch.email}
        Curso: ${studentMatch.course}
        División: ${studentMatch.division}\n\n`;
      }
      // ---------------------------------------------------------------------------

      // Crear el prompt final
      const prompt = `${basePrompt}${conversationContext}${contextPrompt}please respond to: ${content}`;

      const result = await model.generateContent(prompt);
      const response = await result.response;
      const text = response.text();

      const aiMessage = {
        type: "ai",
        content: text,
        timestamp: new Date().toISOString(),
      };

      chatStore.addMessage(chatId, aiMessage);
      return text;
    } catch (error) {
      console.error("Error in sendMessage:", error);
      throw new Error(`Failed to process message: ${error.message}`);
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    sendMessage,
    processFile,
  };
}
