# Remove `OrderOptionDefinitionInterface::validate()` — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remove the `validate(CarrierSchema): bool` method from `OrderOptionDefinitionInterface` and `AbstractOrderOptionDefinition`. Replace its one production caller (`ExcludeParcelLockersCalculator`) and one test caller (`OrderOptionDefinitionInterfaceTest::it('can validate')`) with direct calls to `CarrierSchema::canHaveShipmentOption($definition)`. Net result: option definitions become pure data declarations; the schema query stays where it belongs (on `CarrierSchema`).

**Architecture:** The interface method `validate()` is a thin pass-through. `AbstractOrderOptionDefinition::validate()` defaults to `$carrierSchema->canHaveShipmentOption($this)`. After Schema cleanup part 1 (its prerequisite), no concrete Definition overrides `validate()` — every caller goes through the default delegation. Replacing the call sites with the direct schema query removes one layer of indirection while preserving exact semantics.

**Tech Stack:** PHP 7.4+, Pest v1, Docker (`docker compose`), PHPStan, ripgrep.

**Source finding:** `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` § Schema architecture observations. Detailed analysis in `docs/superpowers/findings/2026-05-11-validate-method-removal-plan.md`.

**Branch:** Execute directly on `chore/v4-capabilities-cleanup-audit`, **after** Schema cleanup part 1's implementation commits have landed on the same branch. All cleanup work lives on this single umbrella branch; one PR ships everything together to `v4-capabilities`.

**Hard dependency:** Schema cleanup part 1 (`2026-05-11-schema-cleanup-part-1-dead-definitions.md`) must be merged into the audit branch first. That cleanup removes the 6 dead `validate()` overrides; without it, this plan leaves dangling overrides referencing a method that no longer exists on the interface.

---

## File structure

| File                                                                     | Action | Responsibility                                                                                                         |
| ------------------------------------------------------------------------ | ------ | ---------------------------------------------------------------------------------------------------------------------- |
| `src/App/Order/Calculator/General/ExcludeParcelLockersCalculator.php`    | Modify | Replace `$definition->validate($this->carrierSchema)` with `$this->carrierSchema->canHaveShipmentOption($definition)`. |
| `src/App/Options/Definition/AbstractOrderOptionDefinition.php`           | Modify | Remove the `validate()` method (and its docblock + `CarrierSchema` import if unused after removal).                    |
| `src/App/Options/Contract/OrderOptionDefinitionInterface.php`            | Modify | Remove the `validate()` method declaration (and the `CarrierSchema` import if unused after removal).                   |
| `tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php` | Modify | Replace `$instance->validate($carrierSchema)` with `$carrierSchema->canHaveShipmentOption($instance)`.                 |

No other src/ or tests/ files reference the `validate()` method (verified after Schema cleanup part 1 — see Task 1 Step 4). Plugins do not call it (verified).

---

## Task 1: Baseline verification + prerequisite check

**Files:**

- No edits.

- [ ] **Step 1: Verify Schema cleanup part 1 is merged**

Run:

```bash
ls src/App/Options/Definition/CountryOfOriginDefinition.php 2>&1
ls src/App/Options/Definition/FitInMailboxDefinition.php 2>&1
```

Expected: both **not found** ("No such file or directory"). If either file still exists, the Schema cleanup part 1 plan has not landed — **stop** and merge it first.

- [ ] **Step 2: Verify branch**

Run:

```bash
git branch --show-current
```

Expected: `chore/v4-capabilities-cleanup-audit`. If you're on a different branch, `git checkout chore/v4-capabilities-cleanup-audit` to come back.

- [ ] **Step 3: Baseline test run**

