#!/usr/bin/env bash
#
# MIKFAST — one-shot installer
#
# Fresh install (clone + setup):
#   sudo MIKFAST_DIR=/var/www/mikhfast bash scripts/install-mikhfast.sh
#
# From GitHub directly:
#   curl -fsSL https://raw.githubusercontent.com/rivalscott04/mikhfast/master/scripts/install-mikhfast.sh | sudo bash
#
# Update existing install (pull + protect config + permissions):
#   cd /var/www/mikhfast && sudo bash scripts/install-mikhfast.sh --update
#
set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

ok()   { echo -e "${GREEN}[OK]${NC} $*"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $*"; }
fail() { echo -e "${RED}[GAGAL]${NC} $*"; }
info() { echo -e "${CYAN}==>${NC} $*"; }

REPO_URL="${MIKFAST_REPO:-https://github.com/rivalscott04/mikhfast.git}"
BRANCH="${MIKFAST_BRANCH:-master}"
INSTALL_DIR="${MIKFAST_DIR:-/var/www/mikhfast}"
DO_UPDATE=0
SKIP_PULL=0

usage() {
  cat <<EOF
Usage: sudo bash scripts/install-mikhfast.sh [options]

Options:
  --dir PATH       Install path (default: /var/www/mikhfast)
  --repo URL       Git repo URL
  --branch NAME    Git branch (default: master)
  --update         Git pull + re-apply permissions (config tidak ditimpa)
  --skip-pull      Skip git clone/pull (hanya setup config + permission)
  -h, --help       Show help

Environment:
  MIKFAST_DIR, MIKFAST_REPO, MIKFAST_BRANCH, MIKFAST_WEB_USER

After install, buka Admin UI:
  http://SERVER-IP/admin.php?id=login
  Login default: mikhmon / 1234
  Lalu Add Router dari Admin Settings.
EOF
}

while [[ $# -gt 0 ]]; do
  case "$1" in
    --dir) INSTALL_DIR="$2"; shift 2 ;;
    --repo) REPO_URL="$2"; shift 2 ;;
    --branch) BRANCH="$2"; shift 2 ;;
    --update) DO_UPDATE=1; shift ;;
    --skip-pull) SKIP_PULL=1; shift ;;
    -h|--help) usage; exit 0 ;;
    *) fail "Unknown option: $1"; usage; exit 1 ;;
  esac
done

require_root() {
  if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
    fail "Jalankan sebagai root: sudo bash $0"
    exit 1
  fi
}

require_git() {
  if ! command -v git &>/dev/null; then
    fail "git belum terinstall. Jalankan: apt install -y git"
    exit 1
  fi
}

script_app_root() {
  local dir
  dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
  if [[ -f "$dir/admin.php" && -f "$dir/index.php" ]]; then
    echo "$dir"
    return 0
  fi
  return 1
}

