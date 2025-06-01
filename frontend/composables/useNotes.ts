import { ref, watch } from 'vue';
import { Note } from '~/types';
import { Document, Packer, Paragraph, TextRun } from 'docx';
import htmlToDocx from 'html-to-docx';

export const useNotes = () => {
  const notes = ref<Note[]>([]);
  const currentNote = ref<Note | null>(null);

  // Load notes from localStorage
  const loadNotes = () => {
    const savedNotes = localStorage.getItem('notes');
    if (savedNotes) {
      notes.value = JSON.parse(savedNotes);
    }
  };

  // Save notes to localStorage
  const saveNotes = () => {
    localStorage.setItem('notes', JSON.stringify(notes.value));
  };

  // Create a new note
  const createNote = (subject: string) => {
    const newNote: Note = {
      id: Date.now().toString(),
      title: 'Untitled Note',
      content: '',
      subject,
      lastModified: Date.now()
    };
    notes.value.push(newNote);
    currentNote.value = newNote;
    saveNotes();
    return newNote; // Retornamos la nueva nota para uso inmediato
  };

  // Update a note with improved reactivity
  const updateNote = (noteId: string, updates: Partial<Note>) => {
    const index = notes.value.findIndex(n => n.id === noteId);
    if (index !== -1) {
      // Crear una nueva referencia del objeto para asegurar reactividad
      const updatedNote = {
        ...notes.value[index],
        ...updates,
        lastModified: Date.now()
      };

      // Actualizar el array de notes con la nueva referencia
      notes.value[index] = updatedNote;

      // Si es la nota actual, actualizar también currentNote
      if (currentNote.value?.id === noteId) {
        currentNote.value = updatedNote;
      }

      // Guardar en localStorage
      saveNotes();

      return updatedNote; // Retornar la nota actualizada
    }
    return null;
  };

  // Delete a note
  const deleteNote = (noteId: string) => {
    notes.value = notes.value.filter(n => n.id !== noteId);
    if (currentNote.value?.id === noteId) {
      currentNote.value = null;
    }
    saveNotes();
  };

  // Export note as PDF
  const exportNotePDF = async (note: Note) => {
    const element = document.createElement('div');
    element.innerHTML = `
      <div style="font-family: Arial, sans-serif; padding: 20px;">
        <h1 style="font-size: 24px; margin-bottom: 10px;">${note.title}</h1>
        <h2 style="font-size: 16px; color: #666; margin-bottom: 20px;">${note.subject}</h2>
        <div style="font-size: 14px; line-height: 1.6;">${note.content}</div>
      </div>
    `;

    const opt = {
      margin: [15, 15],
      filename: `${note.title}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: {
        scale: 2,
        useCORS: true,
        letterRendering: true
      },
      jsPDF: {
        unit: 'mm',
        format: 'a4',
        orientation: 'portrait'
      }
    };

    try {
      const { default: html2pdf } = await import('html2pdf.js');
      await html2pdf().set(opt).from(element).save();
    } catch (error) {
      console.error('Error al exportar PDF:', error);
    }
  };

  // Export note as DOCX
  const exportNoteDocx = async (note: Note) => {
    const contentHTML = `
      <div style="font-family: Arial, sans-serif;">
        <h1>${note.title}</h1>
        <h2 style="color: #666;">${note.subject}</h2>
        ${note.content}
      </div>
    `;

    try {
      // Convierte el HTML a DOCX
      const fileBuffer = await htmlToDocx(contentHTML);
      const blob = new Blob([fileBuffer], {
        type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      });

      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `${note.title}.docx`;
      a.click();
      window.URL.revokeObjectURL(url);
    } catch (error) {
      console.error('Error al exportar DOCX:', error);
    }
  };


  // Watcher para asegurar que los cambios en las notas se reflejan en localStorage
  watch(notes, () => {
    saveNotes();
  }, { deep: true });

  // Initialize
  loadNotes();

  return {
    notes,
    currentNote,
    createNote,
    updateNote,
    deleteNote,
    exportNotePDF,
    exportNoteDocx
  };
};