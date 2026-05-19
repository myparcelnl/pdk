# Calculator defensive dispatch — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make `CapabilitiesOptionCalculator::getCapabilityOption()` surface misconfigurations instead of silently returning `null`. When a `Definition::getCapabilitiesKey()` returns a key whose corresponding getter (e.g. `get<UcfirstKey>`) does not exist on the SDK's `RefCapabilitiesResponseOptionsOptionsV2`, log a warning naming the offending key. Behavior otherwise unchanged.

**Architecture:** The method already checks `method_exists` and returns `null` on failure. The change adds a `Logger::warning(...)` call before the `return null;` so the misconfiguration shows up in logs at runtime instead of being swallowed.

**Tech Stack:** PHP 7.4+, Pest v1, Docker (`docker compose`), PHPStan. Uses the existing `Logger` facade (`src/Facade/Logger.php`) — same pattern as `DeliveryOptionsV1Resource::buildShipmentOptions` and other production logging sites.

**Source finding:** `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` § Calculator B-1. User explicitly approved the +1 simplicity-guardrail violation because the defensive check catches a real misconfiguration class (Definition with a typo'd capabilities key) that the reflection currently swallows.

**Branch:** Execute directly on `chore/v4-capabilities-cleanup-audit`. All cleanup work lives on this single umbrella branch; one PR ships everything together to `v4-capabilities`. No dependencies; can run any time.

---

## File structure

| File                                                                                                     | Action           | Responsibility                                                                               |
| -------------------------------------------------------------------------------------------------------- | ---------------- | -------------------------------------------------------------------------------------------- |
| `src/App/Order/Calculator/General/CapabilitiesOptionCalculator.php`                                      | Modify           | Add `Logger::warning(...)` to the `method_exists` failure branch of `getCapabilityOption()`. |
| `tests/Unit/App/Order/Calculator/General/CapabilitiesOptionCalculatorTest.php` (or new file in that dir) | Modify or create | One new test asserting the warning fires for a missing getter.                               |

No plugin impact (internal method).

---

## Task 1: Baseline + locate the method

**Files:** No edits.

- [ ] **Step 1: Verify branch**

```bash
git branch --show-current
```

Expected: `chore/v4-capabilities-cleanup-audit`. If you're on a different branch, `git checkout chore/v4-capabilities-cleanup-audit` to come back.

- [ ] **Step 2: Baseline tests + PHPStan**

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/baseline-tests.log; echo "exit: $?"
docker compose run --rm php composer analyse 2>&1 | tee /tmp/baseline-phpstan.log; echo "exit: $?"
```

- [ ] **Step 3: Confirm the method's current shape**

```bash
sed -n '262,280p' src/App/Order/Calculator/General/CapabilitiesOptionCalculator.php
```

Expected (approximately):

```php
private function getCapabilityOption(RefCapabilitiesResponseOptionsOptionsV2 $options, string $capabilitiesKey)
{
    $getter = 'get' . ucfirst($capabilitiesKey);

    if (! method_exists($options, $getter)) {
        return null;
    }

    return $options->{$getter}();
}
```

Line numbers may have drifted; locate by method name.

- [ ] **Step 4: Confirm `Logger` facade availability**

```bash
rg -n 'Logger::warning' src/ --type=php | head -3
rg -n 'use MyParcelNL\\Pdk\\Facade\\Logger' src/App/Order/Calculator/ --type=php | head -3
```

Confirms the facade exists and is used elsewhere with the same `Logger::warning('message', ['context' => ...])` shape.

- [ ] **Step 5: No commit.**

---

## Task 2: Write the failing test

**Files:**

- Modify or create: `tests/Unit/App/Order/Calculator/General/CapabilitiesOptionCalculatorTest.php`

- [ ] **Step 1: Locate or create the test file**

```bash
ls tests/Unit/App/Order/Calculator/General/CapabilitiesOptionCalculatorTest.php 2>&1
```

If the file exists, append the new test case at the bottom. If not, create with the standard Pest header (matching other tests in `tests/Unit/App/Order/Calculator/General/`).

- [ ] **Step 2: Add the test case**

The test must drive `getCapabilityOption` indirectly (it's `private`) via a public entry point with a Definition whose `getCapabilitiesKey()` returns a string that has no corresponding getter on `RefCapabilitiesResponseOptionsOptionsV2`.

Approach:

- Build a stub `OrderOptionDefinitionInterface` whose `getCapabilitiesKey()` returns a known-invalid key like `'nonExistentCapabilityKey'`.
- Run `CapabilitiesOptionCalculator::calculate()` (or whichever public method routes to `getCapabilityOption` — line 161 / 204 / 236 are the three internal callers).
- Assert that the logger captured a warning containing the invalid key + the Definition class name.

PDK has a test-time logger spy pattern — search for it:

```bash
rg -n 'Logger.*Mock|UsesMockLogger|captureLog' tests/ --type=php | head -5
```

Use whatever spy pattern the codebase already has. Example test sketch (adapt to the actual spy API):

```php
it('logs a warning when a Definition capabilities key has no matching getter on the SDK options', function () {
    // Arrange: a Definition that returns a key without a corresponding get*() method
    $definition = new class implements OrderOptionDefinitionInterface {
        public function getShipmentOptionsKey(): ?string { return 'fakeShipmentKey'; }
        public function getCapabilitiesOptionsKey(): ?string { return 'nonExistentCapabilityKey'; }
        // ... required interface methods stubbed
    };

    // Set up an order with this definition registered and run the calculator
    // (use existing factories — match the pattern in this test file)

    // Act
    $calculator = Pdk::get(CapabilitiesOptionCalculator::class);
    $calculator->setOrder($order);
    $calculator->calculate();

    // Assert: the logger captured a warning naming 'nonExistentCapabilityKey'
    expect($loggerSpy->warnings())->toContain(
        fn(string $msg) => str_contains($msg, 'nonExistentCapabilityKey')
    );
});
```

Adapt to the codebase's actual factory + spy idioms during execution. The point is: the test asserts that a missing getter produces a logged warning.

- [ ] **Step 3: Run the test — it must FAIL**

```bash
docker compose run --rm php composer test -- --filter=CapabilitiesOptionCalculator 2>&1 | tail -30
```

Expected: the new test FAILS because the current implementation returns `null` silently (no log). If it passes, the implementation already has the warning — surface to the user; the plan may be a no-op.

- [ ] **Step 4: No commit yet.**

---

## Task 3: Add the defensive warning

**Files:**

- Modify: `src/App/Order/Calculator/General/CapabilitiesOptionCalculator.php`

- [ ] **Step 1: Add the `Logger` import if not present**

At the top of the file, add (or confirm exists):

```php
use MyParcelNL\Pdk\Facade\Logger;
```

- [ ] **Step 2: Update the method**

Find:

```php
private function getCapabilityOption(RefCapabilitiesResponseOptionsOptionsV2 $options, string $capabilitiesKey)
{
    $getter = 'get' . ucfirst($capabilitiesKey);

    if (! method_exists($options, $getter)) {
        return null;
    }

    return $options->{$getter}();
}
```

Replace with:

```php
private function getCapabilityOption(RefCapabilitiesResponseOptionsOptionsV2 $options, string $capabilitiesKey)
{
    $getter = 'get' . ucfirst($capabilitiesKey);

    if (! method_exists($options, $getter)) {
        Logger::warning(
            sprintf(
                'No getter %s() exists on %s for capabilities key "%s". '
                . 'Check the OptionDefinition whose getCapabilitiesKey() returns "%s" — '
                . 'the key likely needs to match an SDK-emitted property name.',
                $getter,
                RefCapabilitiesResponseOptionsOptionsV2::class,
                $capabilitiesKey,
                $capabilitiesKey
            ),
            [
                'capabilitiesKey' => $capabilitiesKey,
                'expectedGetter'  => $getter,
                'optionsClass'    => RefCapabilitiesResponseOptionsOptionsV2::class,
            ]
        );

        return null;
    }

    return $options->{$getter}();
}
```

The warning message names both the missing getter and the offending key, so a developer searching the codebase for `getCapabilitiesKey()` returning that string can find the misconfigured Definition quickly. The context array gives structured log consumers the same data in machine-readable form.

- [ ] **Step 3: Run the test — it must now PASS**

```bash
docker compose run --rm php composer test -- --filter=CapabilitiesOptionCalculator 2>&1 | tail -30
```

Expected: pass.

- [ ] **Step 4: Run the full test suite + PHPStan**

```bash
docker compose run --rm php composer test 2>&1 | tail -20
docker compose run --rm php composer analyse 2>&1 | tail -20
```

Expected: tests pass; PHPStan zero errors.

- [ ] **Step 5: No commit yet.**

---

## Task 4: Commit

**Files:** Stage the source + test changes.

- [ ] **Step 1: Show the diff for review**

```bash
git diff --staged --stat
git diff --staged
```

Wait for user approval.

- [ ] **Step 2: Commit (only after approval)**

```bash
git add src/App/Order/Calculator/General/CapabilitiesOptionCalculator.php \
        tests/Unit/App/Order/Calculator/General/CapabilitiesOptionCalculatorTest.php
git commit -m "$(cat <<'EOF'
feat(calculator): log warning when a capabilities key has no matching SDK getter

CapabilitiesOptionCalculator::getCapabilityOption() previously returned
null silently when the reflection-based getter on
RefCapabilitiesResponseOptionsOptionsV2 didn't exist. Misconfigured
OptionDefinitions (typos in getCapabilitiesKey(), keys that don't match
the SDK-emitted property names) were invisible.

Now logs a Logger::warning with the offending key + the expected getter
name + the options class. The fallback null-return semantics are unchanged
so existing behavior is preserved; the change is purely diagnostic.

Audit reference:
docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
(Calculator B-1).

Resolves INT-1504

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 3: Verify commit**

```bash
git log -1 --stat
```

Expected: 2 files changed — `CapabilitiesOptionCalculator.php` + the test file.

- [ ] **Step 4: Plan complete.**

---

## Roll-back

```bash
git revert HEAD
```

Single-commit revert. The fallback semantics are unchanged, so reverting only loses the diagnostic logging — no behavior regression.

---

## Why this is safe

- The defensive check sits on the same conditional branch that already returned `null`. No behavior change at the call sites that consume `getCapabilityOption()`'s return.
- The warning fires only in the misconfiguration path, which currently silently disables the option. Without this change, misconfigurations are invisible; with it, they show up in logs without breaking anything.
- The `+1` simplicity-guardrail violation (Removes 0, Adds 1) is explicitly user-approved in the findings doc.
- Logger usage matches the existing pattern (`Logger::warning('message', $context)`).

---

## Open questions

- **Severity level — warning vs notice?** This is a developer-facing diagnostic; `warning` is appropriate (the option was _supposed_ to be available; the misconfiguration hid it). If the project uses `notice` for "interesting but not critical", swap. The proposed `warning` matches what `DeliveryOptionsV1Resource::buildShipmentOptions` does for "unmapped shipment option key" — a structurally similar case.
- **Should the warning also fire when the Definition's `getCapabilitiesKey()` returns null?** Currently the outer callers already check `if (! $capabilitiesKey) { return; }` (e.g. line 158-159). Null is a valid "no capabilities key" state for product-only Definitions and shouldn't trigger a warning. The defensive check only fires when the key IS set but doesn't map.
