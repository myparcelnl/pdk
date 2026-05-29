# v4-capabilities cleanup audit — design

**Date:** 2026-05-11
**Branch under audit:** `v4-capabilities`
**Author:** Freek van Rijt (via brainstorming session)

## 1. Goal & deliverable

The `v4-capabilities` branch replaced large parts of the carrier/validation/schema/platform/calculator
patterns with a single capabilities-driven flow. This audit identifies what is now dead, what can
be simplified, and where the architecture no longer fits — all biased toward making it easier to
understand flows and define new behaviours.

**Goal:** produce one findings document that lists, per pattern area:

- (A) **Dead code** — classes, methods, properties, consts with zero live references
- (B) **Optimization candidates** — single-call-site, near-duplicate, or roundabout code
- (C) **Architecture observations** — places where the current shape still reflects the pre-capabilities model

**North star: net simplification.** Every (B) and (C) recommendation must show
`Removes ≥ Adds` in concept count, OR explicitly justify that a single new helper / layer
collapses two or more existing ones. Findings that fail this guardrail are kept in a
"Noted concerns" bucket for visibility but are not promoted to implementation plans.

**Deliverable:** `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`

**Downstream (out of scope here):** the findings doc drives five per-pattern implementation plans,
each written via a separate `superpowers:writing-plans` invocation.

## 2. Pattern areas

Five audit passes, one per pattern. Each pass has a fixed concept anchor so agents don't drift.

| #   | Pattern        | Concept anchor                                                                               | Representative namespaces / files                                                                                                                                    |
| --- | -------------- | -------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | **Platform**   | "What 'platform' means now that PlatformManager is gone, and where proposition still lives." | `src/Proposition/`, remaining `Platform`-named symbols, settings/account wiring that previously fanned out per platform                                              |
| 2   | **Carrier**    | "How a Carrier is identified, fetched, and what it knows about itself."                      | `src/Carrier/` (Model, Repository, Concern, Service), `CarrierCapabilitiesRepository`, `HasCarrierAttribute`, `Account/Model/Shop` carrier wiring                    |
| 3   | **Schema**     | "How option/feature definitions are declared and consumed."                                  | `src/App/Options/Definition/`, `AbstractOrderOptionDefinition`, `CapabilitiesDefaultHelper`, `ResolvesOptionAttributes` trait, what survives of the old Schema layer |
| 4   | **Validation** | "How an order/shipment/delivery-options payload is checked against carrier capabilities."    | `src/Validation/`, `CarrierSchema`, `CapabilitiesValidationService`, `DeliveryOptionsValidatorInterface`                                                             |
| 5   | **Calculator** | "How orders get their carrier, package type, delivery type, and shipment options resolved."  | `src/App/Order/Calculator/` (now mostly `General/Capabilities*Calculator`), what's left of `AbstractPdkOrderOptionsCalculator` / `OrderCalculatorService`            |

Findings doc orders sections **Platform → Carrier → Schema → Validation → Calculator** (roughly mirrors request flow).

Calculator and Schema have known overlap (definitions feed calculators). Agents record cross-pattern
findings in **both** sections with a back-reference rather than picking one — easier to read per pattern.

## 3. Methodology (per pattern pass)

Each agent runs five steps. Inputs are the pattern's concept anchor and namespaces from Section 2.

### Step 1 — Build the symbol inventory

Scope is the pattern's full namespace, **not** just the branch diff. Pre-existing symbols within
the pattern namespaces are in scope; pre-capabilities holdovers that are now dead are just as
worth deleting as branch-introduced ones.

```
find src/<pattern-namespaces> -name '*.php' -not -path '*/tests/*'
```

For each file: list every `class`, `interface`, `trait`, public method, public property, and
`const`. Constructors and Pest test files don't count.

To keep volume manageable, the agent prioritises added or modified symbols first
(derivable from `git diff --name-status main...HEAD -- src/<namespace>`), then sweeps the
rest of the pattern's namespaces.

### Step 2 — Reference scan

For each symbol, three scans:

| Scope               | Path                                                                           | Counts as                          |
| ------------------- | ------------------------------------------------------------------------------ | ---------------------------------- |
| PDK production      | `src/`                                                                         | "alive"                            |
| PDK tests           | `tests/`                                                                       | "test-only" (not alive on its own) |
| Plugin: prestashop  | `~/projects/docker-prestashop/modules/myparcelnl/` (skip `vendor/`)            | "alive"                            |
| Plugin: woocommerce | `~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/` (skip `vendor/`) | "alive"                            |

Search patterns to run for each class:

- Bare class name and FQCN (catches imports, instanceof, new, typehints)
- FQCN as a string (`'Pdk\\…'`) — catches DI container bindings and config keys
- `config/` directory (PDK and plugins) — DI wiring lives there

For methods/properties: search `->methodName(`, `::methodName(`, `->propertyName`. Static traps
and dynamic calls won't surface — agent flags as a caveat where relevant.

### Step 3 — Bucket each symbol

