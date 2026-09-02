#!/usr/bin/env bash
# ═══════════════════════════════════════════════════════════════════════════════
# setup-branch-protection.sh
#
# Configura la protección de la rama `testing` igual que `main` usando la
# GitHub CLI (gh). Debe ejecutarse UNA SOLA VEZ por un administrador del repo.
#
# Requisitos:
#   - gh CLI instalada: https://cli.github.com/
#   - Sesión activa con permisos de admin: gh auth login
#   - El repo debe tener habilitada la opción "Allow merge commits" o equivalente.
#
# Uso:
#   bash scripts/setup-branch-protection.sh
#
# Variables de entorno opcionales:
#   REPO   → owner/repo (auto-detectado desde git remote si no se especifica)
# ═══════════════════════════════════════════════════════════════════════════════

set -euo pipefail

# ── Colores ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; RESET='\033[0m'

log()    { echo -e "${BLUE}[INFO]${RESET}  $*"; }
ok()     { echo -e "${GREEN}[OK]${RESET}    $*"; }
warn()   { echo -e "${YELLOW}[WARN]${RESET}  $*"; }
error()  { echo -e "${RED}[ERROR]${RESET} $*"; exit 1; }

# ── Verificar dependencias ───────────────────────────────────────────────────
command -v gh  >/dev/null 2>&1 || error "La GitHub CLI (gh) no está instalada. Ver: https://cli.github.com/"
command -v jq  >/dev/null 2>&1 || error "jq no está instalado. Instalá con: sudo apt install jq"

# ── Detectar repo automáticamente ───────────────────────────────────────────
if [ -z "${REPO:-}" ]; then
  REPO=$(gh repo view --json nameWithOwner --jq '.nameWithOwner' 2>/dev/null) \
    || error "No se pudo detectar el repositorio. Ejecutá 'gh auth login' o seteá la variable REPO."
fi

log "Repositorio detectado: $REPO"

# ── Verificar autenticación ──────────────────────────────────────────────────
AUTH_USER=$(gh api user --jq '.login' 2>/dev/null) \
  || error "No estás autenticado. Ejecutá: gh auth login"
log "Autenticado como: $AUTH_USER"

# ── Verificar permisos de admin ──────────────────────────────────────────────
IS_ADMIN=$(gh api "repos/$REPO" --jq '.permissions.admin' 2>/dev/null || echo "false")
if [ "$IS_ADMIN" != "true" ]; then
  warn "No se detectaron permisos de admin. La API puede rechazar la solicitud."
  read -r -p "¿Continuar de todas formas? [s/N] " CONFIRM
  [[ "$CONFIRM" =~ ^[sS]$ ]] || { log "Abortado por el usuario."; exit 0; }
fi

echo ""
echo -e "${CYAN}══════════════════════════════════════════════════════${RESET}"
echo -e "${CYAN}  Configuración de protección de ramas — $REPO${RESET}"
echo -e "${CYAN}══════════════════════════════════════════════════════${RESET}"
echo ""

# ── Función para aplicar protección ─────────────────────────────────────────
protect_branch() {
  local BRANCH="$1"
  log "Aplicando protección en rama: $BRANCH ..."

  # Payload de protección (igual para main y testing)
  # Ajustá required_status_checks.contexts según los checks que tengas activos
  PAYLOAD=$(cat <<JSON
{
  "required_status_checks": {
    "strict": true,
    "contexts": [
      "Verificar nombre de rama",
      "Lint código",
      "Verificar convención de commits",
      "Verificar Assignee en Issue"
    ]
  },
  "enforce_admins": false,
  "required_pull_request_reviews": {
    "dismiss_stale_reviews": true,
    "require_code_owner_reviews": false,
    "required_approving_review_count": 1
  },
  "restrictions": null,
  "allow_force_pushes": false,
  "allow_deletions": false,
  "block_creations": false,
  "required_conversation_resolution": true,
  "lock_branch": false
}
JSON
)

  HTTP_STATUS=$(gh api \
    --method PUT \
    -H "Accept: application/vnd.github+json" \
    "repos/$REPO/branches/$BRANCH/protection" \
    --input - <<< "$PAYLOAD" \
    -s -o /dev/null -w "%{http_code}" 2>/dev/null || echo "000")

  # gh api no devuelve http_code directamente; usar curl como fallback
  TOKEN=$(gh auth token)
  RESPONSE=$(curl -s -o /tmp/bp_response.json -w "%{http_code}" \
    -X PUT \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/vnd.github+json" \
    -H "X-GitHub-Api-Version: 2022-11-28" \
    "https://api.github.com/repos/$REPO/branches/$BRANCH/protection" \
    -d "$PAYLOAD")

  if [ "$RESPONSE" = "200" ]; then
    ok "✅ Rama '$BRANCH' protegida correctamente."
  else
    warn "Respuesta HTTP: $RESPONSE"
    warn "Detalle: $(cat /tmp/bp_response.json | jq -r '.message // "desconocido"' 2>/dev/null)"
    warn "La rama '$BRANCH' puede no haber sido protegida. Verificá en GitHub: Settings → Branches."
  fi
}

# ── Aplicar protección a ambas ramas ────────────────────────────────────────
protect_branch "main"
protect_branch "testing"

echo ""
echo -e "${GREEN}══════════════════════════════════════════════════════${RESET}"
echo -e "${GREEN}  ✅ Protección aplicada. Verificá en GitHub:${RESET}"
echo -e "${GREEN}     https://github.com/$REPO/settings/branches${RESET}"
echo -e "${GREEN}══════════════════════════════════════════════════════${RESET}"
echo ""
echo -e "${YELLOW}Recordatorios:${RESET}"
echo "  • Los status checks listados en el script deben coincidir exactamente"
echo "    con los nombres mostrados en GitHub en cada PR."
echo "  • Si usás \"Require status checks to pass\", asegurate de que esos"
echo "    checks ya hayan corrido al menos una vez en el repo."
echo "  • enforce_admins está en false → los admins pueden hacer bypass."
echo "    Cambialo a true si querés mayor seguridad."
echo ""
