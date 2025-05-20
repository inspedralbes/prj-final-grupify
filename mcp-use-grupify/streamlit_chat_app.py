import streamlit as st
import asyncio
from custom_query_direct import run_query # Importar run_query

st.title("Xat amb Base de Dades SQL")

# Inicializar el historial del chat
if "messages" not in st.session_state:
    st.session_state.messages = []

# Contenedor para los botones de sugerencia
st.sidebar.header("Preguntes suggerides")

# Función para manejar clics en botones
def handle_suggestion_click(suggestion):
    # Simular la entrada del usuario
    st.session_state.messages.append({"role": "user", "content": suggestion})
    try:
        with st.spinner('Processant la teva consulta...'):
            response_content = asyncio.run(run_query(suggestion, stream=False))
    except Exception as e:
        response_content = f"Ho sento, ha ocorregut un error al processar la teva consulta: {e}"
        st.error(response_content)
    
    st.session_state.messages.append({"role": "assistant", "content": response_content})
    st.rerun()

# Grupos de sugerencias
with st.sidebar.expander("📊 Informació de Grups", expanded=True):
    if st.button("Quants grups hi ha a la meva classe?"):
        handle_suggestion_click("Mostra'm el nombre total de grups a la meva classe")
    if st.button("Com estan distribuïts els estudiants en els grups?"):
        handle_suggestion_click("Mostra la distribució d'estudiants per grup")

with st.sidebar.expander("👥 Estudiants", expanded=True):
    if st.button("Quants estudiants tinc en total?"):
        handle_suggestion_click("Quin és el nombre total d'estudiants a la meva classe?")
    if st.button("Quins són els estudiants sense grup?"):
        handle_suggestion_click("Llista els estudiants que encara no tenen grup assignat")

with st.sidebar.expander("📈 Rendiment", expanded=True):
    if st.button("Veure progrés dels grups"):
        handle_suggestion_click("Mostra el progrés actual de tots els grups")
    if st.button("Identificar grups que necessiten ajuda"):
        handle_suggestion_click("Quins grups tenen menor rendiment o necessiten més suport?")

# Mostrar mensajes del historial en cada re-ejecución de la app
for message in st.session_state.messages:
    with st.chat_message(message["role"]):
        st.markdown(message["content"])

# Reaccionar a la entrada del usuario
if prompt := st.chat_input("Què vols saber de la base de dades?"):
    # Mostrar mensaje del usuario en el contenedor de mensajes
    st.chat_message("user").markdown(prompt)
    # Añadir mensaje del usuario al historial del chat
    st.session_state.messages.append({"role": "user", "content": prompt})

    try:
        # Mostrar spinner mientras se procesa la consulta
        with st.spinner('Processant la teva consulta...'):
            # Obtener respuesta de la base de datos usando run_query
            response_content = asyncio.run(run_query(prompt, stream=False))
    except Exception as e:
        response_content = f"Ho sento, ha ocorregut un error al processar la teva consulta: {e}"
        st.error(response_content) # Mostrar error en la UI también

    # Mostrar respuesta del asistente en el contenedor de mensajes
    with st.chat_message("assistant"):
        st.markdown(response_content)
    # Añadir respuesta del asistente al historial del chat
    st.session_state.messages.append({"role": "assistant", "content": response_content})