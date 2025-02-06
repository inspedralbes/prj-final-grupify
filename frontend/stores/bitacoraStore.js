// stores/BitacoraStore.js
import { defineStore } from 'pinia'

export const useBitacoraStore = defineStore('BitacoraStore', {
  state: () => ({
    bitacora: null,
    notes: [],
    loadingNotes: false,
    selectedUserId: null,
    isEditing: false,
    showNoteModal: false,
    showCreateNoteModal: false,
    newNote: {
      title: '',
      content: '',
      id: null
    }
  }),

  getters: {
    groupedNotes: (state) => {
      return state.notes.reduce((acc, note) => {
        const userName = note.user ? `${note.user.name} ${note.user.last_name}` : 'Desconocido'
        if (!acc[userName]) {
          acc[userName] = []
        }
        acc[userName].push(note)
        return acc
      }, {})
    }
  },

  actions: {
    async fetchBitacora(groupId) {
      try {
        const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}`)
        this.bitacora = await response.json()
      } catch (error) {
        console.error("Error fetchBitacora:", error)
        throw error
      }
    },

    async fetchNotes(groupId) {
      this.loadingNotes = true
      try {
        const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}/notes`)
        this.notes = await response.json()
      } catch (error) {
        console.error("Error fetchNotes:", error)
        throw error
      } finally {
        this.loadingNotes = false
      }
    },

    async createNote(groupId) {
        if (!this.newNote.title || !this.newNote.content || !this.selectedUserId) {
          throw new Error("Por favor, completa todos los campos y selecciona un usuario.")
        }
  
        try {
          const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}/notes`, {
            method: 'POST',
            headers: { 
              'Content-Type': 'application/json',
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              bitacora_id: groupId,
              user_id: this.selectedUserId,
              title: this.newNote.title,
              content: this.newNote.content
            })
          })
  
          if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
          }
  
          const createdNote = await response.json()
          this.notes.push(createdNote.note)
          this.showCreateNoteModal = false
          this.resetNewNote()
          await this.fetchNotes(groupId)
        } catch (error) {
          console.error("Error createNote:", error)
          throw error
        }
    },

    async editNote(groupId, note) {
      try {
        const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}/notes/${note.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            title: note.title,
            content: note.content
          })
        })

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }

        const updatedNote = await response.json()
        const index = this.notes.findIndex(n => n.id === note.id)
        if (index !== -1) {
          this.notes[index] = updatedNote.note
        }

        this.showNoteModal = false
      } catch (error) {
        console.error('Error editNote:', error)
        throw error
      }
    },

    async deleteNote(groupId, noteId) {
      try {
        const response = await fetch(`http://localhost:8000/api/bitacoras/${groupId}/notes/${noteId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json'
          }
        })

        if (response.ok) {
          this.notes = this.notes.filter(note => note.id !== noteId)
        }
      } catch (error) {
        console.error('Error deleteNote:', error)
        throw error
      }
    },

    resetNewNote() {
      this.newNote = {
        title: '',
        content: '',
        id: null
      }
      this.selectedUserId = null
    },

    openEditModal(note) {
      this.newNote = {
        title: note.title,
        content: note.content,
        id: note.id
      }
      this.showNoteModal = true
      this.showCreateNoteModal = false
    },

    toggleEdit() {
      this.isEditing = !this.isEditing
    }
  }
})