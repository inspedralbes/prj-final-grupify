// Plantillas de respaldo para cuando falla la generación con IA
export const fallbackTemplates = {
  "comprensio_lectora": {
    title: "Test de comprensió lectora",
    description: "Avaluació de la comprensió lectora dels alumnes mitjançant preguntes sobre un text",
    questions: [
      {
        type: "text",
        title: "Llegeix el text i després contesta les preguntes que se't plantegen:\n\nLa Mar és una nena que viu a prop del mar. Cada dia, quan acaba l'escola, va a la platja amb el seu gos Max. Li agrada recollir petxines i observar els ocells marins. Un dia va trobar una ampolla amb un missatge a dins. Era un mapa del tresor! La Mar i Max van decidir seguir les pistes del mapa."
      },
      {
        type: "multiple",
        title: "On viu la protagonista de la història?",
        options: [
          { text: "A la muntanya", value: 0 },
          { text: "A prop del mar", value: 1 },
          { text: "En una ciutat gran", value: 2 },
          { text: "En un bosc", value: 3 }
        ]
      },
      {
        type: "multiple",
        title: "Què li agrada fer a la Mar quan va a la platja?",
        options: [
          { text: "Nedar i fer castells de sorra", value: 0 },
          { text: "Jugar a pilota amb altres nens", value: 1 },
          { text: "Recollir petxines i observar ocells", value: 2 },
          { text: "Llegir llibres", value: 3 }
        ]
      },
      {
        type: "multiple",
        title: "Què va trobar la Mar a la platja?",
        options: [
          { text: "Un tresor", value: 0 },
          { text: "Una ampolla amb un missatge", value: 1 },
          { text: "Un vaixell petit", value: 2 },
          { text: "Un ocell ferit", value: 3 }
        ]
      },
      {
        type: "checkbox",
        title: "Quins personatges apareixen a la història? (Selecciona totes les correctes)",
        options: [
          { text: "La Mar", value: 0 },
          { text: "Max", value: 1 },
          { text: "Els pares de la Mar", value: 2 },
          { text: "Un pescador", value: 3 }
        ]
      },
      {
        type: "text",
        title: "Què creus que passarà després? Continua la història breument."
      }
    ]
  },
  
  "habitos_estudio": {
    title: "Qüestionari sobre hàbits d'estudi",
    description: "Avaluació dels hàbits d'estudi dels alumnes per identificar àrees de millora",
    questions: [
      {
        type: "multiple",
        title: "Quantes hores dediques a estudiar cada dia de mitjana?",
        options: [
          { text: "Menys d'1 hora", value: 0 },
          { text: "Entre 1 i 2 hores", value: 1 },
          { text: "Entre 2 i 3 hores", value: 2 },
          { text: "Més de 3 hores", value: 3 }
        ]
      },
      {
        type: "checkbox",
        title: "Quins recursos utilitzes per estudiar? (Selecciona totes les que apliquin)",
        options: [
          { text: "Llibres de text", value: 0 },
          { text: "Apunts propis", value: 1 },
          { text: "Vídeos explicatius", value: 2 },
          { text: "Aplicacions d'estudi", value: 3 },
          { text: "Grups d'estudi", value: 4 }
        ]
      },
      {
        type: "multiple",
        title: "Quin entorn prefereixes per estudiar?",
        options: [
          { text: "Silenci absolut", value: 0 },
          { text: "Amb música de fons", value: 1 },
          { text: "En una biblioteca", value: 2 },
          { text: "En un espai comú amb altres persones", value: 3 }
        ]
      },
      {
        type: "multiple",
        title: "Amb quina freqüència revises els teus apunts després de classe?",
        options: [
          { text: "Mai", value: 0 },
          { text: "De vegades", value: 1 },
          { text: "Sovint", value: 2 },
          { text: "Sempre", value: 3 }
        ]
      },
      {
        type: "number",
        title: "En una escala de l'1 al 10, com valores la teva organització del temps d'estudi?"
      },
      {
        type: "text",
        title: "Descriu breument quines dificultats trobes habitualment quan estudies."
      }
    ]
  },
  
  "matematicas": {
    title: "Avaluació de matemàtiques",
    description: "Test de coneixements bàsics de matemàtiques per a nivell d'ESO",
    questions: [
      {
        type: "multiple",
        title: "Quin és el resultat de 15 × 8?",
        options: [
          { text: "120", value: 1 },
          { text: "123", value: 2 },
          { text: "115", value: 3 },
          { text: "130", value: 4 }
        ]
      },
      {
        type: "multiple",
        title: "Quina és l'àrea d'un quadrat de 5 cm de costat?",
        options: [
          { text: "10 cm²", value: 0 },
          { text: "25 cm²", value: 1 },
          { text: "20 cm²", value: 2 },
          { text: "15 cm²", value: 3 }
        ]
      },
      {
        type: "multiple",
        title: "Si 3x - 2 = 10, quin és el valor de x?",
        options: [
          { text: "4", value: 1 },
          { text: "3", value: 2 },
          { text: "6", value: 3 },
          { text: "5", value: 4 }
        ]
      },
      {
        type: "number",
        title: "Calcula el perímetre d'un rectangle de 6 cm d'amplada i 8 cm de llargada. Escriu només el número."
      },
      {
        type: "multiple",
        title: "Quin és el valor de π arrodonit a dues decimals?",
        options: [
          { text: "3.12", value: 0 },
          { text: "3.14", value: 1 },
          { text: "3.16", value: 2 },
          { text: "3.18", value: 3 }
        ]
      },
      {
        type: "text",
        title: "Explica com resoldre la següent expressió: (2 + 3) × 4 - 5²"
      }
    ]
  }
};

// Función para seleccionar la plantilla más adecuada según la consulta
export function selectFallbackTemplate(query) {
  const queryLower = query.toLowerCase();
  
  if (queryLower.includes('comprensió lectora') || queryLower.includes('lectura') || queryLower.includes('llegir')) {
    return fallbackTemplates.comprensio_lectora;
  }
  
  if (queryLower.includes('hàbits d\'estudi') || queryLower.includes('estudi') || queryLower.includes('estudiar')) {
    return fallbackTemplates.habitos_estudio;
  }
  
  if (queryLower.includes('matemàtiques') || queryLower.includes('mates') || queryLower.includes('càlcul')) {
    return fallbackTemplates.matematicas;
  }
  
  // Si no coincide con ninguna plantilla específica, devolver una por defecto
  const keys = Object.keys(fallbackTemplates);
  const randomIndex = Math.floor(Math.random() * keys.length);
  return fallbackTemplates[keys[randomIndex]];
}
