# Platform cleanup: drop empty PropositionConfig model classes — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Delete three empty proposition model classes — `PropositionI18nConfig`, `PropositionRulesConfig`, `PropositionWeightCategoriesCollection` — and replace their typed casts in `PropositionConfig` with plain `array` casts. No behavior change.

**Architecture:** The three classes are zero-method placeholders the API hydrates into. The plugins (PrestaShop + WooCommerce) only access `propositionConfig->proposition->key`, never the `internationalization`/`rules`/`weightCategories` fields. Switching to `array` casts preserves JSON hydration without the typed wrappers.

**Tech Stack:** PHP 7.4+, Pest v1, Docker (`docker compose`), PHPStan, ripgrep.

**Source finding:** `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` § Platform B-1.

**Branch:** Execute directly on `chore/v4-capabilities-cleanup-audit`. All cleanup work — plan docs and implementation commits — lives on this single umbrella branch; one PR ships everything together to `v4-capabilities`.

---

## File structure

| File                                                                   | Action | Responsibility                                                                                                                                 |
| ---------------------------------------------------------------------- | ------ | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| `src/Proposition/Model/PropositionConfig.php`                          | Modify | Remove the typed cast references to the 3 classes; switch to `array` (or `null` for attributes with no default); update `@property` docblocks. |
| `src/Proposition/Model/PropositionI18nConfig.php`                      | Delete | Empty class; placeholder for future i18n typing. Plugins never read its fields.                                                                |
| `src/Proposition/Model/PropositionRulesConfig.php`                     | Delete | Empty class; placeholder for proposition rules.                                                                                                |
| `src/Proposition/Collection/PropositionWeightCategoriesCollection.php` | Delete | Empty collection; placeholder for weight-category typing.                                                                                      |

No tests or other `src/` files reference the 3 classes (verified by `rg -l 'PropositionI18nConfig|PropositionRulesConfig|PropositionWeightCategoriesCollection' src/ tests/` returning only the 4 files listed above).

---

## Task 1: Baseline verification — confirm green starting point

**Files:**

- Read: `src/Proposition/Model/PropositionConfig.php` (no edits yet)

- [ ] **Step 1: Verify you are on the dedicated cleanup branch**

Run:

```bash
git branch --show-current
```

Expected: `chore/v4-capabilities-cleanup-audit`. If you're on a different branch, `git checkout chore/v4-capabilities-cleanup-audit` to come back.

- [ ] **Step 2: Run the full test suite via Docker**

Run:

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/baseline-tests.log
echo "exit: $?"
```

Expected: tests pass. Note any pre-existing flaky failures; they will be the baseline for comparison after the change.

- [ ] **Step 3: Run PHPStan via Docker**

Run:

```bash
docker compose run --rm php composer analyse 2>&1 | tee /tmp/baseline-phpstan.log
echo "exit: $?"
```

Expected: zero errors.

- [ ] **Step 4: Confirm the 3 classes have only the expected 4 references**

Run:

```bash
rg -l 'PropositionI18nConfig|PropositionRulesConfig|PropositionWeightCategoriesCollection' src/ tests/
```

Expected (exact 4 files, no more):

```
src/Proposition/Model/PropositionI18nConfig.php
src/Proposition/Model/PropositionConfig.php
src/Proposition/Model/PropositionRulesConfig.php
src/Proposition/Collection/PropositionWeightCategoriesCollection.php
```

If any other file appears, **stop**: the audit baseline has drifted. Re-check the assumption and update the plan before continuing.

- [ ] **Step 5: No commit.** Baseline only.

---

## Task 2: Update `PropositionConfig` to use plain array casts

**Files:**

- Modify: `src/Proposition/Model/PropositionConfig.php`

This task switches the three typed casts to `array`/`null` defaults so hydration still works once the classes are gone. Tests should pass at the end of this task even though the class files still exist.

- [ ] **Step 1: Remove the import for `PropositionWeightCategoriesCollection`**

In `src/Proposition/Model/PropositionConfig.php`, delete this line near the top (currently around line 8):

```php
use MyParcelNL\Pdk\Proposition\Collection\PropositionWeightCategoriesCollection;
```

(`PropositionI18nConfig` and `PropositionRulesConfig` live in the same namespace as `PropositionConfig`, so no `use` statement to remove for those — they're referenced as bare class names.)

- [ ] **Step 2: Update the `@property` docblock**

Replace the three typed property declarations with `array`:

Find:

```php
 * @property array $internationalization
```

(this one is already `array` — leave as-is)

Find:

```php
 * @property PropositionRulesConfig $rules
```

Replace with:

```php
 * @property array $rules
```

Find:

```php
 * @property PropositionWeightCategoriesCollection $weightCategories
```

Replace with:

```php
 * @property array $weightCategories
```

- [ ] **Step 3: Update `$attributes` to drop typed-class references**

In the `$attributes` array, change these three entries:

Find:

```php
        'internationalization' => PropositionI18nConfig::class,
```

Replace with:

```php
        'internationalization' => null,
```

Find:

```php
        // Rules for country- or packageType-specific requirements
        'rules' => PropositionRulesConfig::class,
```

Replace with:

```php
        // Rules for country- or packageType-specific requirements
        'rules' => null,
```

Find:

```php
        'weightCategories' => PropositionWeightCategoriesCollection::class,
```

Replace with:

```php
        'weightCategories' => null,
```

- [ ] **Step 4: Update `$casts` to drop typed-class references**

In the `$casts` array, change these two entries (the `internationalization` cast is already `'array'` — leave as-is):

Find:

```php
        // Rules are arrays of country-specific configurations
        'rules' => PropositionRulesConfig::class,
```

Replace with:

```php
        // Rules are arrays of country-specific configurations
        'rules' => 'array',
