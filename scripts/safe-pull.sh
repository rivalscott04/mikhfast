#!/usr/bin/env bash
#
# Git pull tanpa timpa include/config.php (data router live).
#
# Usage:
#   cd /var/www/mikhfast && sudo bash scripts/safe-pull.sh
#
set -euo pipefail

APP="${1:-${MIKFAST_DIR:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}}"
APP="$(cd "$APP" && pwd)"
BRANCH="${MIKFAST_BRANCH:-master}"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'
ok()   { echo -e "${GREEN}[OK]${NC} $*"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $*"; }
fail() { echo -e "${RED}[GAGAL]${NC} $*"; }
info() { echo -e "${CYAN}==>${NC} $*"; }

LIVE_FILES=(include/config.php include/config.php.bak include/quickbt.php)

if [[ ! -d "$APP/.git" ]]; then
  fail "Bukan git repo: $APP"
  exit 1
fi

cfg="$APP/include/config.php"
backup=""
if [[ -f "$cfg" ]]; then
  backup="$(mktemp)"
  cp "$cfg" "$backup"
  ok "Backup config.php → $backup"
fi

info "Lepaskan file live dari git index (data tetap di disk) ..."
for f in "${LIVE_FILES[@]}"; do
  if git -C "$APP" ls-files --error-unmatch "$f" &>/dev/null; then
    git -C "$APP" update-index --skip-worktree "$f" 2>/dev/null || true
    git -C "$APP" rm --cached -f "$f" >/dev/null 2>&1 || true
    ok "Skip git: $f"
  fi
done

info "Git pull ..."
if ! git -C "$APP" pull origin "$BRANCH" --ff-only; then
  warn "ff-only gagal, coba pull biasa ..."
  git -C "$APP" pull origin "$BRANCH"
fi

if [[ -n "$backup" && -f "$backup" ]]; then
  cp "$backup" "$cfg"
  rm -f "$backup"
  ok "config.php live dipulihkan"
fi

for f in "${LIVE_FILES[@]}"; do
  git -C "$APP" update-index --no-skip-worktree "$f" 2>/dev/null || true
  git -C "$APP" rm --cached -f "$f" >/dev/null 2>&1 || true
done

ok "Selesai — config.php tidak lagi di-track git, pull berikutnya aman"
echo ""
echo "Cek: bash scripts/check-persistence.sh $APP"
