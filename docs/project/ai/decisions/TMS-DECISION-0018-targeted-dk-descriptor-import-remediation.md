# TMS-DECISION-0018 — Targeted DK descriptor-import remediation

Status: Accepted
Scope: Project / DK Pack 0003 recovery
Source: Operator decision
Date: 2026-10-11
Review At: completion review of DK Pack 0003
Blocking: Yes until the targeted remediation passes runtime validation
Closure Condition: the three shared descriptor imports resolve at runtime and DK Pack 0003 catalog and coverage validation pass
Next Review At: DK Pack 0003 post-execution review

## Type

3. Pack-Local Decision and 6. Change Request / Remediation Trigger

## Decision

Retain the safe partial output of DK Pack 0003 and correct its runtime descriptor-resolution failure by adding exactly these imports to the existing Notification Delivery Acceptance Component:

- `App\Data\ScenarioDescriptor`;
- `App\Data\SuiteDescriptor`;
- `App\Data\VariantDescriptor`.

The imports are outside the existing TMS managed markers. They must therefore be implemented through a targeted project remediation connected to Change Request 0007, not by rewriting the already executed Pack 0003 scope or moving PHP imports into a method-level managed region.

After the targeted correction, re-run the affected DK runtime catalog, target-module validation, coverage, lint, focused tests, and safe diff checks. The remaining shared importer/validator prevention, large generated-source formatting policy, and bounded PCRE execution work in Change Request 0007 remain follow-up work and do not block validating this retained DK import unless the re-run exposes another correctness failure.

## Reason

The shared importer generated unqualified descriptor construction inside valid managed regions, while the pre-existing component did not import the corresponding `App\Data` classes. Repositioning managed markers around namespace imports would create an invalid ownership boundary and placing `use` statements inside methods would be invalid PHP. The smallest safe correction is an explicit, reviewable import-only remediation.

## Impact

- The partial 139-scenario and 24,668-variant Pack 0003 output is retained.
- The historical Pack scope is not rewritten or presented as if it had originally authorized the correction.
- Runtime class resolution changes only for the existing generated descriptor references.
- No hierarchy identity, mapping, scenario behavior, executable Step, data, configuration, database, dependency, target, provider, browser, network, queue, API, UI, or security capability changes.
- Change Request 0007 remains open for shared prevention and scale/format policy hardening.
- No Canonical update is required.
- No operator guide update is required.

No error handling, logging, or traceability decision impact.

## Files to Update

- `docs/project/ai/decisions/DECISIONS-INDEX.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md`
- `docs/project/ai/changes/CHANGES-INDEX.md`
- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-DK-DESCRIPTOR-IMPORTS-0003.md`
- `docs/project/ai/remediations/REMEDIATIONS-INDEX.md`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php`
- DK Pack 0003 lifecycle records after validation

## Required change request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-IMPORT-COMPATIBILITY-0007.md` records the originating runtime compatibility defect. This Decision accepts only its targeted DK descriptor-import correction; shared preventive hardening remains open.

## future-work

- Complete the remaining shared importer/validator, formatting, and bounded-execution items in Change Request 0007 through separately approved scope.
- Do not treat this target-specific correction as proof that future generated target components are protected from the same defect.

## Review / Resolution

```text
Reviewed In: operator instruction on 2026-10-11
Review Result: retain the current Pack 0003 output and add the missing descriptor imports
Resolution: requires-remediation
Next Review At: DK Pack 0003 post-execution review
Resolution Notes: exact import correction validated and accepted; operator separately authorized commit on 2026-10-11
```

## Notes

Decision 0017 remains unchanged. This correction neither implements nor weakens sensitive-data protection.
