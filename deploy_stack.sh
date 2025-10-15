#!/bin/bash
# =====================================================
# Script de despliegue profesional para stack PETBIO
# Mejora: registro de despliegues y verificación de contenedores
# =====================================================

LOG_FILE=~/docker_volumes/deploy_stack.log

echo "==============================" | tee -a $LOG_FILE
echo "$(date '+%Y-%m-%d %H:%M:%S') - Escorpión: Preparando directorios de volúmenes" | tee -a $LOG_FILE
echo "==============================" | tee -a $LOG_FILE

# Crear directorios de volúmenes si no existen
mkdir -p ~/docker_volumes/whatsapp_auth_data
mkdir -p ~/docker_volumes/petbio_storage_docs
mkdir -p ~/docker_volumes/postgres_data

echo "==============================" | tee -a $LOG_FILE
echo "$(date '+%Y-%m-%d %H:%M:%S') - Sagitario: Construyendo imágenes (solo si hay cambios)" | tee -a $LOG_FILE
echo "==============================" | tee -a $LOG_FILE

# Construye imágenes solo si hay cambios
docker compose build | tee -a $LOG_FILE

echo "==============================" | tee -a $LOG_FILE
echo "$(date '+%Y-%m-%d %H:%M:%S') - Capricornio: Deteniendo contenedores antiguos" | tee -a $LOG_FILE
echo "==============================" | tee -a $LOG_FILE

# Detener contenedores existentes sin borrar volúmenes
docker compose stop | tee -a $LOG_FILE

echo "==============================" | tee -a $LOG_FILE
echo "$(date '+%Y-%m-%d %H:%M:%S') - Acuario: Levantando stack en modo detached" | tee -a $LOG_FILE
echo "==============================" | tee -a $LOG_FILE

docker compose up -d | tee -a $LOG_FILE

echo "==============================" | tee -a $LOG_FILE
echo "$(date '+%Y-%m-%d %H:%M:%S') - Piscis: Ajustando permisos de Puppeteer y WhatsApp sessions" | tee -a $LOG_FILE
echo "==============================" | tee -a $LOG_FILE

# Ajustar permisos dentro del contenedor
docker exec -it whatsapp_bot sh -c "chown -R 1000:1000 /usr/src/app/.wwebjs_auth /usr/src/app/sessions /usr/src/app/petbio_storage"

echo "==============================" | tee -a $LOG_FILE
echo "$(date '+%Y-%m-%d %H:%M:%S') - Orión: Verificando estado de todos los contenedores" | tee -a $LOG_FILE
echo "==============================" | tee -a $LOG_FILE

# Mostrar estado de contenedores
docker compose ps | tee -a $LOG_FILE

# Verificar si todos los contenedores están 'Up'
FAILED_CONTAINERS=$(docker compose ps --format '{{.Name}} {{.Status}}' | grep -v "Up")

if [ -z "$FAILED_CONTAINERS" ]; then
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Todos los contenedores levantados correctamente ✅" | tee -a $LOG_FILE
else
    echo "$(date '+%Y-%m-%d %H:%M:%S') - ⚠️ Algunos contenedores no están arriba:" | tee -a $LOG_FILE
    echo "$FAILED_CONTAINERS" | tee -a $LOG_FILE
fi

echo "==============================" | tee -a $LOG_FILE
echo "$(date '+%Y-%m-%d %H:%M:%S') - Stack PETBIO desplegado con éxito" | tee -a $LOG_FILE
echo "------------------------------" | tee -a $LOG_FILE
echo " - WhatsApp Bot: http://localhost:3001" | tee -a $LOG_FILE
echo " - Biometría AI: http://localhost:5000" | tee -a $LOG_FILE
echo " - Integradores PHP/.NET conectados a nginx y red interna" | tee -a $LOG_FILE
echo "==============================" | tee -a $LOG_FILE
