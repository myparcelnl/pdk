# v4-capabilities cleanup audit — execution plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Execute the audit defined in `docs/superpowers/specs/2026-05-11-v4-capabilities-cleanup-audit-design.md` and produce a single findings doc at `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`.

**Architecture:** Pre-flight gathers shared inputs (diff, PHPStan, plugin SHAs). Five `Explore` subagents run in parallel, one per pattern (Platform, Carrier, Schema, Validation, Calculator), each producing one markdown section. A **per-finding human Q&A curation gate** runs before any assembly. The main session then assembles, reconciles cross-pattern duplicates, cross-checks against PHPStan, writes plan-slicing/summary, and verifies via spot-checks.

**Branching:** All work for this audit lives on branch `chore/v4-capabilities-cleanup-audit`, branched off `v4-capabilities`. The spec is already committed there. The findings doc commit lands on the same branch. Do **not** commit directly on `v4-capabilities`.

**PR timing:** This plan pushes the branch but does **not** open a PR. The PR is opened later, after all five downstream per-pattern cleanup plans have been written and committed on the same branch. The PR stays in **draft** until cleanup code from those plans lands.

**Human review gates:**

- **Gate A (per-finding curation, Task 6):** After agents return, before any processing. The user reviews each A/B/C item per pattern and decides keep / drop / move-to-noted-concerns / discuss. Mandatory; not skippable.
- **Gate B (whole-doc review, Task 14 Step 2):** After assembly, the user reviews the assembled findings doc as a whole before commit. Light-touch sanity check.

**Tech Stack:** `git`, `rg`, Docker (`docker compose`) for PHP/Composer, the `Agent` tool with `subagent_type=Explore`, `AskUserQuestion`, Markdown.

**Spec reference:** All methodology, bucketing rules, simplicity guardrail, and output formats are defined in the spec. This plan does not duplicate them — each task names the spec section the subagent or operator must read.

---

## File structure

| File                                                                       | Role                                                                                             | Lifecycle                   |
| -------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ | --------------------------- |
| `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` | The deliverable                                                                                  | Committed at the end        |
| `tmp/audit-2026-05-11/preflight.md`                                        | Shared pre-flight context (branch SHA, plugin SHAs, diff lists per pattern, PHPStan output path) | Working file; NOT committed |
| `tmp/audit-2026-05-11/phpstan.txt`                                         | Raw `composer analyse` output                                                                    | Working file; NOT committed |
| `tmp/audit-2026-05-11/agent-<pattern>.md`                                  | Each pattern agent's returned markdown                                                           | Working file; NOT committed |
| `tmp/audit-2026-05-11/curated-<pattern>.md`                                | Each pattern's findings after Gate A curation                                                    | Working file; NOT committed |

`tmp/` should already be in `.gitignore`. If not, the operator adds it before the run.

---

## Task 1: Set up working directory and verify `tmp/` is ignored

**Files:**

- Create: `tmp/audit-2026-05-11/` (directory)
- Verify: `.gitignore` contains a rule covering `tmp/`

- [ ] **Step 1: Create the working directory**

Run:

```bash
mkdir -p tmp/audit-2026-05-11
```

- [ ] **Step 2: Verify `tmp/` is gitignored**

Run:

```bash
git check-ignore tmp/audit-2026-05-11/ && echo "ignored OK" || echo "NOT IGNORED — add tmp/ to .gitignore before continuing"
```

Expected: `ignored OK`. If not, add `tmp/` to `.gitignore` and commit before continuing.

- [ ] **Step 3: No commit** — this task produces no committed artifact.

---

## Task 2: Pre-flight — record branch and plugin SHAs

**Files:**

- Create: `tmp/audit-2026-05-11/preflight.md`

- [ ] **Step 1: Capture PDK branch SHA**

Run:

```bash
git rev-parse HEAD
```

Record the output as the PDK SHA.

- [ ] **Step 2: Capture plugin SHAs**

Run:

```bash
git -C ~/projects/docker-prestashop rev-parse HEAD
git -C ~/projects/docker-wordpress rev-parse HEAD
```

Record both SHAs.

- [ ] **Step 3: Write `tmp/audit-2026-05-11/preflight.md`**

