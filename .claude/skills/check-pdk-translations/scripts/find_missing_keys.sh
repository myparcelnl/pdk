#!/usr/bin/env bash
# Diff the PDK's expected translation keys against a plugin's generated translation files.
#
# Expected keys are determined by introspect_keys.php (run via the PDK's php docker service).
# That script reflects PDK Definition classes and SDK constant classes to derive the keys
# the runtime expects, eliminating brittle text-based parsing of PHP source.
#
# Categories covered:
#   - shipment_options_*       label keys from Definition::getCapabilitiesOptionsKey()
#   - settings_carrier_*       allow/price/export keys from Definition classes,
#                              plus static dividers from CarrierSettingsItemView (label only, `_title`)
#   - settings_product_*       per-option product settings from Definition::getProductSettingsKey()
#   - delivery_type_*          from SDK ShipmentDefsDeliveryOptionsDeliveryNameV2
#   - package_type_*           from SDK RefShipmentPackageTypeV2
#   - carrier_*                from SDK RefCapabilitiesSharedCarrierV2
#
# Description (`_description`) and subtext siblings are intentionally NOT emitted —
# they are optional in this codebase. Missing descriptions silently render nothing
# in the UI, so flagging them here as "missing" produces noise. Translate mode asks
# the user per category whether to draft descriptions.
#
# Usage: find_missing_keys.sh <plugin-path>
#   <plugin-path> must contain config/pdk/translations/{en,nl,fr,de,it}.json

set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <plugin-path>" >&2
  exit 2
fi

PLUGIN_PATH="$1"
TRANSLATIONS_DIR="$PLUGIN_PATH/config/pdk/translations"
LANGS=(en nl fr de it)

for lang in "${LANGS[@]}"; do
  if [[ ! -f "$TRANSLATIONS_DIR/$lang.json" ]]; then
    echo "ERROR: $TRANSLATIONS_DIR/$lang.json not found. Run 'yarn translations:import' in $PLUGIN_PATH first." >&2
    exit 1
  fi
done

# Resolve PDK root from this script's location: <pdk>/.claude/skills/<name>/scripts/this.sh
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PDK_ROOT="$(cd "$SCRIPT_DIR/../../../.." && pwd)"

if [[ ! -f "$PDK_ROOT/docker-compose.yml" ]]; then
  echo "ERROR: $PDK_ROOT/docker-compose.yml not found. Is this script inside the PDK repo?" >&2
  exit 1
fi
if [[ ! -f "$PDK_ROOT/vendor/autoload.php" ]]; then
  echo "ERROR: $PDK_ROOT/vendor/autoload.php missing. Run 'composer install' in the PDK." >&2
  exit 1
fi

KEYS_TMPDIR=$(mktemp -d -t pdk_translations_keys.XXXXXX)
trap 'rm -rf "${KEYS_TMPDIR:-}"' EXIT

for lang in "${LANGS[@]}"; do
  jq -r 'keys[]' "$TRANSLATIONS_DIR/$lang.json" > "$KEYS_TMPDIR/$lang.txt"
done

# True if the key exists in en.json (used for legacy-hint lookup).
key_exists_en() { grep -Fxq "$1" "$KEYS_TMPDIR/en.txt"; }

# Echo a comma-separated list of langs where the key is missing, or empty if present in all.
languages_missing_for() {
  local key="$1" lang
  local missing=""
  for lang in "${LANGS[@]}"; do
    if ! grep -Fxq "$key" "$KEYS_TMPDIR/$lang.txt"; then
      missing+="${missing:+,}$lang"
    fi
  done
  echo "$missing"
}

# Emit a row when the key is missing from any of the 5 languages.
# Format: "<key>\t(missing: <comma-separated-langs>)[\t(legacy: <legacy_key>)]"
emit_if_missing() {
  local key="$1" legacy="${2:-}"
  local missing
  missing=$(languages_missing_for "$key")
  [[ -z "$missing" ]] && return

  local out="$key	(missing: $missing)"
  if [[ -n "$legacy" ]] && key_exists_en "$legacy"; then
    out="$out	(legacy: $legacy)"
  fi
  echo "$out"
}

# Run the PHP introspection inside the PDK's docker php service.
INTROSPECT_JSON="$KEYS_TMPDIR/expected_keys.json"
(
  cd "$PDK_ROOT"
  docker compose run --rm -T php php .claude/skills/check-pdk-translations/scripts/introspect_keys.php
) > "$INTROSPECT_JSON"

if [[ ! -s "$INTROSPECT_JSON" ]]; then
  echo "ERROR: PHP introspection produced no output." >&2
  exit 1
fi

# Print a section heading and emit missing keys for every entry under the given jq path.
# Usage: print_section "== heading ==" '<jq expression yielding {key,legacy?} or string>'
print_section() {
  local heading="$1" jq_expr="$2"
  echo "$heading"
  jq -r "$jq_expr" "$INTROSPECT_JSON" \
    | while IFS=$'\t' read -r key legacy; do
        [[ -z "$key" ]] && continue
        emit_if_missing "$key" "$legacy"
      done
}

# `// ""` keeps an empty legacy field when null, so the bash read still gets two columns.
print_section "== shipment_options ==" \
  '.shipment_options[] | "\(.key)\t\(.legacy // "")"'
echo
print_section "== settings_carrier (per-option) ==" \
  '.settings_carrier[] | "\(.)\t"'
echo
print_section "== settings_product (per-option) ==" \
  '.settings_product[] | "\(.)\t"'
echo
print_section "== settings_carrier (static dividers) ==" \
  '.settings_carrier_dividers[] | "\(.)\t"'
echo
print_section "== delivery_type ==" \
  '.delivery_type[] | "\(.)\t"'
echo
print_section "== package_type ==" \
  '.package_type[] | "\(.)\t"'
echo
print_section "== carrier ==" \
  '.carrier[] | "\(.)\t"'
