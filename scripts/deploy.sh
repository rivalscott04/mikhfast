#!/usr/bin/env bash
#
# Deploy otomatis: safe git pull + bun build + permission.
# Dipanggil GitHub Actions (push master) atau manual di server.
#
#   cd /var/www/mikhfast && sudo bash scripts/deploy.sh
#
set -euo pipefail

APP="${1:-${MIKFAST_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}}"
APP="$(cd "$APP" && pwd)"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'
ok()   { echo -e "${GREEN}[OK]${NC} $*"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $*"; }
fail() { echo -e "${RED}[GAGAL]${NC} $*"; }
info() { echo -e "${CYAN}==>${NC} $*"; }

ensure_bun() {
  if command -v bun &>/dev/null; then
    ok "bun $(bun --version)"
    return 0
  fi
  if [[ -x "$HOME/.bun/bin/bun" ]]; then
    export PATH="$HOME/.bun/bin:$PATH"
    ok "bun $(bun --version)"
    return 0
  fi
  if [[ -x "/root/.bun/bin/bun" ]]; then
    export PATH="/root/.bun/bin:$PATH"
    ok "bun $(bun --version)"
    return 0
  fi
  fail "bun belum terinstall. Install: curl -fsSL https://bun.sh/install | bash"
  exit 1
}

echo ""
echo "MIKFAST deploy"
echo "  Path: $APP"
echo ""

info "1/4 Git pull (config live aman) ..."
bash "$APP/scripts/safe-pull.sh" "$APP"

info "2/4 Build frontend ..."
cd "$APP"
ensure_bun
bun run build

info "3/4 Permission ..."
bash "$APP/scripts/setup-permissions.sh" "$APP"

info "4/4 Cek persistence ..."
bash "$APP/scripts/check-persistence.sh" "$APP" || true

echo ""
ok "Deploy selesai — $(date -Iseconds)"
