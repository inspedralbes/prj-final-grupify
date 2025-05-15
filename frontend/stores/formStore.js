import { defineStore } from "pinia";
import { ref } from "vue";

export const useFormStore = defineStore("formStore", () => {
  const forms = ref([]);
  const loading = ref(false);

  /**
   * Fetch forms from the API or combine data from other stores
   */
  const fetchForms = async () => {
    try {
      loading.value = true;
      
      // You might need to fetch from your API endpoint
      // This is a placeholder implementation that you can modify based on your backend
      const response = await fetch("http://localhost:8000/api/forms");
      
      if (!response.ok) {
        throw new Error("Failed to fetch forms");
      }
      
      forms.value = await response.json();
      
      // If you don't have an actual API endpoint yet, you could populate with sample data:
      // forms.value = [
      //   { id: 1, title: "Avaluació de Matemàtiques", status: "completed" },
      //   { id: 2, title: "Qüestionari d'Hàbits d'Estudi", status: "pending" },
      //   { id: 3, title: "Enquesta de Satisfacció", status: "completed" }
      // ];
      
      return forms.value;
    } catch (error) {
      console.error("Error fetching forms:", error);
      // If the API call fails, at least provide some sample data
      forms.value = [
        { id: 1, title: "Avaluació de Matemàtiques", status: "completed" },
        { id: 2, title: "Qüestionari d'Hàbits d'Estudi", status: "pending" },
        { id: 3, title: "Enquesta de Satisfacció", status: "completed" }
      ];
    } finally {
      loading.value = false;
    }
  };

  return {
    forms,
    loading,
    fetchForms
  };
});
