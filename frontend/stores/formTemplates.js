import { defineStore } from "pinia";

export const useFormTemplatesStore = defineStore("formTemplates", () => {
  const templates = ref([
    {
      id: "math-evaluation",
      title: "Avaluació de Matemàtiques 1r ESO",
      description: "Avaluació completa de coneixements matemàtics bàsics",
      category: "evaluacion",
      questions: [
        {
          type: "multiple",
          title: "Quin és el resultat de 3x + 5 = 14?",
          options: [
            { text: "x = 3", value: 1 },
            { text: "x = 4", value: 0 },
            { text: "x = 5", value: 0 },
          ],
        },
        {
          type: "multiple",
          title: "Quina fracció representa la meitat?",
          options: [
            { text: "1/2", value: 1 },
            { text: "2/4", value: 1 },
            { text: "3/4", value: 0 },
          ],
        },
      ],
    },
    {
      id: "study-habits",
      title: "Qüestionari d'Hàbits d'Estudi",
      description: "Avaluació d'hàbits i tècniques destudi de lalumne",
      category: "tutoria",
      questions: [
        {
          type: "multiple",
          title: "Quantes hores dediques a l'estudi diàriament?",
          options: [
            { text: "Menys d'1 hora", value: 0 },
            { text: "Entre 1 y 2 hores", value: 1 },
            { text: "Més de 2 hores", value: 2 },
          ],
        },
        {
          type: "checkbox",
          title: "Quines tècniques destudi utilitzes?",
          options: [
            { text: "Resums", value: 0 },
            { text: "Mapes conceptuals", value: 1 },
            { text: "Subratllat", value: 2 },
          ],
        },
      ],
    },
    {
      id: "course-satisfaction",
      title: "Enquesta de Satisfacció del Curs",
      description: "Avaluació de la satisfacció general amb el curs",
      category: "feedback",
      questions: [
        {
          type: "multiple",
          title: "Com valoraries la qualitat de les classes?",
          options: [
            { text: "Excel·lent", value: 4 },
            { text: "Bona", value: 3 },
            { text: "Regular", value: 2 },
            { text: "Necessita millorar", value: 1 },
          ],
        },
        {
          type: "text",
          title: "Quins aspectes del curs creus que es podrien millorar?",
        },
      ],
    },
  ]);

  const categories = {
    evaluacion: "Avaluació Acadèmica",
    tutoria: "Tutoria i Seguiment",
    feedback: "Feedback i Millora",
  };

  const getTemplateById = id => {
    return templates.value.find(template => template.id === id);
  };

  const getTemplatesByCategory = category => {
    return templates.value.filter(template => template.category === category);
  };

  return {
    templates,
    categories,
    getTemplateById,
    getTemplatesByCategory,
  };
});