clone_or_update_repo() {
  local backup=""
  local quickbt_backup=""
  if [[ -f "$INSTALL_DIR/include/config.php" ]]; then
    backup="$(mktemp)"
    cp "$INSTALL_DIR/include/config.php" "$backup"
    ok "Backup config.php sementara: $backup"
  fi
  if [[ -f "$INSTALL_DIR/include/quickbt.php" ]]; then
    quickbt_backup="$(mktemp)"
    cp "$INSTALL_DIR/include/quickbt.php" "$quickbt_backup"
  fi

  if [[ -d "$INSTALL_DIR/.git" ]]; then
    info "Update repo di $INSTALL_DIR ..."
    # Lepas file live dari git SEBELUM pull — cegah error "would be overwritten by merge"
    local live_files=(include/config.php include/config.php.bak include/quickbt.php)
    for f in "${live_files[@]}"; do
      if git -C "$INSTALL_DIR" ls-files --error-unmatch "$f" &>/dev/null; then
        git -C "$INSTALL_DIR" update-index --skip-worktree "$f" 2>/dev/null || true
        git -C "$INSTALL_DIR" rm --cached -f "$f" >/dev/null 2>&1 || true
        ok "Skip git (pre-pull): $f"
      fi
    done
    git -C "$INSTALL_DIR" fetch origin "$BRANCH"
    git -C "$INSTALL_DIR" checkout "$BRANCH"
    git -C "$INSTALL_DIR" pull origin "$BRANCH" --ff-only || {
      warn "git pull --ff-only gagal — coba: bash scripts/safe-pull.sh"
    }
  elif [[ -d "$INSTALL_DIR" && -n "$(ls -A "$INSTALL_DIR" 2>/dev/null || true)" ]]; then
    fail "Folder $INSTALL_DIR sudah ada tapi bukan git repo."
    echo "       Pindahkan dulu atau pakai --dir path lain."
    exit 1
  else
    info "Clone $REPO_URL → $INSTALL_DIR ..."
    mkdir -p "$(dirname "$INSTALL_DIR")"
    git clone --branch "$BRANCH" --depth 1 "$REPO_URL" "$INSTALL_DIR"
  fi

  if [[ -n "$backup" && -f "$backup" ]]; then
    cp "$backup" "$INSTALL_DIR/include/config.php"
    rm -f "$backup"
    ok "config.php live dipulihkan (tidak ditimpa git pull)"
  fi
  if [[ -n "$quickbt_backup" && -f "$quickbt_backup" ]]; then
    cp "$quickbt_backup" "$INSTALL_DIR/include/quickbt.php"
    rm -f "$quickbt_backup"
    ok "quickbt.php live dipulihkan"
  fi
}

untrack_live_files() {
  local app="$1"
  cd "$app"

  if [[ ! -d .git ]]; then
    warn "Bukan git repo — skip git rm --cached"
    return 0
  fi

  local untracked=0
  for f in include/config.php include/config.php.bak include/quickbt.php; do
    if git ls-files --error-unmatch "$f" &>/dev/null; then
      git rm --cached -f "$f" >/dev/null 2>&1 || true
      untracked=1
      ok "Stop tracking git: $f"
    fi
  done

  if [[ "$untracked" -eq 1 ]]; then
    warn "Jalankan 'git commit' di server opsional — yang penting config tidak ke-overwrite pull."
  else
    ok "File live sudah tidak di-track git"
  fi
}

init_live_config() {
  local app="$1"
  local cfg="$app/include/config.php"
  local example="$app/include/config.php.example"

  if [[ -f "$cfg" && -s "$cfg" ]]; then
    ok "include/config.php sudah ada — tidak ditimpa"
    return 0
  fi

  if [[ -f "$example" ]]; then
    cp "$example" "$cfg"
    ok "include/config.php dibuat dari config.php.example"
  else
    cat > "$cfg" <<'EOF'
<?php 
if(substr($_SERVER["REQUEST_URI"], -10) == "config.php"){header("Location:./");}; 
$data['mikhmon'] = array ('1'=>'mikhmon<|<mikhmon','mikhmon>|>aWNlbA==','qrbt<|<disable');
EOF
    ok "include/config.php dibuat (default)"
  fi
}

