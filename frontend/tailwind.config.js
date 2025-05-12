/** @type {import('tailwindcss').Config} */
export default {
  content: [],
  theme: {
    extend: {
      colors: {
        primary: "rgb(0, 173, 238)",
        secondary: "#6B7280",
        success: "#10B981",
        warning: "#F59E0B",
        danger: "#EF4444",
        background: "#F3F4F6",
        'editor-bg': "#282c34",
        'dark-bg': "#0b0e14",
        'dark-card': "#1e2230",
        'dark-active': "#2a2e3c",
        'purple-accent': "#a855f7"
      },
    },
  },
  plugins: [
    require('@tailwindcss/typography')
  ],
  darkMode: 'class',
};
