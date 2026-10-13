# CHANGE-REQUEST-TMS-TARGET-CONTROL-PROTOCOL-0011 — Add a reusable target Acceptance Control protocol client

## 1. Title

Add the target-neutral Acceptance Control client, protocol contract, and just-in-time secret-handle boundary required by target modules.

## 2. Status

Implemented and accepted through Project Pack 0021 / Run 001; transport default is extended only by Change Request 0012 and Decision 0023.

## 3. Related Release / Phase

Pre-Release cross-owner Acceptance roadmap; prerequisite for DK stage 14 Pack 0004 and future target systems.

## 4. Decision Source

Accepted `TMS-DECISION-0022`.

## 5. Proposed Change

Introduce one versioned `acceptance-control.v1` client boundary in TMS with typed requests/results, strict capability negotiation, bounded HTTP behavior, RFC 9457-compatible safe error normalization, and a constructor-injected `app-secret` resolver port. Keep target command/schema/driver details outside TMS.

## 6. Reason for Change

Current shared resource lifecycle ports are generic, but no concrete reusable transport exists for a real target lease, frozen clock, fixture recipe, cleanup, or baseline verification. DK Pack 0004 cannot safely invent that transport inside one component.

## 7. Scope of Impact

- TMS shared Acceptance application/infrastructure only;
- target modules may consume the new port without changing Core contracts;
- no target implementation, ND command, Browser scenario, product UI, database, or secret store is added here;
- documentation and tests must prove that target internals and secret values cannot escape.

## 8. Affected Files

Exact implementation scope is owned by `AI-PACK-TMS-TARGET-CONTROL-PROTOCOL-0021`. Current documentation scope is this Change Request, Decision 0022, Pack 0021, and their three indexes.

## 9. Backward Compatibility

Existing in-process fakes and target adapters remain valid. The new client is additive and unused until a target module explicitly binds it. No existing stable product/API contract changes.

## 10. Risk Level

Medium: outbound authenticated control, retry/idempotency, secret non-exposure, and cleanup recovery are security- and data-sensitive even though they are test-only.

## 11. Migration or Remediation Needed

No remediation or TMS data migration. Each target implementation needs its own separately approved Pack. DK Pack 0004 requires later owner-local binding.

## 12. Required Human Approval

Architecture and Project Pack 0021 implementation/Run/commit are accepted. Target/network access, concrete secret-provider binding, tests that call a target, owner-local activation, Browser execution, and push remain separately gated.

## 13. Acceptance Criteria

- TMS Core/shared code has no target command, schema, model, or recipe implementation knowledge.
- `acceptance-control.v1` operations and safe error mapping are typed and contract-tested.
- `app-secret` handles resolve only through an injected port immediately before a request.
- Secret values cannot serialize, log, persist, or appear in exceptions/results.
- PUT/GET/DELETE semantics, timeouts, retries, capability mismatch, and idempotency conflicts fail closed.
- Existing target lifecycle and full PHPUnit regressions pass.

## 14. Rollback Plan

Remove only Pack-0021-created client/protocol files and composition registrations. Existing lifecycle ports and target modules remain unchanged. No data rollback is required.

## 15. Final Decision

Implemented and accepted through Project Pack 0021. The exact opt-in local-loopback HTTP extension is owned by Change Request 0012 / Project Pack 0023.

## 16. Required remediation

None. This is an additive architecture change, not a correction of an accepted failed implementation.

## 17. Notes

The external secret provider is deliberately a deployment adapter. A Pack must not introduce a repository-backed secret store or read `.env` contents.