| Bucket               | Definition                                                           | Reported as                                           |
| -------------------- | -------------------------------------------------------------------- | ----------------------------------------------------- |
| **Dead**             | Zero "alive" hits (PDK src + plugins). Test-only references allowed. | (A) — propose delete                                  |
| **PDK-only**         | Used only within PDK src; no plugin references.                      | Note — candidate for `@internal` (no behavior change) |
| **Single-call-site** | One non-test call site, trivially inlineable.                        | (B) — propose inline                                  |
| **Near-duplicate**   | Same logic appears in 2+ symbols within the pattern.                 | (B) — propose merge                                   |
| **Alive & used**     | Multiple call sites, no duplication.                                 | Skip — not reported                                   |

### Step 4 — Architecture observations (the C deliverable)

After bucketing, the agent answers four questions about its pattern, in 2–4 sentences each:

1. **Single source of truth.** Where does the pattern's data come from today (capabilities API,
   config, hardcoded)? Are there places where the same fact is defined twice?
2. **Flow length.** What's the longest call chain from a public PDK entry point to the answer
   the pattern produces? Any link that just passes through?
3. **Misfits.** Anything in the pattern still shaped for the pre-capabilities model
   (carrier-specific branches, platform fan-out, hardcoded lists)?
4. **Extension cost.** How would a new carrier / new shipment option / new delivery type be
   added today? List the touch points.

### Step 5 — Apply the simplicity guardrail

Every (B) and (C) recommendation must declare:

- **Removes:** N classes / methods / concepts
- **Adds:** N classes / methods / concepts
- **Net:** must be ≤ 0, OR the agent must justify why the new concept replaces ≥ 2 existing
  concepts and improves readability

Recommendations that can't meet the guardrail are downgraded to "Noted concerns" — kept in
the doc for visibility, not promoted to implementation plans.

### Caveats baked into the audit