Create the file with this content (substitute actual SHAs):

```markdown
# Pre-flight context — audit 2026-05-11

PDK branch: chore/v4-capabilities-cleanup-audit @ <pdk-sha>
docker-prestashop @ <ps-sha>
docker-wordpress @ <wp-sha>

## Pattern namespaces

(filled in by Task 3)

## PHPStan output

(filled in by Task 4)
```

- [ ] **Step 4: No commit.**

---

## Task 3: Pre-flight — list changed files per pattern namespace

**Files:**

- Modify: `tmp/audit-2026-05-11/preflight.md` (append "Pattern namespaces" section)

Pattern → namespace mapping is in the spec, **Section 2**. Use exactly those paths. The diff baseline is `main`, not `v4-capabilities` — we want to surface everything the capabilities work changed, including pre-existing pattern symbols.

- [ ] **Step 1: Capture the full diff file list**

Run:

```bash
git diff --name-status main...HEAD -- src/ > tmp/audit-2026-05-11/diff-all.txt
wc -l tmp/audit-2026-05-11/diff-all.txt
```

- [ ] **Step 2: Slice the diff into the five pattern buckets**

Run (one block; produces five files):

```bash
cd tmp/audit-2026-05-11
grep -E '^[AM].*src/(Proposition|Platform|Account/.*Platform)' diff-all.txt > diff-platform.txt
grep -E '^[AM].*src/(Carrier|Account/Model/Shop|Account/Repository|Account/Service|Account/Contract)' diff-all.txt > diff-carrier.txt
grep -E '^[AM].*src/(App/Options|Base/Concern/ResolvesOptionAttributes|Base/Model/SdkBackedModel|Base/Support/SdkModelHelper)' diff-all.txt > diff-schema.txt
grep -E '^[AM].*src/(Validation|Carrier/Service/CapabilitiesValidationService)' diff-all.txt > diff-validation.txt
grep -E '^[AM].*src/App/Order/Calculator' diff-all.txt > diff-calculator.txt
wc -l diff-*.txt
cd -
```

These regexes are a starting point — the agent will sweep the full namespace anyway (per spec Section 3 Step 1), so completeness here is about _prioritisation_, not coverage.

- [ ] **Step 3: Append the namespace + diff file list to `preflight.md`**

Append a section listing, per pattern, the namespace globs from spec Section 2 and the path to its `diff-<pattern>.txt` file.

- [ ] **Step 4: No commit.**

---

## Task 4: Pre-flight — run PHPStan and capture output

**Files:**

- Create: `tmp/audit-2026-05-11/phpstan.txt`
- Modify: `tmp/audit-2026-05-11/preflight.md` (append PHPStan path)

PHPStan must run through Docker (per project CLAUDE.md). Output is used as a cross-check on the dead-code bucket — not authoritative.

- [ ] **Step 1: Run `composer analyse` via Docker, capture output**

Run:

```bash
docker compose run --rm php composer analyse 2>&1 | tee tmp/audit-2026-05-11/phpstan.txt
echo "phpstan exit: $?"
```

The exit code may be non-zero (warnings present). That is expected — we want the output, not a clean run.

- [ ] **Step 2: Filter for unused-method / unused-private warnings**

Run:

```bash
grep -E -i 'unused|never read|never used|dead' tmp/audit-2026-05-11/phpstan.txt > tmp/audit-2026-05-11/phpstan-unused.txt
wc -l tmp/audit-2026-05-11/phpstan-unused.txt
```

- [ ] **Step 3: Append PHPStan paths to `preflight.md`**

Append:

```
## PHPStan output

Full: tmp/audit-2026-05-11/phpstan.txt
Unused filter: tmp/audit-2026-05-11/phpstan-unused.txt
```

- [ ] **Step 4: No commit.**

---

## Task 5: Dispatch five pattern agents in parallel

**Files:**

- Create: `tmp/audit-2026-05-11/agent-platform.md`
- Create: `tmp/audit-2026-05-11/agent-carrier.md`
- Create: `tmp/audit-2026-05-11/agent-schema.md`
- Create: `tmp/audit-2026-05-11/agent-validation.md`
- Create: `tmp/audit-2026-05-11/agent-calculator.md`

