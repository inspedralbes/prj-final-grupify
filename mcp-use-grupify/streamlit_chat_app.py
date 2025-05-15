import streamlit as st
import asyncio
from custom_query_direct import run_query # Importar run_query

st.title("Chat con Base de Datos SQL")

# Inicializar el historial del chat
if "messages" not in st.session_state:
    st.session_state.messages = []

# Mostrar mensajes del historial en cada re-ejecución de la app
for message in st.session_state.messages:
    with st.chat_message(message["role"]):
        st.markdown(message["content"])

# Reaccionar a la entrada del usuario
if prompt := st.chat_input("¿Qué quieres saber de la base de datos?"):
    # Mostrar mensaje del usuario en el contenedor de mensajes
    st.chat_message("user").markdown(prompt)
    # Añadir mensaje del usuario al historial del chat
    st.session_state.messages.append({"role": "user", "content": prompt})

    try:
        # Mostrar spinner mientras se procesa la consulta
        with st.spinner('Procesando tu consulta...'):
            # Obtener respuesta de la base de datos usando run_query
            response_content = asyncio.run(run_query(prompt, stream=False))
    except Exception as e:
        response_content = f"Lo siento, ocurrió un error al procesar tu consulta: {e}"
        st.error(response_content) # Mostrar error en la UI también

    # Mostrar respuesta del asistente en el contenedor de mensajes
    with st.chat_message("assistant"):
        st.markdown(response_content)
    # Añadir respuesta del asistente al historial del chat
    st.session_state.messages.append({"role": "assistant", "content": response_content})