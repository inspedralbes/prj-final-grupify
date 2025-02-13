import { ref, watch } from 'vue';
import { Note } from '~/types';
import { Document, Packer, Paragraph, TextRun } from 'docx';

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
  };

  // Update a note
  const updateNote = (noteId: string, updates: Partial<Note>) => {
    const index = notes.value.findIndex(n => n.id === noteId);
    if (index !== -1) {
      notes.value[index] = {
        ...notes.value[index],
        ...updates,
        lastModified: Date.now()
      };
      saveNotes();
    }
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
    const doc = new Document({
      sections: [{
        properties: {},
        children: [
          new Paragraph({
            children: [
              new TextRun({
                text: note.title,
                bold: true,
                size: 32,
              }),
            ],
          }),
          new Paragraph({
            children: [
              new TextRun({
                text: note.subject,
                size: 24,
                color: '666666',
              }),
            ],
          }),
          new Paragraph({
            children: [
              new TextRun({
                text: note.content.replace(/<[^>]*>/g, ''),
                size: 24,
              }),
            ],
          }),
        ],
      }],
    });

    const blob = await Packer.toBlob(doc);
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${note.title}.docx`;
    a.click();
    window.URL.revokeObjectURL(url);
  };

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