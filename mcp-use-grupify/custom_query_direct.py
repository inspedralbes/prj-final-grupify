#!/usr/bin/env python3
import asyncio
import os
import sys
import json
from dotenv import load_dotenv
from langchain_openai import ChatOpenAI
from langchain.agents import AgentExecutor, create_openai_tools_agent
from langchain_core.prompts import ChatPromptTemplate, MessagesPlaceholder
from langchain_core.tools import Tool
from langchain_core.callbacks.streaming_stdout import StreamingStdOutCallbackHandler
from langchain_core.callbacks.manager import CallbackManager
from mysql_connector import execute_sql, close_connection

async def run_query(query, stream=True):
    # Load environment variables
    load_dotenv()
    
    # Get router API key
    ROUTER_API_KEY = os.getenv("ROUTER_API_KEY")
    if ROUTER_API_KEY is None:
        raise ValueError("ROUTER_API_KEY not found. Please check your .env file.")
    
    # MySQL configuration parameters from env
    mysql_host = os.getenv("MYSQL_HOST", "127.0.0.1")
    mysql_port = os.getenv("MYSQL_PORT", "3306")
    mysql_user = os.getenv("MYSQL_USER", "user")
    mysql_pass = os.getenv("MYSQL_PASS", "user")
    mysql_db = os.getenv("MYSQL_DB", "gestioeduca")
    
    print(f"Connecting to MySQL at {mysql_host}:{mysql_port}, database: {mysql_db}...")
    
    # Define a custom MySQL query tool that uses our direct connector
    def mysql_query(sql):
        """Execute a SQL query against the MySQL database"""
        try:
            result = execute_sql(sql)
            return result
        except Exception as e:
            return f"Error executing query: {str(e)}"
    
    # Create a LangChain tool for MySQL queries
    mysql_tool = Tool(
        name="mysql_query",
        func=mysql_query,
        description="Execute a SQL query against the MySQL database. Input should be a valid SQL query string."
    )
    
    # Initialize the LLM using the router service
    # Configure streaming if enabled
    if stream:
        # Create a callback manager with streaming handler for real-time output
        callback_manager = CallbackManager([StreamingStdOutCallbackHandler()])
        
        llm = ChatOpenAI(
            model="anthropic/claude-3-7-sonnet-latest",
            api_key=ROUTER_API_KEY,
            base_url="https://router.requesty.ai/v1",
            default_headers={"Authorization": f"Bearer {ROUTER_API_KEY}"},
            streaming=True,
            callback_manager=callback_manager
        )
    else:
        # Standard non-streaming setup
        llm = ChatOpenAI(
            model="anthropic/claude-3-7-sonnet-latest",
            api_key=ROUTER_API_KEY,
            base_url="https://router.requesty.ai/v1", 
            default_headers={"Authorization": f"Bearer {ROUTER_API_KEY}"}
        )
    
    # Define a prompt template that includes instructions for the agent
    prompt = ChatPromptTemplate.from_messages([
        ("system", """You are a helpful assistant that can answer questions about a MySQL database.
        You have access to a MySQL database called 'gestioeduca' which contains educational data.
        Use the mysql_query tool to execute SQL queries against the database.
        Always think step by step about what SQL query would best answer the user's question.
        Format your responses in a clear and readable way.
        """),
        MessagesPlaceholder(variable_name="chat_history", optional=True),
        ("human", "{input}"),
        MessagesPlaceholder(variable_name="agent_scratchpad"),
    ])
    
    # Create the agent
    agent = create_openai_tools_agent(llm, [mysql_tool], prompt)
    
    # Create an agent executor
    agent_executor = AgentExecutor(
        agent=agent,
        tools=[mysql_tool],
        verbose=True,
        max_iterations=60
    )
    
    try:
        print("\n" + "="*50)
        print(f"🔍 Running query with streaming: {stream}")
        print("="*50 + "\n")
        
        # Run the provided query
        result = await agent_executor.ainvoke({"input": query})
        
        print("\n" + "="*50)
        print(f"✅ Final Result:")
        print("="*50)
        print(f"{result['output']}")
        print("="*50 + "\n")
        
        return result['output']
    finally:
        # Close the MySQL connection
        close_connection()

if __name__ == "__main__":
    # Parse command line arguments
    import argparse
    parser = argparse.ArgumentParser(description='Run a query with optional streaming.')
    parser.add_argument('query', type=str, help='The query to run')
    parser.add_argument('--no-stream', dest='stream', action='store_false', 
                      help='Disable streaming output (streaming is enabled by default)')
    parser.set_defaults(stream=True)
    
    args = parser.parse_args()
    
    print(f"🚀 Initializing query runner...")
    asyncio.run(run_query(args.query, stream=args.stream))
