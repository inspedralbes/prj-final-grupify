# MySQL MCP Agent

This project demonstrates using the mcp-use library to create an agent that can interact with a MySQL database through the MCP protocol, powered by Anthropic's Claude 3.7 Sonnet.

## Setup

1. Install the required dependencies:
   ```bash
   pip install -r requirements.txt
   ```

2. Update the `.env` file with your specific MySQL connection details:
   ```
   # API key for the Anthropic router service
   ROUTER_API_KEY=your_api_key_here
   
   # MySQL connection settings
   MYSQL_HOST=127.0.0.1
   MYSQL_PORT=3306
   MYSQL_USER=your_mysql_user
   MYSQL_PASS=your_mysql_password
   MYSQL_DB=your_database_name
   ```

3. Update the `mysql_mcp.json` file with the same MySQL connection details if needed.

4. Run the agent in terminal CLI:
   ```bash
   python custom_query_direct.py
   ```

## How it works

This agent uses:
- `mcp-use` library to connect to a MySQL database via MCP protocol
- Anthropic Claude 3.7 Sonnet model via the router service
- LangChain for LLM integration

The agent connects to your MySQL database and can answer questions about your database schema, run SQL queries, and analyze data.

## Example queries you can try

- "Show me all tables in the database"
- "Describe the structure of table X"
- "Count the number of records in table Y"
- "Find the top 10 records in table Z sorted by column A"
- "Show me the relationships between tables in the database"
- "Create a summary of what this database is used for based on its schema"

## Using the Agent with Streamlit

In addition to running the agent via the terminal CLI, you can also use it with a Streamlit-based web interface. This allows for a more interactive experience.

1. Ensure all dependencies are installed:
   ```bash
   pip install -r requirements.txt
   ```

2. Run the Streamlit app:
   ```bash
   streamlit run streamlit_chat_app.py
   ```

3. Open the provided URL in your browser to interact with the agent through a user-friendly interface.

## Advanced usage

You can modify the script to run different queries or customize the agent's behavior. The `MCPAgent` class supports various configuration options - check the mcp-use documentation for more details.

## Troubleshooting

- Ensure MySQL is running and accessible from your environment
- Check that the MySQL user has appropriate permissions
- Verify your API key is correctly set in the .env file
- Make sure all dependencies are installed correctly