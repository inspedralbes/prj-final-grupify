export const useTypingEffect = (speed = 30) => {
  const content = ref("");
  const isTyping = ref(true);

  const typeMessage = async text => {
    content.value = "";
    for (let i = 0; i < text.length; i++) {
      content.value += text[i];
      await new Promise(resolve => setTimeout(resolve, speed));
    }
    isTyping.value = false;
  };

  return {
    content,
    isTyping,
    typeMessage,
  };
};

export const useScrollToBottom = containerRef => {
  const scrollToBottom = () => {
    setTimeout(() => {
      if (containerRef.value) {
        containerRef.value.scrollTop = containerRef.value.scrollHeight;
      }
    }, 100);
  };

  return { scrollToBottom };
};
