#!/usr/bin/env bash
# switch-db.sh — Cambia entre base de datos local (MySQL) y Supabase (PostgreSQL)
#
# Uso:
#   ./switch-db.sh local     → MySQL en Docker
#   ./switch-db.sh supabase  → Supabase (PostgreSQL)
#   ./switch-db.sh status    → Muestra la BD activa actual

set -e

# El script vive en switchBD/ — los archivos .env y docker-compose están en el padre
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ACTIVE_FILE="$SCRIPT_DIR/.env.active"
LOCAL_FILE="$SCRIPT_DIR/.env.local"
SUPABASE_FILE="$SCRIPT_DIR/.env.supabase"

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

show_status() {
    if [ ! -f "$ACTIVE_FILE" ]; then
        echo -e "${YELLOW}⚠ No hay BD activa configurada. Ejecutá: ./switch-db.sh local${NC}"
        return
    fi
    local driver host
    driver=$(grep "^DB_DRIVER=" "$ACTIVE_FILE" | cut -d= -f2)
    host=$(grep "^DB_HOST=" "$ACTIVE_FILE" | cut -d= -f2)
    if [ "$driver" = "mysql" ]; then
        echo -e "${GREEN}${BOLD}● BD activa: LOCAL (MySQL) → $host${NC}"
    else
        echo -e "${CYAN}${BOLD}● BD activa: SUPABASE (PostgreSQL) → $host${NC}"
    fi
}

switch_to() {
    local mode="$1"
    local source_file

    case "$mode" in
        local)
            source_file="$LOCAL_FILE"
            label="LOCAL (MySQL Docker)"
            color="$GREEN"
            ;;
        supabase)
            source_file="$SUPABASE_FILE"
            label="SUPABASE (PostgreSQL)"
            color="$CYAN"
            ;;
        *)
            echo -e "${RED}✗ Modo inválido: '$mode'. Usar: local | supabase${NC}"
            exit 1
            ;;
    esac

    if [ ! -f "$source_file" ]; then
        echo -e "${RED}✗ No se encontró el archivo $source_file${NC}"
        exit 1
    fi

    echo -e "${YELLOW}⇄ Cambiando a ${color}${BOLD}${label}${NC}${YELLOW}...${NC}"

    # Copia el env elegido como el activo
    cp "$source_file" "$ACTIVE_FILE"

    echo -e "${color}${BOLD}✓ .env.active actualizado${NC}"

    # Reinicia solo el contenedor web (no la BD ni mailpit)
    if docker ps --format "{{.Names}}" | grep -q "^classia_web$"; then
        echo -e "${YELLOW}↻ Reiniciando classia_web...${NC}"
        (cd "$SCRIPT_DIR" && docker compose restart web 2>/dev/null) || docker restart classia_web
        echo -e "${GREEN}${BOLD}✓ Listo. BD activa: ${label}${NC}"
    else
        echo -e "${YELLOW}ℹ  Contenedor no está corriendo. Levantá con: docker compose up -d${NC}"
    fi
}

case "${1:-status}" in
    local|supabase) switch_to "$1" ;;
    status) show_status ;;
    *)
        echo ""
        echo -e "${BOLD}Uso: ./switch-db.sh [local|supabase|status]${NC}"
        echo ""
        echo "  local     → Usar MySQL local (Docker)"
        echo "  supabase  → Usar Supabase (PostgreSQL)"
        echo "  status    → Ver BD activa actual"
        echo ""
        show_status
        exit 1
        ;;
esac