- `vendor/` inside plugin repos is ignored (it mirrors what we're auditing).
- A symbol referenced **only** by `config/` DI bindings is flagged for human review,
  not auto-classified as dead.
- PHPStan output (`composer analyse`) is used as a cross-check on the dead-code bucket,
  not as the primary source.

## 4. Findings doc structure

One markdown file at `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`.
Sections ordered Platform → Carrier → Schema → Validation → Calculator.

### Top of doc

```
# v4-capabilities cleanup audit — findings

Branch: v4-capabilities (<sha>) vs main
Audit date: 2026-05-11
Plugin cross-check refs: docker-prestashop@<sha>, docker-wordpress@<sha>

## Summary
| Pattern    | Dead (A) | Optimize (B) | Architecture (C) |
|------------|---------:|-------------:|-----------------:|
| Platform   | …        | …            | …                |
| Carrier    | …        | …            | …                |
| Schema     | …        | …            | …                |
| Validation | …        | …            | …                |
| Calculator | …        | …            | …                |

## Caveats
- Symbols referenced only via DI/config strings flagged with ⚠.
- Dynamic method calls (`__call`, variable method names) not detected.
- Test-only references do not keep a symbol alive.
```

### Per-pattern section

```
## <Pattern>

### A. Dead code
| ID | Symbol | Kind | File | Evidence |
|----|--------|------|------|----------|
| A-1 | `Pdk\Foo\BarService` | class | src/Foo/BarService.php | 0 hits in PDK src/plugins; 2 hits in PDK tests |
| A-2 | `BarService::oldMethod()` | method | src/Foo/BarService.php | unreferenced |

### B. Optimization candidates
**B-1**
- **Symbol(s):** `…`
- **Observation:** one sentence — what's duplicated, single-call-site, or roundabout
- **Proposed change:** one sentence — inline / merge / extract
- **Simplicity delta:** Removes N, Adds M, Net: -X
- **Plugin impact:** none / prestashop / woocommerce / both — names the files

### C. Architecture observations
1. Single source of truth — …
2. Flow length — …
3. Misfits — …
4. Extension cost — …

### Cross-pattern references
- See `<Other Pattern> §B` item B-3 (shared symbol `Pdk\X\Y`)
```

Stable IDs (`Calculator A-3`, `Carrier B-2`) let per-pattern plans reference findings without
copy-pasting.

### Bottom of doc

```
## Recommended plan slicing
- **Plan: Platform cleanup** — items: Platform A-1..A-N, B-1, B-2
- **Plan: Carrier cleanup** — items: …
- **Plan: Schema cleanup** — items: …
- **Plan: Validation cleanup** — items: …
- **Plan: Calculator cleanup** — items: …
- **Noted concerns (not promoted to plans)** — items that failed the simplicity guardrail

## Open questions for human review
- …
```

- **Noted concerns** gives a place for "this smells but the fix adds complexity" findings —
  visible without being scheduled.
- **Open questions** captures DI-binding-only symbols and ambiguous cases the agent couldn't
  resolve without human knowledge.

## 5. Execution model

### Pre-flight (main session, once)

1. `git diff --name-status main...HEAD -- src/` → file list per pattern namespace.
2. `composer analyse` (PHPStan) → capture unused-private / unused-method warnings to a temp file.
   Cross-checked later against the dead-code bucket.
3. Record current SHAs of the two plugin repos (provenance for the findings doc).

### Pattern agents (parallel)

Five `Explore` subagents (read-only, no writes), dispatched in a single message.

**Why `Explore`:** the work is search-heavy, doesn't touch code, and its read-window limit
doesn't hurt because each agent runs many narrow searches rather than reading whole large files.

**Why parallel:** the five patterns have minimal symbol overlap; Calculator/Schema overlap is
handled by cross-references in the doc. Sequential would 5× wallclock without quality gain.

### Prompt template (per pattern)

Each agent gets a self-contained prompt with these blocks:

```
ROLE: Audit the <Pattern> pattern on branch v4-capabilities for cleanup opportunities.

CONCEPT ANCHOR: <one sentence from Section 2>

PATTERN NAMESPACES (in scope, full content — not just diff):
<from Section 2>

ADDED/MODIFIED FILES (priority for inventory):
<from pre-flight diff>

METHODOLOGY: <Section 3 inlined verbatim — 5 steps>

SIMPLICITY GUARDRAIL: <Section 1 paragraph inlined>

PLUGIN PATHS (skip vendor/):
- ~/projects/docker-prestashop/modules/myparcelnl/
- ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/

PHPSTAN OUTPUT (cross-check only, not authoritative):
<path to pre-flight phpstan output>

OUTPUT FORMAT: <Section 4 per-pattern template, verbatim>
- Assign stable IDs: <Pattern> A-1, A-2, …, B-1, B-2, …
- Report cross-pattern symbols in both relevant patterns with a `Cross-pattern references` note.

CONSTRAINTS:
- Read-only. Do not edit files.
- Do not propose plans, only findings. Plan slicing is done by the main session.
- A finding that fails the simplicity guardrail goes in "Noted concerns" (main session merges these).
- If you're uncertain about a DI-only or string-referenced symbol, list it in Open Questions rather than guessing.

REPORT BACK: markdown for one `## <Pattern>` section, plus a list of noted concerns and a list of open questions.
```

### Assembly (main session, after agents return)

1. Stitch the five sections in fixed Platform → Carrier → Schema → Validation → Calculator order.
2. Resolve cross-pattern duplicates — keep the more specific finding, replace the other with a back-reference.
3. Merge per-agent "Noted concerns" into the doc-level bucket.
4. Merge per-agent "Open questions" into the doc-level bucket.
5. Cross-check the dead-code bucket against PHPStan pre-flight output:
   - Items PHPStan flagged but agents missed → add.
   - Items agents flagged but PHPStan didn't → keep, note "PHPStan didn't see it" in evidence.
6. Write the **Recommended plan slicing** section — bridge to downstream `writing-plans` calls.
7. Generate the summary-table counts.

### Verification before "done"

Before declaring the findings doc complete:

- Spot-check 3 random "Dead" entries by running the rg commands manually in the main session.
- Spot-check that each "Plan: X cleanup" contains only items from the matching pattern section.
- Confirm every (B)/(C) item has the four required fields (symbol, observation, proposed change, simplicity delta).

If any spot-check fails, re-dispatch that pattern's agent with the specific gap.

## 6. Out of scope

Pre-existing symbols **within the pattern namespaces are in scope** for dead-code and
optimization findings. Symbols outside the five pattern namespaces are out of scope regardless of state.

| Out of scope                                                  | Why / where it goes instead                                                                                                                                 |
| ------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Writing the cleanup PRs                                       | Downstream per-pattern `writing-plans` invocations, not this audit.                                                                                         |
| Editing plugin code (`docker-prestashop`, `docker-wordpress`) | Plugins are read-only references. Plugin updates land separately.                                                                                           |
| Editing the SDK                                               | SDK is generated from OpenAPI; out of bounds for PDK cleanup.                                                                                               |
| Editing `js-pdk` / `delivery-options` Vue widget              | Different repo, different lifecycle. PHP/JS coordination captured as "plugin impact" notes only.                                                            |
| Test rewrites                                                 | Tests are scanned to determine reference liveness; only broken or now-dead tests are flagged. Active test refactoring is its own task.                      |
| Snapshot reconciliation                                       | Snapshot churn from the branch is already merged.                                                                                                           |
| Performance tuning                                            | Outside the cleanup remit unless it also collapses a layer.                                                                                                 |
| Comment/docblock cleanup                                      | Skip unless tied to a deletion.                                                                                                                             |
| Translation key cleanup                                       | Use the existing `check-pdk-translations` skill separately.                                                                                                 |
| Architectural rewrites that need their own design             | Become entries in **Open questions** in the findings doc. They get their own brainstorm → spec → plan cycle, not folded into this audit's downstream plans. |
| Composer/dependency version bumps                             | Outside cleanup remit.                                                                                                                                      |

### Explicit non-goals

- Not trying to get the branch to zero PHPStan warnings — only using PHPStan as one signal for dead code.
- Not producing a "what was changed on this branch" changelog — `git log` already does that.
- Not comparing implementations against the deleted carrier-specific calculators to validate
  behavior parity — that work was done in the PRs that landed the capabilities migration.
