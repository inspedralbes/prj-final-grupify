// Expanded suggestion groups with more variety
const suggestionGroups = [
  // Evaluación académica
  [
    "Necessito un formulari per avaluar matemàtiques de 1r ESO",
    "Crea una avaluació de física i química",
    "Genera un test de comprensió lectora",
    "Dissenya una prova d'anglès nivell B1",
    "Avaluació de coneixements d'història",
    "Test de geografia sobre la Unió Europea",
  ],
  // Seguiment i tutoria
  [
    "Qüestionari sobre hàbits d'estudi",
    "Enquesta sobre l'ambient a classe",
    "Formulari de seguiment tutorial",
    "Avaluació de necessitats de suport",
    "Qüestionari d'adaptació al curs",
    "Qüestionari d'adaptació al curs",
  ],
  // Feedback i millora
  [
    "Enquesta de satisfacció trimestral",
    "Avaluació de la metodologia docent",
    "Valoració de recursos didàctics",
    "Suggeriments de millora del curs",
    "Feedback sobre activitats extraescolars",
    "Avaluació del pla d'estudis",
  ],
  // Desenvolupament personal
  [
    "Qüestionari d'interessos professionals",
    "Test d'estils d'aprenentatge",
    "Test d'estils d'aprenentatge",
    "Enquesta sobre gestió del temps",
    "Formulari d'objectius acadèmics",
    "Valoració de competències digitals",
  ],
];

export function useFormSuggestions() {
  const currentGroupIndex = ref(0);

  const rotateSuggestions = () => {
    currentGroupIndex.value =
      (currentGroupIndex.value + 1) % suggestionGroups.length;
    return getRandomFromCurrentGroup();
  };

  const getRandomFromCurrentGroup = () => {
    const currentGroup = suggestionGroups[currentGroupIndex.value];
    const shuffled = [...currentGroup].sort(() => Math.random() - 0.5);
    return shuffled.slice(0, 3); // Return only 3 random suggestions
  };

  const getRandomSuggestions = () => {
    const allSuggestions = suggestionGroups.flat();
    const shuffled = [...allSuggestions].sort(() => Math.random() - 0.5);
    return shuffled.slice(0, 3);
  };

  return {
    getCurrentSuggestions: getRandomFromCurrentGroup,
    rotateSuggestions,
    getRandomSuggestions,
  };
}
