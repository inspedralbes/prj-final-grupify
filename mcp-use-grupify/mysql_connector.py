import mysql.connector
import os
from dotenv import load_dotenv

class MySQLConnector:
    def __init__(self):
        # Try to load from .env file if it exists, but don't fail if it doesn't
        env_path = os.path.join(os.path.dirname(__file__), '.env')
        if os.path.exists(env_path):
            load_dotenv(dotenv_path=env_path)
            
        self.host = os.environ.get("DB_HOST")
        self.port = os.environ.get("DB_PORT", "3306")  # Default MySQL port
        self.user = os.environ.get("DB_USER")
        self.password = os.environ.get("DB_PASSWORD")
        self.database = os.environ.get("DB_DATABASE")
        self.connection = None
        self.cursor = None
        self._connect()

    def _connect(self):
        try:
            self.connection = mysql.connector.connect(
                host=self.host,
                port=self.port,
                user=self.user,
                password=self.password,
                database=self.database
            )
            self.cursor = self.connection.cursor(buffered=True, dictionary=True) # Use buffered dictionary cursor
            print(f"Successfully connected to MySQL: {self.host}:{self.port}/{self.database}")
        except mysql.connector.Error as err:
            print(f"Error connecting to MySQL: {err}")
            self.connection = None
            self.cursor = None
            # Optionally re-raise or handle as appropriate for your application
            raise

    def execute_sql_query(self, sql_query, params=None):
        if not self.connection or not self.connection.is_connected():
            print("No active MySQL connection. Attempting to reconnect...")
            self._connect()
        
        if not self.connection or not self.cursor:
            return "Failed to connect to the database."

        try:
            self.cursor.execute(sql_query, params)
            if sql_query.strip().upper().startswith("SELECT") or sql_query.strip().upper().startswith("SHOW"):
                result = self.cursor.fetchall()
            else:
                self.connection.commit()
                result = f"Query executed successfully. Rows affected: {self.cursor.rowcount}"
            return result
        except mysql.connector.Error as err:
            print(f"Error executing query: {err}")
            # In case of connection errors, try to reconnect once
            if err.errno == mysql.connector.errorcode.CR_SERVER_LOST_EXTENDED or \
               err.errno == mysql.connector.errorcode.CR_SERVER_GONE_ERROR:
                print("Connection lost. Attempting to reconnect...")
                try:
                    self._connect()
                    self.cursor.execute(sql_query, params) # Retry query
                    if sql_query.strip().upper().startswith("SELECT") or sql_query.strip().upper().startswith("SHOW"):
                        result = self.cursor.fetchall()
                    else:
                        self.connection.commit()
                        result = f"Query executed successfully after reconnect. Rows affected: {self.cursor.rowcount}"
                    return result
                except mysql.connector.Error as retry_err:
                    print(f"Error executing query after reconnect: {retry_err}")
                    return f"Error executing query after reconnect: {retry_err}"
            return f"Error executing query: {err}"
        except Exception as e:
            print(f"An unexpected error occurred: {e}")
            return f"An unexpected error occurred: {e}"

    def close(self):
        """Close the database connection"""
        try:
            if self.cursor:
                try:
                    # Try to consume any unread results first
                    if self.connection and self.connection.is_connected() and hasattr(self.cursor, 'with_rows') and self.cursor.with_rows:
                        self.cursor.fetchall()
                except Exception as e_fetchall:
                    print(f"Note: Error consuming unread results before closing cursor: {e_fetchall}")
                self.cursor.close()
                self.cursor = None
            if self.connection and self.connection.is_connected():
                self.connection.close()
                self.connection = None
                print("MySQL connection closed.")
        except Exception as e:
            print(f"Error while closing connection: {e}")

# Singleton instance
_connector_instance = None

def get_connector():
    global _connector_instance
    if _connector_instance is None:
        _connector_instance = MySQLConnector()
    return _connector_instance

def execute_sql(sql_query, params=None):
    connector = get_connector()
    return connector.execute_sql_query(sql_query, params)

def close_connection():
    connector = get_connector()
    connector.close()
    global _connector_instance # Allow instance to be recreated if needed later
    _connector_instance = None

if __name__ == '__main__':
    # Example Usage (for testing this module directly)
    print("Testing MySQL Connector...")
    try:
        # Ensure .env file is in the same directory as this script or adjust path
        # load_dotenv(dotenv_path=os.path.join(os.path.dirname(__file__), '.env'))
        
        # Test connection (implicitly done by get_connector)
        test_connector = get_connector()
        if not test_connector.connection or not test_connector.connection.is_connected():
            print("Failed to connect for testing.")
        else:
            print("\nFetching list of tables...")
            tables = execute_sql("SHOW TABLES;")
            if isinstance(tables, list):
                print("Tables found:")
                for table in tables:
                    print(table)
            else:
                print(f"Result of SHOW TABLES: {tables}")

            # Example: Select from a hypothetical 'users' table
            # print("\nFetching users (limit 2)...")
            # users = execute_sql("SELECT * FROM users LIMIT 2;")
            # if isinstance(users, list):
            #     for user in users:
            #         print(user)
            # else:
            #     print(f"Result of SELECT users: {users}")

    except Exception as e:
        print(f"Error during testing: {e}")
    finally:
        close_connection()
        print("Test finished.")
