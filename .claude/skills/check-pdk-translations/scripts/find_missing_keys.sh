#!/usr/bin/env bash
# Enumerate expected PDK translation keys and diff them against a plugin's en.json.
#
# Categories covered:
#   - shipment_options_*       (label + _description) from Definition classes
#   - settings_carrier_*       allow/price/export keys from Definition classes,
#                              plus static dividers grep'd from view files
#   - settings_product_*       per-option product settings from Definition classes
#   - delivery_type_*          from SDK ShipmentDefsDeliveryOptionsDeliveryNameV2
#   - package_type_*           from SDK RefShipmentPackageTypeV2
#   - carrier_*                from SDK RefCapabilitiesSharedCarrierV2
#
# Usage: find_missing_keys.sh <plugin-path>
#   <plugin-path> must contain config/pdk/translations/en.json

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
DEFINITIONS_DIR="$PDK_ROOT/src/App/Options/Definition"
SDK_MODELS="$PDK_ROOT/vendor/myparcelnl/sdk/src/Client/Generated/CoreApi/Model"
VIEWS_DIR="$PDK_ROOT/src/Frontend/View"

if [[ ! -d "$DEFINITIONS_DIR" ]]; then
  echo "ERROR: $DEFINITIONS_DIR not found. Is this script inside the PDK repo?" >&2
  exit 1
fi
if [[ ! -d "$SDK_MODELS" ]]; then
  echo "ERROR: SDK models not found at $SDK_MODELS. Run 'composer install'." >&2
  exit 1
fi

KEYS_TMPDIR=$(mktemp -d -t pdk_translations_keys.XXXXXX)
trap 'rm -rf "$KEYS_TMPDIR"' EXIT

for lang in "${LANGS[@]}"; do
  jq -r 'keys[]' "$TRANSLATIONS_DIR/$lang.json" > "$KEYS_TMPDIR/$lang.txt"
done

# True if the key exists in the en.json key set (used for legacy-hint lookup).
key_exists() { grep -Fxq "$1" "$KEYS_TMPDIR/en.txt"; }

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

# Convert camelCase → snake_case (portable: macOS BSD sed has no \L)
to_snake() {
  echo "$1" | sed -E 's/([A-Z])/_\1/g; s/^_//' | tr '[:upper:]' '[:lower:]'
}

# Extract a method's first return value from a Definition file.
# Returns the snake_case literal arg if the method is `attributeMap()['snake']`,
# otherwise the camelCase string literal returned directly.
extract_return() {
  local file="$1" method="$2"
  local cap=""
  cap=$(awk -v m="$method" '$0 ~ "function " m {flag=1} flag && /return/{print; exit}' "$file" \
    | sed -nE "s/.*attributeMap\(\)\['([a-zA-Z_]+)'\].*/\1/p")
  if [[ -z "$cap" ]]; then
    cap=$(awk -v m="$method" '$0 ~ "function " m {flag=1} flag && /return/{print; exit}' "$file" \
      | sed -nE "s/.*return '([a-zA-Z_]+)'.*/\1/p")
  fi
  # Edge: AbstractOrderOptionDefinition's getCarrierSettingsKey builds 'export' . ucfirst($key)
  # — we synthesise the same value below in the shipment-options loop, not here.
  echo "$cap"
}

# Emit a row when the key is missing from any of the 5 languages.
# Format: "<key>\t(missing: <comma-separated-langs>)[\t(legacy: <legacy_key>)]"
# When all 5 are missing the missing-list reads "en,nl,fr,de,it" — that's the same
# signal the previous version surfaced, just explicit. Partial gaps (e.g. "(missing: it)")
# are now visible too.
emit_if_missing() {
  local key="$1" legacy="${2:-}"
  local missing
  missing=$(languages_missing_for "$key")
  [[ -z "$missing" ]] && return

  local out="$key	(missing: $missing)"
  if [[ -n "$legacy" ]] && key_exists "$legacy"; then
    out="$out	(legacy: $legacy)"
  fi
  echo "$out"
}

# ---- shipment_options_* and settings_carrier_*/settings_product_* per-option ----

