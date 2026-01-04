#!/bin/bash
# Script de Backup Automático Seguro - Turnos-Ya
# Versión: 1.0 - Fecha: 2026-01-04

# Configuración
BACKUP_DIR="/var/backups/turnos_ya"
SOURCE_DIR="/var/www/html/Turnos-Ya-Single"
DB_HOST="localhost"
DB_NAME="turnos_ya"
DB_USER="turnos_user"
DB_PASS="${DB_PASSWORD:-}"  # Debe estar en variable de entorno
LOG_FILE="/var/log/backup_turnos_ya.log"
RETENTION_DAYS=7
ENCRYPTION_KEY="${BACKUP_ENCRYPTION_KEY:-}"  # Debe estar en variable de entorno

# Función de logging
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

# Función de error
error_exit() {
    log "ERROR: $1"
    exit 1
}

# Verificar dependencias
check_dependencies() {
    local deps=("mysqldump" "tar" "gpg" "openssl")
    for dep in "${deps[@]}"; do
        if ! command -v "$dep" &> /dev/null; then
            error_exit "Dependencia faltante: $dep"
        fi
    done
}

# Crear directorio de backup si no existe
create_backup_dir() {
    if [ ! -d "$BACKUP_DIR" ]; then
        mkdir -p "$BACKUP_DIR" || error_exit "No se pudo crear directorio de backup: $BACKUP_DIR"
        chmod 700 "$BACKUP_DIR" || error_exit "No se pudieron establecer permisos del directorio"
    fi
}

# Backup de base de datos
backup_database() {
    local timestamp=$(date +%Y%m%d_%H%M%S)
    local db_backup="$BACKUP_DIR/db_$timestamp.sql"

    log "Iniciando backup de base de datos..."

    if [ -z "$DB_PASS" ]; then
        error_exit "Contraseña de base de datos no configurada"
    fi

    # Crear backup con mysqldump
    mysqldump --single-transaction \
               --routines \
               --triggers \
               --add-drop-database \
               --add-drop-table \
               -h "$DB_HOST" \
               -u "$DB_USER" \
               -p"$DB_PASS" \
               "$DB_NAME" > "$db_backup" 2>> "$LOG_FILE"

    if [ $? -ne 0 ]; then
        error_exit "Error al crear backup de base de datos"
    fi

    # Comprimir
    gzip "$db_backup" || error_exit "Error al comprimir backup de BD"
    db_backup="${db_backup}.gz"

    # Encriptar si hay clave
    if [ -n "$ENCRYPTION_KEY" ]; then
        openssl enc -aes-256-cbc -salt -in "$db_backup" -out "${db_backup}.enc" -k "$ENCRYPTION_KEY" 2>> "$LOG_FILE"
        if [ $? -eq 0 ]; then
            rm "$db_backup"
            db_backup="${db_backup}.enc"
        fi
    fi

    log "Backup de base de datos completado: $(basename "$db_backup")"
    echo "$db_backup"
}

# Backup de archivos
backup_files() {
    local timestamp=$(date +%Y%m%d_%H%M%S)
    local file_backup="$BACKUP_DIR/files_$timestamp.tar.gz"

    log "Iniciando backup de archivos..."

    # Excluir directorios sensibles
    tar --exclude='*.log' \
        --exclude='vendor' \
        --exclude='node_modules' \
        --exclude='.git' \
        --exclude='tmp' \
        --exclude='cache' \
        -czf "$file_backup" \
        -C "$SOURCE_DIR" \
        . 2>> "$LOG_FILE"

    if [ $? -ne 0 ]; then
        error_exit "Error al crear backup de archivos"
    fi

    # Encriptar si hay clave
    if [ -n "$ENCRYPTION_KEY" ]; then
        openssl enc -aes-256-cbc -salt -in "$file_backup" -out "${file_backup}.enc" -k "$ENCRYPTION_KEY" 2>> "$LOG_FILE"
        if [ $? -eq 0 ]; then
            rm "$file_backup"
            file_backup="${file_backup}.enc"
        fi
    fi

    log "Backup de archivos completado: $(basename "$file_backup")"
    echo "$file_backup"
}

