import { useChatStore } from "@/stores/chatStore";
import { useKnowledgeStore } from "@/stores/knowledgeStore";
import { useStudentsStore } from "@/stores/studentsStore";
import { useCoursesStore } from "@/stores/coursesStore";
import { GoogleGenerativeAI } from "@google/generative-ai";

export function useChat() {
  const isLoading = ref(false);
  const chatStore = useChatStore();
  const knowledgeStore = useKnowledgeStore();
  const studentsStore = useStudentsStore();
  const coursesStore = useCoursesStore();
  const sociogramStore = useSociogramStore();

  // Variables reactivas para almacenar datos
  const students = ref([]);
  const courses = ref([]);
  const forms = ref([]);
  const responses = ref([]);


  onMounted(async () => {
    try{
      await sociogramStore.fetchResponses();
    }catch(error){
      console.error("Error loading responses:", error);
    }
  })
  // Cargar formularios activos
  onMounted(async () => {
    fetch("https://api.grupify.cat/api/forms/active")
      .then(response => response.json())
      .then(data => {
        forms.value = data;
        console.log("Forms loaded:", forms.value);
      })
      .catch(error => {
        console.error("Error loading forms:", error);
      });
  });

  // Cargar respuestas
  onMounted(async () => {
    fetch("https://api.grupify.cat/api/all-responses")
      .then(response => response.json())
      .then(data => {
        responses.value = data;
        console.log("Responses loaded:", responses.value);
      })
      .catch(error => {
        console.error("Error loading responses:", error);
      });
  });

  // Cargar alumnos
  onMounted(async () => {
    try {
      await studentsStore.fetchStudents();
      students.value = studentsStore.students;
      console.log("Students loaded:", students.value);
    } catch (error) {
      console.error("Error loading students:", error);
    }
  });

  // Cargar cursos
  onMounted(async () => {
    try {
      await coursesStore.fetchCourses();
      courses.value = coursesStore.courses;
      console.log("Courses loaded:", courses.value);
    } catch (error) {
      console.error("Error loading courses:", error);
    }
  });

  const waitForData = () => {
    return new Promise(resolve => {
      const interval = setInterval(() => {
        const sociogramResponses = sociogramStore.responses;
        console.log("Waiting for data...", sociogramResponses);
        if (sociogramResponses && sociogramResponses.all_responses.length > 0) {
          clearInterval(interval);
          resolve();
        }
      }, 100); // Verificar cada 100ms
    });
  };

  onMounted(async () => {
    console.log("Waiting for data...");
    await waitForData();
    console.log("Data cargadad: ", sociogramStore.responses);

    chatStore.createNewChat({ name: "Sociograma" });

    chatStore.addMessage(chatStore.currentChatId, {
      type: "system",
      content: `Benvingut a la sessió de sociograma. \n 
      CURSO: ${sociogramStore.currentCourse.courseName} \n
      DIVISION: ${sociogramStore.currentDivision.divisionName}
      \n Com puc ajudar-te?\n
        Pot fer preguntes sobre:\n
        - Sobre les preferències individuals\n
        - Sobre les relacions entre alumnes\n
        - Sobre el sociograma en general\n
        I més...
      `,
      timestamp: new Date().toISOString(),
    });
    
    chatStore.addMessage(chatStore.currentChatId, {
      type: "user",
      content: `Que informació pots proporcionar-me sobre el sociograma?`,
      timestamp: new Date().toISOString(),
    })
  });

  const genAI = new GoogleGenerativeAI(
    "AIzaSyC0NI-xnqWHJy-0XoJl7cVo63MYpqC1r9E"
  );

  const processFile = (content, chatId) => {
    const documentId = knowledgeStore.addDocument(content);
    chatStore.addDocumentToChat(chatId, documentId);
  };

  const sendMessage = async (content, chatId) => {
    if (!content.trim()) return;

    // Agregar el mensaje del usuario a la conversación
    const userMessage = {
      type: "user",
      content,
      timestamp: new Date().toISOString(),
    };
    chatStore.addMessage(chatId, userMessage);

    isLoading.value = true;

    try {
      const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash" });

      // Obtener el contexto de la conversación actual
      const chat = chatStore.chats.find(c => c.id === chatId);
      const chatDocuments = chat.documents
        .map(docId => knowledgeStore.getDocumentById(docId))
        .filter(Boolean);

      const chatHistory = chat ? chat.messages : [];
      const conversationHistory = chatHistory
        .map(
          msg =>
            `${msg.type === "user" ? "Usuari" : "Assistent"}: ${msg.content}`
        )
        .join("\n");


      // RECUPERAR DATOS DE TODAS LAS RESPUESTAS DEL SOCIOGRAMA
      const allResponsesSocriogramData = sociogramStore.responsesByCourseDivision.all_responses;

      // Construir el contexto de todas las respuestas del sociograma
      const allResponsesSociogramContext =
        allResponsesSocriogramData && allResponsesSocriogramData.length > 0
          ? `
        Dades de totes les respostes del sociograma:
        ${JSON.stringify(allResponsesSocriogramData)}
      `
          : "No hi ha dades de totes les respostes del sociograma disponibles.";

      // Recuperar los datos del sociograma desde el sociogramStore CURSO Y DIVISION ACTUAL
      const sociogramData = sociogramStore.responses.all_responses;

      // Construir el contexto del sociograma
      const sociogramContext =
        sociogramData && sociogramData.length > 0
          ? `
        Dades del sociograma:
        ${JSON.stringify(sociogramData)}
      `
          : "No hi ha dades del sociograma disponibles.";

      // Construir el contexto de los alumnos
      const studentsContext = students.value.length
        ? `
          Informació dels alumnes:
          ${students.value
            .map(
              student =>
                `Nom: ${student.name}, Cognoms: ${student.last_name}, Curs: ${student.course}, Divisió: ${student.division}`
            )
            .join("\n")}
        `
        : "No hi ha informació d'alumnes disponible.";

      // Construir el contexto de los cursos
      const coursesContext = courses.value.length
        ? `
          Informació dels cursos:
          ${courses.value
            .map(
              course => `- Curs ${course.courseId}: Nom - ${course.courseName}`
            )
            .join("\n")}
        `
        : "No hi ha informació de cursos disponible.";

      // Construir el contexto de los formularios
      const formsContext = forms.value.length
        ? `
          Informació dels formularis actius:
          ${forms.value
            .map(
              form =>
                `- Formulari ID: ${form.id}, Títol: ${form.title}, Estat: ${form.status === 1 ? "Actiu" : "Inactiu"}`
            )
            .join("\n")}
        `
        : "No hi ha informació de formularis disponibles.";

      // Construir el contexto de las respuestas
      const responsesContext = responses.value.length
        ? `
          Informació de les respostes:
          ${responses.value
            .map(
              response =>
                `- Formulari: ${response.form_id}, Pregunta: ${response.question_id}, Respostes: ${JSON.stringify(response.responses)}`
            )
            .join("\n")}
        `
        : "No hi ha informació de respostes disponibles.";

      // Construir el prompt base
      const basePrompt = `
        Ets un assistent IA especialitzat en la gestió d'estadístiques i anàlisi de formularis per a l'Institut Pedralbes.
        Les teves responsabilitats inclouen:
        1. Responent a consultes sobre la participació dels alumnes en formularis.
        2. Ofereix resums semàntics de les respostes dels alumnes.
        3. Proporciona insights sobre la dinàmica grupal dels cursos.
        4. Donar informació sobre els alumnes i cursos, com ara rendiment acadèmic, participació en activitats, etc.
        Sempre respon en català, però si l'usuari pregunta en altre idioma, respon en el mateix idioma que ell utilitzi.
        Mantén una conversa natural i amable.
        Ten en compte que el Formulari ID: 3 és el sociograma.


        Context of all responses of sociogram:
          ${allResponsesSociogramContext}

        Context del sociograma:
          ${sociogramContext}
      `;

      const conversationContext = conversationHistory
        ? `\n\nAquest és el nostre diàleg fins ara:\n${conversationHistory}\n\n`
        : "";

      let contextPrompt = "";
      if (chatDocuments.length > 0) {
        contextPrompt =
          `\n\nAddicionalment, aquí tens algun context específic que pot ser rellevant:\n` +
          chatDocuments.map(doc => doc.content).join("\n---\n") +
          "\n\nTenint en compte aquest context, ";
      }

      // Combinar todo en el prompt final
      const prompt = `
        ${basePrompt}
        ${conversationContext}
        ${studentsContext}
        ${coursesContext}
        ${formsContext}
        ${responsesContext} // Usar el contexto combinado de respuestas
        ${contextPrompt}
        Respon a: ${content}
      `;

      // Enviar el prompt a la IA y obtener la respuesta
      const result = await model.generateContent(prompt);
      const response = await result.response;
      const text = response.text();

      // Agregar la respuesta de la IA a la conversación
      const aiMessage = {
        type: "ai",
        content: text,
        timestamp: new Date().toISOString(),
      };
      chatStore.addMessage(chatId, aiMessage);

      return text;
    } catch (error) {
      console.error("Error en sendMessage:", error);
      throw new Error(`No s'ha pogut processar el missatge: ${error.message}`);
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