# Build a cache file with one line per Definition:
#   <basename>|<cap>|<legacy_camel>|<allow>|<price>|<carrier>|<product>
build_definition_cache() {
  local cache="$1"
  : > "$cache"
  local f basename cap legacy_camel allow price carrier product first rest
  for f in "$DEFINITIONS_DIR"/*Definition.php; do
    basename="$(basename "$f")"
    [[ "$basename" == "AbstractOrderOptionDefinition.php" ]] && continue

    cap=$(extract_return "$f" "getCapabilitiesOptionsKey")
    legacy_camel=$(extract_return "$f" "getShipmentOptionsKey")
    allow=$(extract_return "$f" "getAllowSettingsKey")
    price=$(extract_return "$f" "getPriceSettingsKey")
    carrier=$(extract_return "$f" "getCarrierSettingsKey")
    product=$(extract_return "$f" "getProductSettingsKey")

    # Apply abstract default for getCarrierSettingsKey: 'export' . ucfirst($shipmentOptionsKey).
    if [[ -z "$carrier" && -n "$legacy_camel" ]]; then
      first=${legacy_camel:0:1}
      rest=${legacy_camel:1}
      carrier="export$(echo "$first" | tr '[:lower:]' '[:upper:]')${rest}"
    fi

    printf '%s|%s|%s|%s|%s|%s|%s\n' \
      "$basename" "$cap" "$legacy_camel" "$allow" "$price" "$carrier" "$product" >> "$cache"
  done
}

# Description (`_description`) and subtext siblings are intentionally NOT emitted —
# they are optional in this codebase. Missing descriptions silently render nothing
# in the UI, so flagging them here as "missing" produces noise. Translate mode asks
# the user per category whether to draft descriptions.

print_shipment_options_from_cache() {
  echo "== shipment_options =="
  local cap legacy_camel legacy_snake key legacy
  while IFS='|' read -r _ cap legacy_camel _ _ _ _; do
    [[ -z "$cap" ]] && continue
    legacy_snake=$(to_snake "$legacy_camel")
    key="shipment_options_${cap}"
    legacy=""
    [[ -n "$legacy_snake" && "$legacy_snake" != "$cap" ]] && legacy="shipment_options_${legacy_snake}"
    emit_if_missing "$key" "$legacy"
  done < "$1"
}

print_settings_carrier_per_option_from_cache() {
  echo "== settings_carrier (per-option) =="
  local allow price carrier camel snake
  while IFS='|' read -r _ _ _ allow price carrier _; do
    for camel in "$allow" "$price" "$carrier"; do
      [[ -z "$camel" ]] && continue
      snake=$(to_snake "$camel")
      emit_if_missing "settings_carrier_${snake}"
    done
  done < "$1"
}

print_settings_product_per_option_from_cache() {
  echo "== settings_product (per-option) =="
  local product snake
  while IFS='|' read -r _ _ _ _ _ _ product; do
    [[ -z "$product" ]] && continue
    snake=$(to_snake "$product")
    emit_if_missing "settings_product_${snake}"
  done < "$1"
}

# ---- static settings dividers grep'd from views ----
# createGenericLabel('foo') in a view with labelPrefix=$prefix produces
# settings_<prefix>_<foo>. Default prefix in CarrierSettingsItemView is 'carrier'.
# We only include matches we can attribute to a known prefix.

print_static_dividers() {
  echo "== settings_carrier (static dividers) =="
  # CarrierSettingsItemView always uses prefix 'carrier' (CarrierSettings::ID).
  # Dividers are rendered with `_title` (visible header) — not the bare key.
  # `_description` exists for some dividers but is optional, so we don't emit it.
  local view="$VIEWS_DIR/CarrierSettingsItemView.php"
  if [[ -f "$view" ]]; then
    grep -oE "createGenericLabel\(['\"][a-z_]+['\"]\)" "$view" \
      | sed -E "s/createGenericLabel\(['\"]([a-z_]+)['\"]\)/\1/" \
      | sort -u \
      | while read -r divider; do
          emit_if_missing "settings_carrier_${divider}_title"
        done
  fi
}

# ---- delivery_type_* ----

print_delivery_types() {
  echo "== delivery_type =="
  local sdk_file="$SDK_MODELS/ShipmentDefsDeliveryOptionsDeliveryNameV2.php"
  [[ ! -f "$sdk_file" ]] && { echo "(SDK class not found: $sdk_file)"; return; }
  grep -E "public const [A-Z_]+ = '" "$sdk_file" \
    | sed -E "s/.*= '([a-z_]+)'.*/\1/" \
    | while read -r name; do
        emit_if_missing "delivery_type_${name}"
      done
}

# ---- package_type_* ----

print_package_types() {
  echo "== package_type =="
  local sdk_file="$SDK_MODELS/RefShipmentPackageTypeV2.php"
  [[ ! -f "$sdk_file" ]] && { echo "(SDK class not found: $sdk_file)"; return; }
  grep -E "public const [A-Z_]+ = '[A-Z_]+'" "$sdk_file" \
    | sed -E "s/.*= '([A-Z_]+)'.*/\1/" \
    | tr '[:upper:]' '[:lower:]' \
    | while read -r name; do
        emit_if_missing "package_type_${name}"
      done
}

# ---- carrier_* ----

print_carriers() {
  echo "== carrier =="
  local sdk_file="$SDK_MODELS/RefCapabilitiesSharedCarrierV2.php"
  [[ ! -f "$sdk_file" ]] && { echo "(SDK class not found: $sdk_file)"; return; }
  grep -E "public const [A-Z_]+ = '[A-Z_]+'" "$sdk_file" \
    | sed -E "s/.*= '([A-Z_]+)'.*/\1/" \
    | tr '[:upper:]' '[:lower:]' \
    | while read -r name; do
        emit_if_missing "carrier_${name}"
      done
}

DEF_CACHE=$(mktemp -t pdk_translations_definitions.XXXXXX)
trap 'rm -f "$DEF_CACHE"' EXIT
build_definition_cache "$DEF_CACHE"

print_shipment_options_from_cache "$DEF_CACHE"
echo
print_settings_carrier_per_option_from_cache "$DEF_CACHE"
echo
print_settings_product_per_option_from_cache "$DEF_CACHE"
echo
print_static_dividers
echo
print_delivery_types
echo
print_package_types
echo
print_carriers
