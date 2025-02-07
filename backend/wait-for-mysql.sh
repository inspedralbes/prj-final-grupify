#!/bin/bash
# wait-for-mysql.sh
# Uso: ./wait-for-mysql.sh <HOST> <PORT> <TIMEOUT> <COMANDO> [ARGUMENTOS...]

# Parámetros:
#   HOST:       Host o dirección IP de MySQL (por ejemplo, db)
#   PORT:       Puerto en el que escucha MySQL (generalmente 3306)
#   TIMEOUT:    Tiempo máximo en segundos para esperar (por defecto 30)
#   COMANDO:    Comando a ejecutar una vez que MySQL esté disponible

HOST=$1
PORT=$2
TIMEOUT=${3:-30}

if [ -z "$HOST" ] || [ -z "$PORT" ]; then
  echo "Uso: $0 <HOST> <PORT> <TIMEOUT> <COMANDO> [ARGUMENTOS...]"
  exit 1
fi

echo "Esperando a que MySQL inicie en ${HOST}:${PORT} (timeout ${TIMEOUT}s)..."

COUNTER=0
while ! (echo > /dev/tcp/${HOST}/${PORT}) 2>/dev/null; do
  COUNTER=$((COUNTER+1))
  if [ ${COUNTER} -ge ${TIMEOUT} ]; then
    echo "Tiempo de espera excedido: MySQL no está disponible en ${HOST}:${PORT} después de ${TIMEOUT} segundos."
    exit 1
  fi
  echo "MySQL no disponible aún. Reintentando en 1 segundo..."
  sleep 1
done

echo "MySQL ya está disponible en ${HOST}:${PORT}."
shift 3
exec "$@"
