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
  const groupStore = useGroupStore();
  const cescStore = useCescStore();

  // Variables reactivas para almacenar datos
  const students = ref([]);
  const courses = ref([]);
  const forms = ref([]);
  const responses = ref([]);
  const waitingForConfirmation = ref(false);
  let extractedGroups = [];

  const handleUserConfirmation = (content, chatId) => {
    const userMessage = {
      type: "user",
      content,
      timestamp: new Date().toISOString(),
    };
    chatStore.addMessage(chatId, userMessage);
    if (content.trim().toUpperCase() === "S") {
      handleDataGroup(extractedGroups);
      const aiMessage = {
        type: "ai",
        content: "Generant grups...",
        timestamp: new Date().toISOString(),
      };
      chatStore.addMessage(chatId, aiMessage);
      waitingForConfirmation.value = false;
    } else if (content.trim().toUpperCase() === "N") {
      const chatMessage = {
        type: "ai",
        content: "No s'han generat els grups.",
        timestamp: new Date().toISOString(),
      };
      chatStore.addMessage(chatId, chatMessage);
      waitingForConfirmation.value = false;
    } else {
      const chatMessage = {
        type: "ai",
        content: "Si us plau, respon com a 'S' per a si o 'N' per a no.",
        timestamp: new Date().toISOString(),
      };
      chatStore.addMessage(chatId, chatMessage);
    }
  };

  function extractGroups(text) {
    const groups = [];
    const groupRegex =
      /Grup \d+:\s*Name grup:\s*(.*?)\s*\nIntegrants:\s*(.*?)(?=\n\n|\nGrup|\n?$)/gs;
    let match;
    while ((match = groupRegex.exec(text)) !== null) {
      const name_group = match[1].trim();
      const integrantsRaw = match[2].trim();
      const integrants =
        integrantsRaw
          .match(/\((\d+)\)/g)
          ?.map(id => id.replace(/\(|\)/g, "")) || [];
      groups.push({
        name_group,
        description_group:
          "Grup format amb relacions positives identificades al sociograma",
        integrants,
      });
    }
    extractedGroups = groups;
  }

  const handleDataGroup = groups => {
    groups.forEach(group => {
      const groupName = group.name_group;
      const groupDescription = group.description_group;
      const number_of_students = group.integrants.length;
      const integrants = group.integrants;

      // Crear el grupo en la base de datos
      const groupData = {
        name: groupName,
        description: groupDescription,
        number_of_students,
      };

      createGroup(groupData, integrants);
    });
  };

  const createGroup = async (group, integrants) => {
    const response = await groupStore.createGroup(group);

    console.log(group);
    console.log(integrants);
    if (response) {
      console.log(response.id);
      groupStore.addStudentsToGroup(response.id, integrants);
    } else {
      console.error("Error creating group:", response);
    }
  };

  onMounted(async () => {
    try {
      await sociogramStore.fetchResponses();
    } catch (error) {
      console.error("Error loading responses:", error);
    }
  });
  // Cargar formularios activos
  onMounted(async () => {
    fetch("https://api.basebrutt.com/api/forms/active")
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
    fetch("https://api.basebrutt.com/api/all-responses")
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
      const authStore = useAuthStore();
      const userId = authStore.user?.id;
      await coursesStore.fetchCourses(false, userId);
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
        if (
          sociogramResponses &&
          sociogramResponses.all_responses != null > 0
        ) {
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

    if (sociogramStore.currentCourse != null) {
    }
  });

  const waitForDataCesc = () => {
    return new Promise(resolve => {
      const interval = setInterval(() => {
        const cescResponses = cescStore.responses;
        console.log("Waiting for data...", cescResponses);
        if (cescResponses && cescResponses.all_responses != null > 0) {
          clearInterval(interval);
          resolve();
        }
      }, 100); // Verificar cada 100ms
    });
  };

  onMounted(async () => {
    console.log("Waiting for data...");
    await waitForDataCesc();
    console.log("Data cargadad: ", cescStore.responses);
  });

  const genAI = new GoogleGenerativeAI(
    "AIzaSyCeLUwISVfHOQbA7HeN_coGBnMZHvHNgic"
  );

  const processFile = (content, chatId) => {
    const documentId = knowledgeStore.addDocument(content);
    chatStore.addDocumentToChat(chatId, documentId);
  };

  const sendMessage = async (content, chatId) => {
    if (!content.trim()) return;
    if (waitingForConfirmation.value) {
      handleUserConfirmation(content, chatId);
      return;
    }

    // Agregar el mensaje del usuario a la conversación
    const userMessage = {
      type: "user",
      content,
      timestamp: new Date().toISOString(),
    };
    chatStore.addMessage(chatId, userMessage);

    isLoading.value = true;

    try {
      const model = genAI.getGenerativeModel({
        model: "gemini-2.0-flash-lite-preview-02-05",
      });

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
      const allResponsesSocriogramData =
        sociogramStore.responsesByCourseDivision.all_responses;

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

      // asdas
      // Recuperar los datos del sociograma desde el sociogramStore CURSO Y DIVISION ACTUAL
      const cescData = cescStore.responses.all_responses;

      // Construir el contexto del sociograma
      const cescContext =
        cescData && cescData.length > 0
          ? `
        Dades del sociograma:
        ${JSON.stringify(cescData)}
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
        Ten en compte que el Formulari ID: 3 és el sociograma."
        5. Formacio de gruops amb el seguent format: Simpre que se mencione la palabra "generar grup","crear grupo", "crear grup" "formr grupos" "Formar grup" en la respuesta utilizar siempre el siguiente formato.
        Siempre usar el nombre y apellido del usuario e incluir el id del usuario. Agregar siempre un name_group.
        FORMACIO GRUPS:
        Grup 1:
        Name grup: 
        Integrants: (id) Nombre y  Apellido, (id) Nombre y  Apellido
        etc...

        cuando se hable del formulario cesc quiero que sigas este formato:
        ### Formulario CESC (ID: 2)
        
        \n
        ---[Usuari]---
        ✦ Nombre del alumno (ID:3)
          - Pregnta:
            ➤ Respuesta 1
            ➤ Respuesta 1 
            ➤ Respuesta 1  
          


        Context of all responses of sociogram:
          ${allResponsesSociogramContext}


        Context del sociograma:
          ${sociogramContext}

        
        Contex of cesc responses of course and division

        ${cescContext}
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
        ${cescContext}
        ${responsesContext} // Usar el contexto combinado de respuestas
        ${contextPrompt}
        Respon a: ${content}
      `;

      const result = await model.generateContent(prompt);
      const response = await result.response;
      const text = response.text();

      const keywords = /FORMACIO GROUPOS|FORMACIO GRUP/i;

      if (keywords.test(text)) {
        console.log("Text contains keywords:", text);

        extractGroups(text);

        const aiMessage = {
          type: "ai",
          content: text + "\n\nDesea generar los grupos: (S/N)",
          timestamp: new Date().toISOString(),
        };
        chatStore.addMessage(chatId, aiMessage);

        waitingForConfirmation.value = true;
      } else {
        const aiMessage = {
          type: "ai",
          content: text,
          timestamp: new Date().toISOString(),
        };
        chatStore.addMessage(chatId, aiMessage);
      }

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