All five agents are dispatched in a **single message** with five `Agent` tool calls so they run concurrently. `subagent_type` is `Explore` for each.

- [ ] **Step 1: Build the prompt for each agent**

Use the prompt template in spec **Section 5**. For each pattern, fill in:

- `CONCEPT ANCHOR` — from spec Section 2 row
- `PATTERN NAMESPACES` — from spec Section 2 row (full namespace paths)
- `ADDED/MODIFIED FILES` — paste the contents of `tmp/audit-2026-05-11/diff-<pattern>.txt`
- `METHODOLOGY` — inline the full text of spec Section 3 (Steps 1–5 plus the "Caveats baked into the audit" bullets)
- `SIMPLICITY GUARDRAIL` — inline the paragraph from spec Section 1 ("North star: net simplification …")
- `PLUGIN PATHS` — `~/projects/docker-prestashop/modules/myparcelnl/` and `~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/`
- `PHPSTAN OUTPUT` — `tmp/audit-2026-05-11/phpstan-unused.txt`
- `OUTPUT FORMAT` — inline the "Per-pattern section" template from spec Section 4
- Stable ID prefix: `<Pattern>` (e.g. `Carrier A-1`, `Platform B-2`)
- Constraints block: copy verbatim from spec Section 5 prompt template

Each prompt must end with:

````
REPORT BACK in three labelled fenced blocks:

```findings
<the ## <Pattern> markdown section here>
````

```noted-concerns
<bullet list, or empty>
```

```open-questions
<bullet list, or empty>
```

````

- [ ] **Step 2: Dispatch all five agents in one message**

In one assistant message, emit five `Agent` tool calls in parallel:

| Agent | description                | subagent_type | prompt              |
| ----- | -------------------------- | ------------- | ------------------- |
| 1     | `Audit Platform pattern`   | `Explore`     | Platform prompt     |
| 2     | `Audit Carrier pattern`    | `Explore`     | Carrier prompt      |
| 3     | `Audit Schema pattern`     | `Explore`     | Schema prompt       |
| 4     | `Audit Validation pattern` | `Explore`     | Validation prompt   |
| 5     | `Audit Calculator pattern` | `Explore`     | Calculator prompt   |

- [ ] **Step 3: Write each agent's returned markdown to disk**

For each returned agent result, write the full report to `tmp/audit-2026-05-11/agent-<pattern>.md`. Preserve the three fenced blocks (`findings`, `noted-concerns`, `open-questions`) verbatim.

- [ ] **Step 4: Verify all five files exist and are non-empty**

Run:
```bash
ls -la tmp/audit-2026-05-11/agent-*.md
wc -l tmp/audit-2026-05-11/agent-*.md
````

Expected: five files, each non-zero.

- [ ] **Step 5: No commit.**

---

## Task 6: Per-finding human Q&A curation (Gate A — mandatory)

**Files:**

- Create: `tmp/audit-2026-05-11/curated-platform.md`
- Create: `tmp/audit-2026-05-11/curated-carrier.md`
- Create: `tmp/audit-2026-05-11/curated-schema.md`
- Create: `tmp/audit-2026-05-11/curated-validation.md`
- Create: `tmp/audit-2026-05-11/curated-calculator.md`

This is a **blocking gate**: no item from any agent enters the assembly pipeline (Task 7+) until the user has explicitly decided what to do with it. This implements the user's "per-point human interaction" requirement.

Curation is done pattern by pattern, in the order Platform → Carrier → Schema → Validation → Calculator. For each pattern, the operator iterates over the agent's findings and asks the user for a decision per item. The curated output is a copy of `agent-<pattern>.md` with the user's decisions applied.

- [ ] **Step 1: Announce the gate to the user**

Tell the user: "Five pattern agents have returned <N total> items. Starting per-finding curation for Pattern 1/5: Platform." Show a count of A/B/C items per pattern from the agent output files.

- [ ] **Step 2: For each pattern, present items in groups of 5–8 via `AskUserQuestion`**

For each A item, ask one question with options:

- **Keep** — include in the findings doc as written
- **Drop** — remove from the findings doc (false positive / not actually dead)
- **Move to noted-concerns** — keep visibility, don't promote to a plan
- **Discuss** — opens free-form Q&A in chat; operator and user agree, operator records the resolution

For each B item, same four options. "Discuss" is typical here — B items often need wording tweaks or simplicity-delta re-evaluation.

For each C observation, ask one question per architecture question (1–4 from spec Section 3 Step 4):

- **Keep** — accept the agent's framing
- **Reword** — operator captures user's preferred phrasing
- **Drop** — observation isn't actionable / agent overreached

`AskUserQuestion` allows max 4 options per question and max 4 questions per call. Batch items so each call fits.

- [ ] **Step 3: Apply each decision to `curated-<pattern>.md`**

Start from a copy of `agent-<pattern>.md`. For each decision:

- **Keep** → no change
- **Drop** → delete the row/block
- **Move to noted-concerns** → remove from A/B table, add a bullet to the agent's `noted-concerns` block prefixed with the item ID for traceability
- **Discuss** → record the negotiated text in place of the original

If the user asks for additional evidence on an item ("show me the rg output for this symbol"), the operator runs the search and shows the result in chat, then re-asks the keep/drop/move/discuss question.

- [ ] **Step 4: Per-pattern checkpoint with the user**

After each pattern is curated, summarize:

```
Pattern <X> curated:
- A items: <kept>/<total>, <dropped>, <moved>, <discussed>
- B items: <kept>/<total>, <dropped>, <moved>, <discussed>
- C items: <kept>/4, <reworded>, <dropped>
```

Wait for the user's go-ahead before moving to the next pattern. Allow the user to revisit decisions ("actually, drop Carrier B-3 too") — re-apply to `curated-carrier.md` before continuing.

- [ ] **Step 5: After all five patterns are curated, present the global summary**

Show counts for all five patterns and confirm with the user that processing (Task 7 onward) may begin.

- [ ] **Step 6: No commit yet.**

---

## Task 7: Assemble the findings doc — stitch sections

**Files:**

- Create: `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`

Order is fixed: **Platform → Carrier → Schema → Validation → Calculator** (spec Section 4). Source is the **curated** files from Task 6, not the raw agent output.

- [ ] **Step 1: Write the top of the findings doc**

Create `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` with this content (substitute SHAs from `preflight.md`):

```markdown
# v4-capabilities cleanup audit — findings

Branch: chore/v4-capabilities-cleanup-audit (<pdk-sha>) — audit of v4-capabilities vs main
Audit date: 2026-05-11
Plugin cross-check refs: docker-prestashop@<ps-sha>, docker-wordpress@<wp-sha>

## Summary

| Pattern    |            Dead (A) | Optimize (B) | Architecture (C) |
| ---------- | ------------------: | -----------: | ---------------: |
| Platform   | TBD-fill-in-task-12 |          TBD |              TBD |
| Carrier    |                 TBD |          TBD |              TBD |
| Schema     |                 TBD |          TBD |              TBD |
| Validation |                 TBD |          TBD |              TBD |
| Calculator |                 TBD |          TBD |              TBD |

## Caveats

- Symbols referenced only via DI/config strings flagged with ⚠.
- Dynamic method calls (`__call`, variable method names) not detected.
- Test-only references do not keep a symbol alive.
- Every item below passed Gate A (per-finding human curation).

---
```

The `TBD-fill-in-task-12` placeholders are intentional and removed by Task 12.

- [ ] **Step 2: Append each pattern section in order**

For each pattern in order Platform → Carrier → Schema → Validation → Calculator:

1. Open `tmp/audit-2026-05-11/curated-<pattern>.md`.
2. Extract the contents of the `findings` fenced block.
3. Append to the findings doc, preceded by a horizontal rule (`---`).

- [ ] **Step 3: Sanity-check the assembled doc**

Run:

```bash
grep -c '^## ' docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
```

Expected: at least 7 (one `## Summary`, one `## Caveats`, plus one per pattern = 7).

- [ ] **Step 4: No commit yet** — assembly continues in later tasks.

---

## Task 8: Resolve cross-pattern duplicates

**Files:**

- Modify: `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`

Per spec Section 5 assembly step 2: if the same symbol shows up in two pattern sections, keep the more specific finding (the one whose pattern owns it) and replace the other with a back-reference.

- [ ] **Step 1: Find duplicate symbols across pattern sections**

Run:

```bash
grep -E '`[A-Z][A-Za-z]+[A-Za-z0-9\\\\]+`' docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md \
  | sort | uniq -c | sort -rn | awk '$1 > 1' | head -40
```

This surfaces symbols appearing more than once. Manual review needed — some duplicates are legitimate (a class mentioned in evidence for two unrelated symbols).

- [ ] **Step 2: For each genuine cross-pattern duplicate, pick the owner**

A class belongs to the pattern where its namespace lives (spec Section 2). If `Pdk\App\Options\Foo` appears in both Schema and Calculator findings, Schema owns it.

- [ ] **Step 3: Replace the non-owner entry with a back-reference**

Edit the non-owner pattern's `Cross-pattern references` subsection to add:

```
- See `<Owner Pattern> §A` item A-N (shared symbol `Pdk\…\Foo`)
```

…and remove the duplicate entry from its own A/B table.

- [ ] **Step 4: No commit yet.**

---

## Task 9: Merge per-agent "Noted concerns" and "Open questions" buckets

**Files:**

- Modify: `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`

Source is the **curated** files (which include items the user moved into noted-concerns during Gate A).

- [ ] **Step 1: Collect all `noted-concerns` blocks**

For each `tmp/audit-2026-05-11/curated-<pattern>.md`, extract the `noted-concerns` fenced block. Prefix each bullet with its source pattern (`[Carrier] …`).

- [ ] **Step 2: Collect all `open-questions` blocks**

Same as above for `open-questions` blocks.

- [ ] **Step 3: Append both buckets to the findings doc**

Append at the bottom (these sections will be followed by Task 10's plan-slicing section):

```markdown
---

## Noted concerns (not promoted to plans)

<merged bullets with [Pattern] prefixes; empty if none>

## Open questions for human review

<merged bullets with [Pattern] prefixes; empty if none>
```

- [ ] **Step 4: No commit yet.**

---

## Task 10: PHPStan cross-check against the dead-code bucket

**Files:**

- Modify: `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` (possibly add entries to A tables)

Per spec Section 5 assembly step 5. **Any new entries discovered here must also pass Gate A** — re-prompt the user for keep/drop/move/discuss before adding to the findings doc.

- [ ] **Step 1: Extract symbol names from PHPStan unused output**

Run:

```bash
grep -oE '[A-Za-z\\\\]+::[a-zA-Z_]+' tmp/audit-2026-05-11/phpstan-unused.txt | sort -u > tmp/audit-2026-05-11/phpstan-symbols.txt
wc -l tmp/audit-2026-05-11/phpstan-symbols.txt
```

- [ ] **Step 2: Extract dead-bucket symbols from the findings doc**

Run:

```bash
grep -oE '`[A-Za-z\\\\]+(::|->)?[a-zA-Z_]+(\(\))?`' docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md | sort -u > tmp/audit-2026-05-11/findings-symbols.txt
```

- [ ] **Step 3: Find PHPStan-flagged symbols not in the findings**

Run:

```bash
comm -23 tmp/audit-2026-05-11/phpstan-symbols.txt tmp/audit-2026-05-11/findings-symbols.txt
```

If the list is non-empty, run a mini-Gate-A: for each missing symbol, determine its owning pattern, then ask the user keep/drop/move/discuss before adding the row. Evidence column: `flagged by PHPStan; missed by agent`.

- [ ] **Step 4: Annotate findings-only entries**

For dead-bucket items present in findings but NOT in PHPStan output, append `(PHPStan didn't see it)` to the evidence column. Use:

```bash
comm -13 tmp/audit-2026-05-11/phpstan-symbols.txt tmp/audit-2026-05-11/findings-symbols.txt
```

This list will include lots of B/C entries — only mark A-row entries.

- [ ] **Step 5: No commit yet.**

---

## Task 11: Write the "Recommended plan slicing" section

**Files:**

- Modify: `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`

Per spec Section 4 "Bottom of doc". This section groups A/B items per pattern into a notional plan, so the downstream per-pattern `writing-plans` invocations have a starting list.

- [ ] **Step 1: For each pattern, list its non-skipped A/B item IDs**

For example, for Carrier: read the Carrier section's A and B tables, list every item ID where the finding is **not** in Noted concerns. Format:

```
- **Plan: Carrier cleanup** — items: Carrier A-1, A-2, A-4, B-1, B-3
```

If a pattern has no items at all, write:

```
- **Plan: Carrier cleanup** — no items (pattern is clean)
```

- [ ] **Step 2: Append the slicing section**

Insert before the "Noted concerns" section (so the doc reads: pattern sections → plan slicing → noted concerns → open questions):

```markdown
---

## Recommended plan slicing

- **Plan: Platform cleanup** — items: …
- **Plan: Carrier cleanup** — items: …
- **Plan: Schema cleanup** — items: …
- **Plan: Validation cleanup** — items: …
- **Plan: Calculator cleanup** — items: …
```

- [ ] **Step 3: No commit yet.**

---

## Task 12: Generate summary-table counts

**Files:**

- Modify: `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md` (replace `TBD-fill-in-task-12` placeholders)

- [ ] **Step 1: Count A/B items per pattern**

For each pattern, count:

- A items: rows in the `### A. Dead code` table (excluding the header)
- B items: occurrences of `**B-` (one per optimization candidate)
- C items: always 4 (the four architecture questions), but report 0 if the C subsection is empty/absent

Use:

```bash
for p in Platform Carrier Schema Validation Calculator; do
  echo "=== $p ==="
  awk "/^## $p$/,/^## [A-Z]/" docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md \
    | grep -cE '^\| A-' || echo 0
done
```

Adapt for B counts (`grep -cE '\*\*B-'`).

- [ ] **Step 2: Replace `TBD-fill-in-task-12` placeholders**

Edit the summary table at the top of the findings doc with actual counts.

- [ ] **Step 3: Verify no `TBD` remains in the file**

Run:

```bash
grep -n 'TBD' docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
```

Expected: no output. If anything remains, resolve it before continuing.

- [ ] **Step 4: No commit yet.**

---

## Task 13: Verification spot-checks

**Files:**

- Modify (if any spot-check fails): `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`

Per spec Section 5 "Verification before done". If spot-checks force removal of any item that was approved by Gate A, the operator surfaces this back to the user in plain text (not a re-curation) and confirms before mutating the doc.

- [ ] **Step 1: Spot-check three random Dead entries**

Pick 3 random rows from any pattern's `### A. Dead code` table. For each symbol, manually run:

```bash
# Replace SYMBOL with the actual class or method name (just the short name)
rg -n 'SYMBOL' src/ tests/ ~/projects/docker-prestashop/modules/myparcelnl/ ~/projects/docker-wordpress/plugins/myparcelnl-woocommerce/ --glob '!vendor/'
```

Expected: zero "alive" hits in `src/` and the two plugin paths. Test-only hits are OK.

If any "alive" hits show up, the entry is **not** dead — flag to the user, remove it from the A table after their go-ahead, and add to Open questions with a note.

- [ ] **Step 2: Verify plan slicing is pattern-pure**

For each `Plan: X cleanup` line in the slicing section, confirm every listed item ID is prefixed with the matching pattern name. A `Carrier A-2` in the Calculator plan is a bug — fix it.

- [ ] **Step 3: Verify every B/C item has the four required fields**

For each pattern's `### B. Optimization candidates` section, check each item has:

- `**Symbol(s):**`
- `**Observation:**`
- `**Proposed change:**`
- `**Simplicity delta:**`
- `**Plugin impact:**`

Run:

```bash
awk '/### B\. Optimization candidates/,/### C\. Architecture/' docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md | grep -cE '\*\*(Symbol|Observation|Proposed change|Simplicity delta|Plugin impact)'
```

Expected: count is `5 × (number of B items)`. If short, the missing fields must be added — re-dispatch the relevant pattern agent with the specific gap if needed; any added item goes through Gate A.

- [ ] **Step 4: No commit yet.**

---

## Task 14: Commit the findings doc (Gate B — whole-doc review)

**Files:**

- Stage: `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`

- [ ] **Step 1: Verify you are on the audit branch**

Run:

```bash
git branch --show-current
```

Expected: `chore/v4-capabilities-cleanup-audit`. If it shows `v4-capabilities`, stop — commits must not land directly on `v4-capabilities`. Switch with `git checkout chore/v4-capabilities-cleanup-audit`.

- [ ] **Step 2: Gate B — show the user the final doc for whole-doc review**

Per project CLAUDE.md (review before commit). Display the assembled doc (or its summary table + section headings + plan-slicing section + open questions) and wait for explicit approval before committing. Note that all individual items already passed Gate A — Gate B is a holistic sanity check, not item-by-item.

- [ ] **Step 3: Stage and commit**

Run (only after user approval):

```bash
git add docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md
git commit -m "$(cat <<'EOF'
docs: add v4-capabilities cleanup audit findings

Per-pattern findings (Platform, Carrier, Schema, Validation, Calculator)
covering dead code, optimization candidates, and architecture observations.
All items curated via per-finding human Q&A (Gate A).
Drives downstream per-pattern cleanup plans.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 4: Verify commit landed**

Run:

```bash
git log -1 --stat
```

Expected: one file added (`docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`).

---

## Task 15: Clean up working artifacts

**Files:**

- Delete: `tmp/audit-2026-05-11/`

- [ ] **Step 1: Verify nothing in `tmp/audit-2026-05-11/` is tracked**

Run:

```bash
git ls-files tmp/audit-2026-05-11/
```

Expected: empty output.

- [ ] **Step 2: Remove the working directory**

Run:

```bash
rm -rf tmp/audit-2026-05-11/
```

- [ ] **Step 3: No commit** — `tmp/` is gitignored.

---

## Task 16: Push the branch (no PR yet)

**Files:**

- None (git operations only)

The PR is **deliberately deferred**. It is opened only after all five per-pattern cleanup plans have been written and committed on this same branch (see "Downstream" below). Even when opened, the PR stays in draft until cleanup code from those plans lands.

- [ ] **Step 1: Confirm branch state**

Run:

```bash
git branch --show-current && git log --oneline v4-capabilities..HEAD
```

Expected: current branch is `chore/v4-capabilities-cleanup-audit`; log lists the spec commit, the plan commit, and the findings commit (three commits ahead of `v4-capabilities` at this point).

- [ ] **Step 2: Push the branch**

Run:

```bash
git push -u origin chore/v4-capabilities-cleanup-audit
```

- [ ] **Step 3: Explicitly do NOT open a PR**

The PR is opened later, in a separate workflow, after the five downstream plans exist. If `gh pr create` is run at this stage, that is a plan violation — abort and confirm with the user.

---

## Downstream: per-pattern cleanup plans

After this audit completes, five separate `superpowers:writing-plans` invocations produce the per-pattern cleanup plans on this same branch:

- `docs/superpowers/plans/2026-05-…-platform-cleanup.md`
- `docs/superpowers/plans/2026-05-…-carrier-cleanup.md`
- `docs/superpowers/plans/2026-05-…-schema-cleanup.md`
- `docs/superpowers/plans/2026-05-…-validation-cleanup.md`
- `docs/superpowers/plans/2026-05-…-calculator-cleanup.md`

Each plan reads the `Plan: <pattern> cleanup` line of the findings doc to know its scope. Each plan's commit-readiness should itself include a per-item Q&A gate (mirroring Gate A in this plan), so the user reviews item scoping before the plan is written into a final form.

### PR opening (later)

Once all five per-pattern plans are committed on `chore/v4-capabilities-cleanup-audit`:

1. Open a **draft** PR with `gh pr create --draft --base v4-capabilities --head chore/v4-capabilities-cleanup-audit`.
2. The PR description lists: the audit spec, the findings doc, and the five cleanup plans.
3. The PR stays in draft while the per-pattern cleanup CODE is implemented (potentially on separate child branches per pattern, merging back into this branch).
4. Only when all cleanup code has landed and tests pass does the PR move out of draft and become reviewable for merge into `v4-capabilities`.
