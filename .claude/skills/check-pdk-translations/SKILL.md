---
name: check-pdk-translations
description: Check for or translate missing PDK translation keys for shipment options, delivery types, package types, and carriers. Use whenever the user asks about missing translations, untranslated keys, or wants translation suggestions for the PDK — including phrases like "check translations", "find missing translations", "translate keys", "shipment options translations", "carrier translations". Always invoke this skill before doing manual grep-based translation key searches in the PDK.
---

# Check PDK Translations

This skill finds translation keys referenced in the PDK source/SDK that are missing from a plugin's generated translation files, and optionally translates them into all 5 supported languages (en, nl, fr, de, it).

The PDK does not store translation values directly — values only exist in plugin builds (e.g. `docker-prestashop/modules/myparcelnl/config/pdk/translations/{en,nl,fr,de,it}.json`). This skill therefore requires a plugin path to compare against.

## Sheet location

The Google Sheet that drives all translations:
- **Document:** "PDK Vertalingen"
- **Sheet:** "Vertalingen"
- **URL:** https://docs.google.com/spreadsheets/d/1TPE7gwG2GXtX7vlKIaskwMy0Xr4o_ir-lsedWB86xyc/edit?gid=0#gid=0

Whenever you tell the user to paste the TSV into the sheet, include this URL and run `open <url>` (macOS) to launch it in their browser, so they don't have to hunt for the tab.

## When to use

Trigger on any of: "check translations", "find missing translations", "untranslated keys", "translate PDK keys", "missing shipment options/carrier/delivery type translations", or any request to audit or fill in PDK translations.

## Terminology — be explicit about keys vs values

Two distinct things get called "translations" in this codebase:

- **Translation key** (a.k.a. "key") — the identifier like `shipment_options_signature`. Keys are derived deterministically from PDK source, the SDK, or the helper script. There is no guesswork about which keys exist or are needed.
- **Translation value** (a.k.a. "value", "copy", or the language name e.g. "English copy", "Dutch translation") — the actual user-facing string like "Signature" or "Handtekening".

When narrating any decision or observation to the user, always say "key" or "value" (or a more specific synonym) explicitly. Don't write "patterns" or "style" or "newer additions" without clarifying whether you mean key naming or value wording — the user has to pause and re-read to figure out which you mean.

Examples:
- ✅ "Sibling **values** for `settings_carrier_export_*` use a mix of 'Activate X' / 'Enable X' / just 'X'. I'll follow the dominant 'Activate X' pattern for these new entries."
- ✅ "The **key** `shipment_options_oversized_package` is the capability-key form of the legacy **key** `shipment_options_large_format`. I'll reuse the legacy **value** 'Extra large format' verbatim under the new **key**."
- ❌ "settings_carrier_export_* has mixed patterns in legacy ('Activate X' / 'Enable X' / just 'X'). Newer additions (fresh_food, frozen) drop the verb, so I'll do the same." → ambiguous: are we talking about keys or values? Always clarify.

## Modes

- **check** — diagnostic only. Lists missing keys, grouped by category, with the legacy key (if any) whose value can be reused.
- **translate** — same as check, plus interactive translation flow that produces a TSV ready to paste into the sheet. Optionally re-imports translations into plugins/PDK and rebuilds.

## Required input

- **Plugin path** — absolute path to a plugin's translations directory (e.g. `/Users/freek.vanrijt/projects/docker-prestashop/modules/myparcelnl`). If the user did not provide one, ask. Do not invent a default.

## Workflow

### Step 1 — refresh translations (always)

Before either mode, refresh the plugin's translation files so the comparison reflects the current state of the Google Sheet:

```bash
cd <plugin-path> && yarn translations:import
```

If the plugin uses a different command (older repos), check `package.json` for a translation-related script and use that. Don't skip this step — stale local files will produce false positives.

### Step 2 — enumerate expected keys

Use `scripts/find_missing_keys.sh <plugin-path>`. The script reads PDK Definition classes and SDK constants, computes expected keys by category, diffs against the plugin's `en.json`, and prints missing keys grouped per category.

If you need to understand or extend what the script enumerates, read `references/key-patterns.md` — it documents every key pattern and where it comes from.

The output is grouped per category:
```
== shipment_options ==                    (label + _description from Definition classes)
== settings_carrier (per-option) ==        (allow/price/export keys per Definition)
== settings_product (per-option) ==        (per-option product settings)
== settings_carrier (static dividers) ==   (export, delivery_options, etc. grep'd from views)
== delivery_type ==                        (SDK ShipmentDefsDeliveryOptionsDeliveryNameV2)
== package_type ==                         (SDK RefShipmentPackageTypeV2)
== carrier ==                              (SDK RefCapabilitiesSharedCarrierV2)
```

Each missing key is shown on its own line, annotated with which of the 5 languages it is missing from. Renamed keys also include a legacy hint:

- Fully missing: `carrier_brt	(missing: en,nl,fr,de,it)` — needs translation in every language.
- Partial gap: `carrier_trunkrs	(missing: it)` — translated in en/nl/fr/de but not it. Generate only the missing language(s) when filling these.
- Renamed: `shipment_options_oversized_package	(missing: en,nl,fr,de,it)	(legacy: shipment_options_large_format)` — copy from the legacy key into the new key for any language where the new key is missing.

The "legacy" column lets you reuse existing translations when a key was renamed (e.g. PDK option `large_format` → capability key `oversized_package`). When present, the legacy key's value is good copy already approved by the translation owner.

### Step 3 — (check mode) report and stop

Present the grouped missing keys to the user. For each missing key, mention if a legacy key with reusable copy exists. End the turn.

### Step 3 — (translate mode) interactive flow

1. **Confirm scope** — show the user the missing keys grouped by category and ask which to translate. They may want all of them, only one category, or a subset.

2. **Per-category description toggle** — descriptions are optional and not auto-flagged by the diff script. For each category in scope, ask whether `_description` rows should also be drafted. Default: include description if a `_description` legacy key already exists for the option being renamed, otherwise label-only. Ask once per category, not per key, unless the user wants finer control. The same applies to divider `_description` keys — only generate them when the user opts in.

3. **Draft English first** — for each key, draft an English value. Sources to draw on, in order:
   - The legacy key's existing translation (verbatim if the option semantics are identical).
   - The PDK Definition class's docblock and class name.
   - The SDK class's docblock for the corresponding constant or attribute.
   - The capability/option key's name itself, used carefully.

4. **Verify uncertain English** — for any key where you are not confident the English is right (semantics unclear, no legacy reference, ambiguous capability name, marketing phrasing matters), present your draft and ask the user to confirm or supply an alternative. Do this before translating to other languages — translating bad English wastes the user's time. Batch the verifications: list all uncertain drafts at once, not one by one.

5. **Translate the missing languages** — for fully missing keys, draft all 5 languages from English. For partial gaps (e.g. `(missing: it)`), only draft the languages listed in the `(missing: …)` annotation — pull the existing values for the other languages from the plugin's translation JSON files so the TSV row is complete. Reuse existing translations of the same word/phrase where they appear elsewhere in the same JSON file (grep `nl.json` for "Signature" equivalents to match style). Match the tone and length of nearby legacy translations.

6. **Output the TSV** — write to `<pdk-root>/missing_pdk_translations.tsv` (the PDK repo root, not the plugin dir — the TSV is a working artifact for this skill, not part of any plugin). Format: tab-separated, **no header row**, columns in this order: `key, en, nl, fr, de, it`. Sort alphabetically by key. Then:
   - Tell the user the file path.
   - Run `open <pdk-root>/missing_pdk_translations.tsv` so the file opens in the user's default editor for review.
   - Print the sheet URL: https://docs.google.com/spreadsheets/d/1TPE7gwG2GXtX7vlKIaskwMy0Xr4o_ir-lsedWB86xyc/edit?gid=0#gid=0
   - Run `open 'https://docs.google.com/spreadsheets/d/1TPE7gwG2GXtX7vlKIaskwMy0Xr4o_ir-lsedWB86xyc/edit?gid=0#gid=0'` so the sheet opens in their browser.
   - Remind them: "Append to the bottom of the 'Vertalingen' sheet."

7. **Offer post-translation rebuild** — after the user confirms the TSV is in the sheet, ask whether they want to:
   - Re-run `yarn translations:import` in this plugin.
   - Run the same in any sibling plugin/module the user mentions (e.g. WooCommerce).
   - Trigger a JS rebuild (`yarn build:js:dev --skip-nx-cache` or equivalent).
   - Apply equivalent steps for the PDK itself if relevant.

   Only run what the user confirms. Do not blanket-rebuild all plugins.

## Output rules

- **TSV format:** no header, tab-separated, 6 columns (`key`, `en`, `nl`, `fr`, `de`, `it`), alphabetical by key.
- **TSV location:** `<pdk-root>/missing_pdk_translations.tsv` (the PDK repo root, overwrite if exists). The file is `.gitignore`-eligible — it's a working artifact, not source.
- **Auto-open:** always `open` the TSV file and the sheet URL after writing, so the user can review and paste without hunting for either.
- **`_subtext` rows:** skip them. The codebase has no generic `_subtext` translations — fields silently omit subtext when the key is missing, so writing empty rows just clutters the sheet. Only include `_subtext` if the user explicitly asks.

## Useful patterns

- "I want to translate PDK keys but only signature stuff" → check mode first, then translate scoped to keys containing `signature` or its capability variant.
- "Just tell me what's missing" → check mode.
- "Update everything" → translate mode with rebuild step at the end across all plugin paths the user names.