Run:

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/baseline-tests.log
echo "exit: $?"
```

Expected: tests pass.

- [ ] **Step 4: Baseline PHPStan**

Run:

```bash
docker compose run --rm php composer analyse 2>&1 | tee /tmp/baseline-phpstan.log
echo "exit: $?"
```

Expected: zero errors.

- [ ] **Step 5: Confirm `validate()` call-site inventory**

Run:

```bash
rg -n '->validate\(' src/ tests/ --type=php
```

Expected (exactly 2 lines):

```
src/App/Order/Calculator/General/ExcludeParcelLockersCalculator.php:42:        if (! $definition->validate($this->carrierSchema)) {
tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php:85:        \PHPUnit\Framework\Assert::assertTrue($instance->validate($carrierSchema), "Definition {$definition} failed validation");
```

(Line numbers may differ slightly.) If more than these 2 lines appear, the dependency landed but the inventory drifted — **stop** and update the plan before continuing.

- [ ] **Step 6: Confirm `validate()` is not declared on any concrete Definition (post-Schema-cleanup-part-1)**

Run:

```bash
rg -n 'function validate\(' src/App/Options/
```

Expected (exactly 2 lines, both on the abstract/interface):

```
src/App/Options/Contract/OrderOptionDefinitionInterface.php:75:    public function validate(CarrierSchema $carrierSchema): bool;
src/App/Options/Definition/AbstractOrderOptionDefinition.php:135:    public function validate(CarrierSchema $carrierSchema): bool
```

If any concrete Definition still overrides `validate()`, **stop** — the dependency is incomplete.

- [ ] **Step 7: Confirm plugins don't call `Definition::validate()`**

Run:

```bash
rg -l '->validate\(' ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null
```

Expected: no output (the plugins don't have any `->validate(` call sites at all). If hits appear, manually check whether they're on a Definition; if so, update this plan to include a plugin-side change before continuing.

- [ ] **Step 8: No commit.** Baseline only.

---

## Task 2: Replace the production call site in `ExcludeParcelLockersCalculator`

**Files:**

- Modify: `src/App/Order/Calculator/General/ExcludeParcelLockersCalculator.php`

- [ ] **Step 1: Read the surrounding context**

Run:

```bash
sed -n '35,55p' src/App/Order/Calculator/General/ExcludeParcelLockersCalculator.php
```

Confirm the `calculate()` method shape matches the snippet in Step 2.

- [ ] **Step 2: Replace the validate() call**

In `src/App/Order/Calculator/General/ExcludeParcelLockersCalculator.php`, find:

```php
    public function calculate(): void
    {
        $definition = new ExcludeParcelLockersDefinition();

        if (! $definition->validate($this->carrierSchema)) {
            $this->order->deliveryOptions->shipmentOptions->excludeParcelLockers = TriStateService::DISABLED;

            return;
        }
```

Replace with:

```php
    public function calculate(): void
    {
        $definition = new ExcludeParcelLockersDefinition();

        if (! $this->carrierSchema->canHaveShipmentOption($definition)) {
            $this->order->deliveryOptions->shipmentOptions->excludeParcelLockers = TriStateService::DISABLED;

            return;
        }
```

The change replaces `$definition->validate($this->carrierSchema)` — which delegated to `$this->carrierSchema->canHaveShipmentOption($definition)` — with the direct call. Same semantics; one fewer indirection.

- [ ] **Step 3: Run the affected test**

Run:

```bash
docker compose run --rm php composer test -- --filter=ExcludeParcelLockers 2>&1 | tail -30
echo "exit: $?"
```

Expected: tests pass. If a snapshot test fails because of unrelated symbol-equality (e.g., assertion string changed), investigate — the behavior should be identical.

- [ ] **Step 4: No commit yet.** Continue.

---

## Task 3: Replace the test call site in `OrderOptionDefinitionInterfaceTest`

**Files:**

- Modify: `tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php`

- [ ] **Step 1: Read the surrounding context**

Run:

```bash
sed -n '65,90p' tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php
```

Confirm the `it('can validate', ...)` test block matches the snippet in Step 2.

- [ ] **Step 2: Replace the validate() call and rename the test**

Find this test (after Schema cleanup part 1 it iterates over the 9 surviving Definitions):

```php
it('can validate', function () use ($definitions) {
    $fakeCarrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    $carrierSchema = Pdk::get(CarrierSchema::class);
    $carrierSchema->setCarrier($fakeCarrier);

    foreach ($definitions as $definition) {
        /** @var \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $instance */
        $instance = new $definition();

        \PHPUnit\Framework\Assert::assertTrue($instance->validate($carrierSchema), "Definition {$definition} failed validation");
    }
});
```

Replace with:

```php
it('is supported by a POSTNL carrier with all capabilities', function () use ($definitions) {
    $fakeCarrier = factory(Carrier::class)
        ->withCarrier('POSTNL')
        ->withAllCapabilities()
        ->make();

    $carrierSchema = Pdk::get(CarrierSchema::class);
    $carrierSchema->setCarrier($fakeCarrier);

    foreach ($definitions as $definition) {
        /** @var \MyParcelNL\Pdk\App\Options\Contract\OrderOptionDefinitionInterface $instance */
        $instance = new $definition();

        \PHPUnit\Framework\Assert::assertTrue(
            $carrierSchema->canHaveShipmentOption($instance),
            "Definition {$definition} is not supported by the carrier schema"
        );
    }
});
```

The test now exercises the schema query that `validate()` was wrapping. The renaming (`can validate` → `is supported by a POSTNL carrier with all capabilities`) makes the test's intent explicit and matches the assertion's actual subject.

- [ ] **Step 3: Run the affected test**

Run:

```bash
docker compose run --rm php composer test -- --filter=OrderOptionDefinitionInterface 2>&1 | tail -30
echo "exit: $?"
```

Expected: tests pass. Both the snapshot test and the renamed-validation test should pass.

- [ ] **Step 4: No commit yet.** Continue.

---

## Task 4: Remove `validate()` from `AbstractOrderOptionDefinition`

**Files:**

- Modify: `src/App/Options/Definition/AbstractOrderOptionDefinition.php`

- [ ] **Step 1: Read the surrounding context**

Run:

```bash
sed -n '125,145p' src/App/Options/Definition/AbstractOrderOptionDefinition.php
```

Confirm the `validate()` method block matches the snippet in Step 2.

- [ ] **Step 2: Delete the `validate()` method (and its docblock)**

In `src/App/Options/Definition/AbstractOrderOptionDefinition.php`, find this block (currently around lines 128-140):

```php
    /**
     * Validate the option against a carrier schema. Default delegates to
     * canHaveShipmentOption; subclasses may override for option-specific rules
     * (e.g. unconditional product-only options always return true).
     */
    public function validate(CarrierSchema $carrierSchema): bool
    {
        return $carrierSchema->canHaveShipmentOption($this);
    }
```

(Exact docblock wording may differ; find the `public function validate(CarrierSchema $carrierSchema): bool` declaration and remove the method including its preceding docblock and any trailing blank line that was paired with it.)

- [ ] **Step 3: Remove the `CarrierSchema` import if no longer used**

After the method is gone, check whether `CarrierSchema` is still referenced anywhere in the file:

```bash
rg 'CarrierSchema' src/App/Options/Definition/AbstractOrderOptionDefinition.php
```

If the only remaining hit is the `use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;` line itself, delete that `use` statement too. Otherwise leave it.

- [ ] **Step 4: Run tests + PHPStan to confirm nothing else needs validate()**

Run:

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/post-abstract-remove-tests.log
echo "exit: $?"
docker compose run --rm php composer analyse 2>&1 | tee /tmp/post-abstract-remove-phpstan.log
echo "exit: $?"
```

Expected: tests pass, PHPStan zero errors. If PHPStan reports "interface OrderOptionDefinitionInterface declares method validate() but AbstractOrderOptionDefinition does not implement it" — this is the expected interim state since we haven't touched the interface yet. PHPStan may complain. Proceed if and only if the only PHPStan errors are about the interface mismatch; fix it in Task 5.

If other errors appear, **stop**: there's a hidden caller we missed.

- [ ] **Step 5: No commit yet.** Continue immediately to Task 5.

---

## Task 5: Remove `validate()` from `OrderOptionDefinitionInterface`

**Files:**

- Modify: `src/App/Options/Contract/OrderOptionDefinitionInterface.php`

- [ ] **Step 1: Read the surrounding context**

Run:

```bash
sed -n '65,80p' src/App/Options/Contract/OrderOptionDefinitionInterface.php
```

Confirm the method declaration block matches the snippet in Step 2.

- [ ] **Step 2: Delete the `validate()` declaration (and its docblock)**

In `src/App/Options/Contract/OrderOptionDefinitionInterface.php`, find this block (currently around lines 70-78):

```php
    /**
     * Validate the option against a carrier schema. Returns true when the
     * option is supported in the current context.
     */
    public function validate(CarrierSchema $carrierSchema): bool;
```

(Exact docblock wording may differ.) Remove the declaration including its preceding docblock.

- [ ] **Step 3: Remove the `CarrierSchema` import if no longer used**

```bash
rg 'CarrierSchema' src/App/Options/Contract/OrderOptionDefinitionInterface.php
```

If only the `use` line remains, delete it.

- [ ] **Step 4: No commit yet.** Continue.

---

## Task 6: Final verification

**Files:**

- No edits.

- [ ] **Step 1: Verify zero remaining `validate()` references**

Run:

```bash
rg -n '->validate\(' src/ tests/ --type=php
```

Expected: **no output**. Both call sites are replaced; the method itself is gone.

Run:

```bash
rg -n 'function validate\(' src/App/Options/
```

Expected: **no output**. The interface and abstract no longer declare `validate()`.

- [ ] **Step 2: Run the full test suite**

Run:

```bash
docker compose run --rm php composer test 2>&1 | tee /tmp/final-tests.log
echo "exit: $?"
```

Expected: tests pass. The test name changed from `'can validate'` to `'is supported by a POSTNL carrier with all capabilities'` — Pest will treat this as a new test (one removed, one added).

- [ ] **Step 3: Run PHPStan**

Run:

```bash
docker compose run --rm php composer analyse 2>&1 | tee /tmp/final-phpstan.log
echo "exit: $?"
```

Expected: zero errors.

- [ ] **Step 4: Plugin scan**

Run:

```bash
rg -l '->validate\(' ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/' --type=php 2>/dev/null
```

Expected: no output (plugins were not calling `->validate(` in the baseline).

- [ ] **Step 5: No commit yet.** Continue to Task 7.

---

## Task 7: Commit

**Files:**

- Stage: 2 src files modified + 1 test file modified = 4 files staged (interface + abstract + calculator + test).

- [ ] **Step 1: Show the diff for review**

Run:

```bash
git diff --stat
git status --short
```

Wait for user approval.

- [ ] **Step 2: Commit (only after approval)**

Run:

```bash
git add src/App/Options/Contract/OrderOptionDefinitionInterface.php \
        src/App/Options/Definition/AbstractOrderOptionDefinition.php \
        src/App/Order/Calculator/General/ExcludeParcelLockersCalculator.php \
        tests/Unit/App/Options/Contract/OrderOptionDefinitionInterfaceTest.php
git commit -m "$(cat <<'EOF'
refactor(options): remove validate() from OrderOptionDefinition contract

The validate() method on OrderOptionDefinitionInterface was a thin
pass-through to CarrierSchema::canHaveShipmentOption(). After Schema
cleanup part 1 removed the dead Definitions that overrode it, no concrete
Definition added behavior on top of the default delegation. Removing the
method drops one indirection without changing semantics.

The one production caller (ExcludeParcelLockersCalculator) and one test
caller (OrderOptionDefinitionInterfaceTest) now call
\$carrierSchema->canHaveShipmentOption(\$definition) directly.

Audit reference: docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
(Schema architecture observations) and
docs/superpowers/findings/2026-05-11-validate-method-removal-plan.md.

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

Expected: 4 files changed; net line count negative (removing method definitions + docblocks).

- [ ] **Step 4: Plan complete.** Ready for cross-pattern review or PR creation.

---

## Roll-back instructions (if needed)

```bash
git revert HEAD
```

Single-commit revert restores the `validate()` method on both the interface and abstract, and restores the call sites in the calculator and test.

---

## Why this is safe

- After Schema cleanup part 1, no concrete `OrderOptionDefinition` overrides `validate()` — all callers go through the default delegation in `AbstractOrderOptionDefinition`. Replacing the default delegation's call site with the direct schema query is mechanically equivalent.
- Only two call sites in the entire codebase (one production, one test). Plugin scan confirms zero plugin callers.
- The interface change is breaking for any external implementer of `OrderOptionDefinitionInterface`. PrestaShop and WooCommerce plugin scans show neither implements the interface directly; they consume PDK's built-in Definitions only. If a downstream user has a custom Definition implementing the interface, they'll need to drop the `validate()` method too — that's the intended outcome (a smaller interface).
- INT-1568 (capabilities API gaps research) does not interact with this change.
- `CarrierSchema` is `@deprecated` and slated for further dissolution in plan #4 (Validation + CarrierSchema dissolution). The `canHaveShipmentOption()` method will move/be renamed in that plan; this plan moves callers onto that method in its current form, accepting that they'll need one more touch when CarrierSchema dissolution lands. That second touch is a `sed`-friendly rename, not a refactor.