```

Find:

```php
        'weightCategories' => PropositionWeightCategoriesCollection::class,
```

Replace with:

```php
        'weightCategories' => 'array',
```

- [ ] **Step 5: Run the full test suite — must still pass**

Run:

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/after-config-update-tests.log
echo "exit: $?"
```

Expected: same pass/fail counts as `/tmp/baseline-tests.log` (no new failures). If any test relating to `PropositionConfig`, `PropositionService`, or proposition JSON hydration regresses, investigate before continuing.

- [ ] **Step 6: Run PHPStan — must still pass**

Run:

```bash
docker compose run --rm php composer analyse 2>&1 | tee /tmp/after-config-update-phpstan.log
echo "exit: $?"
```

Expected: zero errors.

- [ ] **Step 7: No commit yet.** Continue to Task 3.

---

## Task 3: Delete the three empty class files

**Files:**

- Delete: `src/Proposition/Model/PropositionI18nConfig.php`
- Delete: `src/Proposition/Model/PropositionRulesConfig.php`
- Delete: `src/Proposition/Collection/PropositionWeightCategoriesCollection.php`

- [ ] **Step 1: Delete the three files**

Run:

```bash
git rm src/Proposition/Model/PropositionI18nConfig.php \
       src/Proposition/Model/PropositionRulesConfig.php \
       src/Proposition/Collection/PropositionWeightCategoriesCollection.php
```

Expected: `git status` shows 3 deletions staged.

- [ ] **Step 2: Verify no stragglers reference the deleted classes**

Run:

```bash
rg 'PropositionI18nConfig|PropositionRulesConfig|PropositionWeightCategoriesCollection' src/ tests/
```

Expected: **no output** (zero hits). If there are hits, investigate before proceeding — this indicates the references were not all in the 4 files we expected.

- [ ] **Step 3: No commit yet.** Continue to Task 4.

---

## Task 4: Final verification

**Files:**

- No edits.

- [ ] **Step 1: Run the full test suite**

Run:

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/final-tests.log
echo "exit: $?"
```

Expected: same pass/fail counts as `/tmp/baseline-tests.log`. Any regression means a hidden coupling we missed — stop and investigate.

- [ ] **Step 2: Run PHPStan**

Run:

```bash
docker compose run --rm php composer analyse 2>&1 | tee /tmp/final-phpstan.log
echo "exit: $?"
```

Expected: zero errors. PHPStan should not complain about missing classes (we removed all references).

- [ ] **Step 3: Hydration smoke test (sanity check)**

Use Docker to instantiate `PropositionConfig` with a fixture-shaped array and confirm the three previously-typed fields hydrate as plain arrays:

```bash
docker compose run --rm php php -r "
require 'vendor/autoload.php';
\$config = new \MyParcelNL\Pdk\Proposition\Model\PropositionConfig([
    'internationalization' => ['language' => 'nl'],
    'rules' => ['NL' => ['minWeight' => 0]],
    'weightCategories' => [['id' => 1, 'maxWeightInGrams' => 2000]],
]);
echo 'internationalization: ' . gettype(\$config->internationalization) . PHP_EOL;
echo 'rules: ' . gettype(\$config->rules) . PHP_EOL;
echo 'weightCategories: ' . gettype(\$config->weightCategories) . PHP_EOL;
"
```

Expected output:

```
internationalization: array
rules: array
weightCategories: array
```

- [ ] **Step 4: No commit yet.** Continue to Task 5.

---

## Task 5: Commit

**Files:**

- Stage: `src/Proposition/Model/PropositionConfig.php`
- Stage: deletions of the 3 class files (already staged via `git rm` in Task 3).

- [ ] **Step 1: Show the diff to the user for review**

Per project CLAUDE.md (review before commit). Display:

```bash
git diff --staged
git status --short
```

Wait for user approval before continuing.

- [ ] **Step 2: Stage and commit (only after approval)**

Run:

```bash
git add src/Proposition/Model/PropositionConfig.php
git commit -m "$(cat <<'EOF'
chore(proposition): drop empty PropositionConfig model wrappers

Replaces PropositionI18nConfig, PropositionRulesConfig, and
PropositionWeightCategoriesCollection (all zero-method placeholders) with
plain array casts on PropositionConfig. Plugins only consume
propositionConfig->proposition->key, never these fields — no behavior change.

Audit reference: docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
(Platform B-1).

Resolves INT-1504

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 3: Verify the commit**

Run:

```bash
git log -1 --stat
```

Expected: 1 file modified (`PropositionConfig.php`), 3 files deleted (the 3 model classes).

- [ ] **Step 4: Plan complete.** Ready for cross-pattern review or PR creation (deferred until all per-pattern plans land per the audit-branch workflow).

---

## Roll-back instructions (if needed)

```bash
git revert HEAD
```

The change is a single commit; revert restores the 3 classes and their typed casts. No DB state, no plugin coordination needed — pure PHP refactor of internal model declarations.

---

## Why this is safe

- The three classes are zero-method placeholders. Deleting them removes no behavior.
- PDK is consumed only by the PrestaShop and WooCommerce plugins (per memory note: backoffice never uses PDK). `rg` against both plugin paths confirms zero references to these three classes.
- The `internationalization`, `rules`, and `weightCategories` JSON keys remain accessible on `PropositionConfig` as plain arrays. Any future structured-typing work can be reintroduced when concrete fields and consumers exist.
- INT-1568 (capabilities API gaps research) does not interact with this change.
- The `@todo` comment in the old `PropositionI18nConfig` (suggesting future `language`/`supportedLanguages`/`dateFormats` fields) is intentionally NOT preserved — when those fields are actually consumed, a fresh typed model can be added with real properties, rather than continuing to maintain an empty placeholder.
