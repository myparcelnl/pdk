# Jira research ticket — draft (Dutch)

Companion to `2026-05-11-v4-capabilities-cleanup-findings.md` §Cross-cutting Architecture observations.

**Type:** Research
**Status:** **Posted as [INT-1568](https://myparcelnl.atlassian.net/browse/INT-1568)** (Refinement)
**Project:** INT
**Labels:** capabilities, api-gap, research

This file is retained for traceability; the live ticket on Jira is the source of truth from this point on.

---

## Title

Onderzoek: aanpak voor niet-capabilities per-carrier API-gegevens

## Beschrijving

Tijdens de v4-capabilities cleanup zijn een paar plekken boven water gekomen waar we lokaal carrier-specifieke checks doen voor zaken die níet als capabilities-veld bestaan (eerder metadata/validation). Wil graag met API-team(s) afstemmen waar deze gegevens horen, zodat de PDK in een vervolg-PR de hardcodes kan opruimen.

Concrete punten:

- **Delivery date per carrier** — `DeliveryDateExceptionCalculator` heeft hardcoded BPost/DPD. Geen capabilities-veld; vermoedelijk metadata/validation-laag. Waar past dit?
- **Customer info required** — `CustomerInformationCalculator` doet hetzelfde voor DPD. Geen capabilities-veld; idem metadata/validation. Waar past dit?
- **Monday delivery** — `CarrierSchema::canHaveMondayDelivery()` checkt hardcoded PostNL. Bewust géén capabilities-onderwerp (widget-only feit). Hoort dit toch ergens in een API-vorm te leven, of laten we het puur een UI-keuze?
- **Legacy V1 ↔ V2 mapping** — INT-1441 verplaatst Carrier-mapping naar de SDK. Hetzelfde moet gelden voor DeliveryType, PackageType en ShipmentOptions (parallel V1/V2 enums in `DeliveryOptions`). Kunnen deze mappings dynamisch uit een API komen i.p.v. statische tabellen?

Uitkomst is een lijstje API-wijzigingen voor de roadmap (of bevestigingen "dit blijft lokaal"), plus een PDK-PR die de `@TODO`'s opruimt.

## Referenties

- `docs/superpowers/findings/2026-05-11-v4-capabilities-cleanup-findings.md`
- `docs/superpowers/findings/2026-05-11-carrierschema-architecture-decision.md`
- In flight: INT-1266, INT-1441, INT-1479

---

**Posted:** 2026-05-11 as [INT-1568](https://myparcelnl.atlassian.net/browse/INT-1568) in status `Refinement`.

**Out of scope for this ticket (handled in PDK cleanup plans):**

- `hasReturnCapabilities()` — directionality is already available in `/capabilities`; this is a PDK implementation gap, not an API gap. Will be fixed in the Validation/CarrierSchema dissolution plan.