print_nginx_hint() {
  local app="$1"
  cat <<EOF

${CYAN}--- Nginx (native PHP-FPM) ---${NC}
Buat site config, contoh:

  root $app;
  index index.php;

  # Jangan expose data tenant / env ke publik
  location ^~ /data/ {
    deny all;
    return 403;
  }

  location ~ \.php$ {
    include snippets/fastcgi-php.conf;
    fastcgi_pass unix:/run/php/php8.3-fpm.sock;
  }
  location / {
    try_files \$uri \$uri/ /index.php?\$query_string;
  }

${CYAN}--- PHP-FPM pool (cron & domain, bukan login super-admin) ---${NC}
  php scripts/superadmin-init.php superadmin password-anda
  env[MIKHMON_BASE_DOMAIN] = mikfast.com
  env[MIKHMON_CRON_TOKEN] = token-cron-rahasia
  env[MIKHMON_INGEST_TOKEN] = token-ingest-rahasia
  env[MIKHMON_NOTIFY_WEBHOOK] = https://hooks.example.com/mikfast
  env[MIKHMON_OFF_ROUTER] = 1

  Jangan taruh .env di document root. Set env di pool systemd / php-fpm.d/*.conf saja.

Reload: nginx -t && systemctl reload nginx && systemctl reload php8.3-fpm

${CYAN}--- Docker ---${NC}
  cd $app && docker compose up -d
  Akses: http://SERVER-IP:8080/admin.php?id=login
EOF
}

print_done() {
  local app="$1"
  echo ""
  echo -e "${GREEN}========================================${NC}"
  echo -e "${GREEN} MIKFAST install selesai${NC}"
  echo -e "${GREEN}========================================${NC}"
  echo ""
  echo "  Path   : $app"
  echo "  Login  : http://SERVER-ANDA/admin.php?id=login"
  echo "  User   : mikhmon"
  echo "  Pass   : 1234"
  echo ""
  echo "  Langkah berikutnya (single-tenant / lokal):"
  echo "    1. Login"
  echo "    2. Admin Settings → Add Router"
  echo "    3. Isi IP, user, password MikroTik → Save"
  echo ""
  echo "  Deploy SaaS multi-tenant (wildcard subdomain):"
  echo "    Baca $app/DEPLOY.md"
  echo ""
  print_nginx_hint "$app"
}

main() {
  require_root
  require_git

  local app=""
  if app="$(script_app_root)"; then
    INSTALL_DIR="$app"
    info "Jalankan dari dalam repo: $INSTALL_DIR"
  fi

  INSTALL_DIR="$(mkdir -p "$INSTALL_DIR" && cd "$INSTALL_DIR" && pwd)"

  echo ""
  echo "MIKFAST Installer"
  echo "  Target : $INSTALL_DIR"
  echo "  Repo   : $REPO_URL ($BRANCH)"
  echo ""

  if [[ "$SKIP_PULL" -eq 0 ]]; then
    if [[ "$DO_UPDATE" -eq 1 ]]; then
      if [[ ! -f "$INSTALL_DIR/scripts/deploy.sh" ]]; then
        fail "deploy.sh tidak ada — git pull dulu atau clone ulang repo"
        exit 1
      fi
      bash "$INSTALL_DIR/scripts/deploy.sh" "$INSTALL_DIR"
      print_done "$INSTALL_DIR"
      exit 0
    elif [[ -d "$INSTALL_DIR/.git" ]]; then
      clone_or_update_repo
    elif [[ ! -f "$INSTALL_DIR/admin.php" ]]; then
      clone_or_update_repo
    else
      warn "Folder sudah berisi app tanpa .git — skip clone (pakai --update atau --skip-pull)"
    fi
  fi

  if [[ ! -f "$INSTALL_DIR/admin.php" ]]; then
    fail "Install gagal — admin.php tidak ditemukan di $INSTALL_DIR"
    exit 1
  fi

  init_live_config "$INSTALL_DIR"
  untrack_live_files "$INSTALL_DIR"

  if command -v bun &>/dev/null || [[ -x "$HOME/.bun/bin/bun" ]] || [[ -x "/root/.bun/bin/bun" ]]; then
    info "Build frontend ..."
    (cd "$INSTALL_DIR" && bun run build) || warn "bun build gagal — skip"
  fi

  info "Setup permission ..."
  bash "$INSTALL_DIR/scripts/setup-permissions.sh" "$INSTALL_DIR"

  if [[ ! -f "$INSTALL_DIR/data/superadmin/credentials.json" ]]; then
    echo ""
    read -r -p "Password super-admin (default user: superadmin, min 4 char): " SA_PASS
    if [[ -n "$SA_PASS" && ${#SA_PASS} -ge 4 ]]; then
      php "$INSTALL_DIR/scripts/superadmin-init.php" superadmin "$SA_PASS" || warn "superadmin-init gagal — jalankan manual: php scripts/superadmin-init.php"
    else
      warn "Skip super-admin init — jalankan nanti: php scripts/superadmin-init.php superadmin password-anda"
    fi
  fi

  info "Cek persistence ..."
  bash "$INSTALL_DIR/scripts/check-persistence.sh" "$INSTALL_DIR" || true

  print_done "$INSTALL_DIR"
}

main "$@"