# Limpiar backups antiguos
cleanup_old_backups() {
    log "Limpiando backups antiguos (más de $RETENTION_DAYS días)..."

    # Limpiar backups de BD
    find "$BACKUP_DIR" -name "db_*.sql.gz*" -mtime +$RETENTION_DAYS -delete 2>> "$LOG_FILE"

    # Limpiar backups de archivos
    find "$BACKUP_DIR" -name "files_*.tar.gz*" -mtime +$RETENTION_DAYS -delete 2>> "$LOG_FILE"

    log "Limpieza completada"
}

# Verificar integridad de backups
verify_backups() {
    local backup_file="$1"
    local file_type="$2"

    log "Verificando integridad de $file_type..."

    if [ -n "$ENCRYPTION_KEY" ] && [[ "$backup_file" == *.enc ]]; then
        # Para archivos encriptados, solo verificar que existen
        if [ -f "$backup_file" ]; then
            log "Archivo encriptado existe y es accesible"
            return 0
        else
            error_exit "Archivo encriptado no encontrado: $backup_file"
        fi
    else
        # Para archivos no encriptados, verificar que se pueden leer
        if [ -f "$backup_file" ] && [ -r "$backup_file" ]; then
            log "Archivo $file_type es accesible y legible"
            return 0
        else
            error_exit "Archivo $file_type no es accesible: $backup_file"
        fi
    fi
}

# Enviar notificación
send_notification() {
    local subject="$1"
    local message="$2"
    local email="${NOTIFICATION_EMAIL:-admin@turnosya.com}"

    if command -v mail &> /dev/null; then
        echo "$message" | mail -s "$subject" "$email" 2>> "$LOG_FILE"
        log "Notificación enviada a $email"
    else
        log "Mail no disponible, notificando por log: $subject - $message"
    fi
}

# Función principal
main() {
    log "=== Iniciando Backup Automático Turnos-Ya ==="

    # Verificar que no haya otro backup ejecutándose
    local lockfile="$BACKUP_DIR/backup.lock"
    if [ -f "$lockfile" ]; then
        log "Otro backup está ejecutándose. Saliendo."
        exit 1
    fi

    # Crear lock file
    touch "$lockfile"

    # Cleanup function
    cleanup() {
        rm -f "$lockfile"
    }
    trap cleanup EXIT

    # Ejecutar backup
    check_dependencies
    create_backup_dir

    local db_backup=""
    local file_backup=""
    local success=true

    # Backup de base de datos
    if db_backup=$(backup_database 2>&1); then
        verify_backups "$db_backup" "base de datos"
    else
        log "ERROR en backup de base de datos: $db_backup"
        success=false
    fi

    # Backup de archivos
    if file_backup=$(backup_files 2>&1); then
        verify_backups "$file_backup" "archivos"
    else
        log "ERROR en backup de archivos: $file_backup"
        success=false
    fi

    # Limpiar antiguos
    cleanup_old_backups

    # Calcular tamaño total
    local total_size=$(du -sh "$BACKUP_DIR" 2>/dev/null | cut -f1)

    # Resultado final
    if [ "$success" = true ]; then
        local message="Backup completado exitosamente.
Base de datos: $(basename "$db_backup")
Archivos: $(basename "$file_backup")
Tamaño total: $total_size
Directorio: $BACKUP_DIR"
        log "Backup completado exitosamente"
        send_notification "Backup Turnos-Ya - Éxito" "$message"
    else
        local message="Backup completado con errores.
Revisar logs para más detalles.
Directorio: $BACKUP_DIR"
        log "Backup completado con errores"
        send_notification "Backup Turnos-Ya - Errores" "$message"
    fi

    log "=== Backup Finalizado ==="
}

# Ejecutar solo si se llama directamente
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi