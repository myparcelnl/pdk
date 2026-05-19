# API gap: "does carrier X support returns at all?"

**Status**: Open — candidate for INT-1568 (per-carrier API gaps, Research)
**Surfaced by**: Plan 4 (Validation + CarrierSchema dissolution)
**Date**: 2026-05-12

## The gap

The only way to learn whether a carrier supports inbound (return) shipments today
is to POST `/capabilities` with `direction: INBOUND`. That call requires
`recipient.country_code` — there is no carrier-wide signal.

The contract/proposition definitions response (the natural home for
carrier-wide flags) does not advertise return support at all.

Concrete consequence:

- We cannot answer "carrier X supports returns" — only "carrier X supports
  returns _to country Y_". A carrier with returns enabled for NL but not BE
  is indistinguishable from a carrier with no returns at all unless you ask
  per-country.
- Settings UI and pre-flight validations that ought to be carrier-scoped
  must either fall back to a hardcoded destination, defer the question
  until a destination is known, or hide the feature entirely.

## Why this matters now

`CarrierValidationService::supportsReturns(Carrier $c, array $context = [])`
existed as a single method with an optional `$context` bag, advertising the
"carrier-wide" question while quietly requiring `$context['cc']` to be useful.
That misled callers and reviewers.

Plan 4 fixed the API mismatch on the PHP side by:

1. Moving the method to `CapabilitiesValidationService::supportsReturns(Carrier, string $countryCode)` — country code now required, no `array` bag.
2. Updating both call sites (`PdkOrderCollection::generateReturnShipments`,
   `PostReturnShipmentsRequest::ensureReturnCapabilities`) to pass
   `$shipment->recipient->cc` and to skip with a notification when the
   destination is unknown.

The PHP side is now honest about what it can answer. The underlying API gap
remains.

## What we'd want from the API

Either (in order of preference):

1. **Contract definitions**: a per-carrier flag like `supportsReturns: bool`
   that holds across countries (with country-specific exceptions exposed
   separately).
2. **Capabilities**: allow `direction: INBOUND` without a destination,
   returning the union across all supported destinations.

Both are feasible; (1) better matches how the rest of the
contract/proposition data is shaped.

## Suggested next step

Roll this into INT-1568 (or a child of it) as a concrete API ask:

> Expose carrier-wide return support in the contract/proposition definitions
> response so the PDK does not need a destination to answer "can this carrier
> handle returns?". Until then, the PDK requires a destination for the
> question and skips the check (with a notification) when none is available.

## References

- `src/Carrier/Service/CapabilitiesValidationService.php` — new method
- `src/App/Order/Collection/PdkOrderCollection.php:80` — call site with null-cc skip
- `src/Shipment/Request/PostReturnShipmentsRequest.php:128` — call site with hoisted recipient guard
