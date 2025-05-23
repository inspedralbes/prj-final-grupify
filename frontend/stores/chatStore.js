import { defineStore } from "pinia";

export const useChatStore = defineStore("chat", {
  state: () => ({
    chats: [],
    currentChatId: null,
  }),

  actions: {
    createNewChat() {
      const newChat = {
        id: Date.now(),
        name: "Nou Chat",
        messages: [],
        documents: [],
        createdAt: new Date().toISOString(),
      };
      this.chats.push(newChat);
      this.currentChatId = newChat.id;
      this.saveToLocalStorage();
    },

    deleteChat(chatId) {
      this.chats = this.chats.filter(chat => chat.id !== chatId);
      if (this.currentChatId === chatId) {
        this.currentChatId = this.chats.length > 0 ? this.chats[0].id : null;
      }
      this.saveToLocalStorage();
    },

    renameChat(chatId, newName) {
      const chat = this.chats.find(c => c.id === chatId);
      if (chat) {
        chat.name = newName;
        this.saveToLocalStorage();
      }
    },

    addMessage(chatId, message) {
      const chat = this.chats.find(c => c.id === chatId);
      if (chat) {
        chat.messages.push(message);
        // Update chat name with first user message if it's the first message
        if (message.type === "user" && chat.messages.length === 1) {
          const words = message.content.split(" ").slice(0, 5).join(" ");
          this.renameChat(
            chatId,
            words + (message.content.length > words.length ? "..." : "")
          );
        }
        console.log(message)
        this.saveToLocalStorage();
      }
    },

    addDocumentToChat(chatId, documentId) {
      const chat = this.chats.find(c => c.id === chatId);
      if (chat && !chat.documents.includes(documentId)) {
        chat.documents.push(documentId);
        this.saveToLocalStorage();
      }
    },

    removeDocumentFromChat(chatId, documentId) {
      const chat = this.chats.find(c => c.id === chatId);
      if (chat) {
        chat.documents = chat.documents.filter(id => id !== documentId);
        this.saveToLocalStorage();
      }
    },

    loadFromLocalStorage() {
      const saved = localStorage.getItem("teachingAssistantChats");
      if (saved) {
        const data = JSON.parse(saved);
        this.chats = data.chats.map(chat => ({
          ...chat,
          name: chat.name || "Nou Chat",
          documents: chat.documents || [],
        }));
        this.currentChatId = data.currentChatId;
      } else {
        this.createNewChat();
      }
    },

    saveToLocalStorage() {
      localStorage.setItem(
        "teachingAssistantChats",
        JSON.stringify({
          chats: this.chats,
          currentChatId: this.currentChatId,
        })
      );
    },
  },

  getters: {
    currentChat: state => {
      return state.chats.find(chat => chat.id === state.currentChatId);
    },
  },
});
