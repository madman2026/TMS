# ND contract amendment 0002 — human-v1.1 intake boundary

Status: current-source reference; upstream amendment accepted on 2026-10-17; scoped commit authorized; DK migration pending. Not an imported or executable successor corpus.

## Owner and source

ND owns `platform/plugins/notification-delivery/docs/ai/testing/contracts/human-v1.1/`
in the sibling `dk` repository. Read its README, `amendment.json` and closed schema
together with the immutable `human-v1` base. Upstream Change 0071 and Remediation
0067 record the operator's 2026-10-16 authorization to recheck DK/ND and TMS and
update conflicting contracts. No new product Canonical decision. Scoped commit authorized on 2026-10-17.

| Input | Pin |
|---|---|
| ND/current target project source | `1b4416dd005d3102773580b000de82e8fe3416bf` |
| TMS/Core/DK source | `e7c81af9634267847947ef79ec9ccaaf4e2dc392` |
| Immutable base export | `7ec4b4dc8573f24252175bf9e1a21364b0a48d9c` |
| Base runtime snapshot | `0ef0b92d9192d0162060d31efe08f55552b084f7` |

The amendment records SHA-256 and byte lengths of all 22 committed base artifacts,
six authority hashes and 1,126 scoped source Git blob identities. Validate both
repositories using the upstream standalone amendment validator before intake.
The accepted amendment commit and artifact hashes are recorded in the intake pin
below. This reference does not represent an imported, materialized successor corpus.

## Requirement delta

The composition preserves all 139 base definitions and 24,668 variant identities.
Ten supplemental requirements append obligations to eight definition families with
3,696 affected existing variants: navigation 1,067; contextual help 2,424; health
205. No identities are removed or renamed. Supplemental contexts are not ten new
materialized variants; final successor variant/assertion totals remain undefined.

Changed obligations cover authenticated cross-page Admin navigation and negative
scope; enhanced single help surface, conditional native fallback and dynamic insertion;
health no-evidence/expiry clock semantics; five native recipes, exact fixture
projection, baseline cleanup and HTTP-hosted inspection-command registration.
Intentional Provider-form `nativeTitle=false` consumers retain their accessible
labels without a newly invented native title.

## Current consumer and architecture

- DK catalog `v4` contains all base identities; only
  `nd.component.operations.health` has executable Browser steps: 21 variants.
  The remaining 138 definitions / 24,647 base variants are non-executable skeletons.
- `nd.delivery.operations.health` is separate and remains unimplemented.
- Core supports Browser and HTTP; project batch/execution/run handoff still forces
  Browser. Preserve the accepted executor-neutral architecture and fix the gap in
  Remediation 0007; do not claim end-to-end HTTP execution already exists.
- Selector limit remains 1,000 and catalog visit budget 10,000. Pack 0024 owns
  bounded segmentation. No safety limits were raised.
- Target integration uses the accepted local lease, browser identity binding,
  frozen clock, exact fixture recipe and baseline digest/count cleanup. No real
  Provider I/O or new sensitive capture permission is introduced.

## Required exact DK migration

1. Use the accepted amendment pin below and resolve the materialized successor identity during the DK migration.
2. Add explicit composition support to the intake under a scoped DK Pack. The old
   importer cannot silently consume human-v1.1 as a flat human-v1 export.
3. Preserve unchanged identities and historical mappings. Reproject added obligations
   into source assertion IDs; materialize the supplementary positive/negative contexts
   and reconcile exact old/new variant and assertion counts without implied coverage.
4. Declare each scenario's capability and automation disposition from source; keep
   unimplemented/manual/blocked separate from executed and accepted.
5. Complete focused import/mapping validation before project Remediation 0007,
   segmentation Pack 0024 and DK Pack 0005/its scenario implementation children.

`ND-CORPUS-MANIFEST-0001.md`, old hashes, accepted import Runs and test history remain
unchanged. Thirteen historical checkout hash differences were LF/CRLF representation;
the amendment explicitly uses committed Git blob bytes. Never overwrite manifest
0001 to conceal this difference or invoke a broad historical import approval.

## Validation limits and known issues

The standalone amendment check validates base schemas/semantic joins and artifact
integrity, current scoped sources, changed-path dispositions, requirement references,
affected counts and exact 21 health identities. Nine negative probes cover malformed
relationships, safety and integrity rejection. It is not a complete rerun of the
old validator's historical lifecycle/current-source gates or runtime acceptance.

Accepted upstream Remediation 0066 Run 001 records 1,197 passing tests and one
isolated Acceptance lifecycle `target_control_capability_mismatch` failure, plus
Operations Health horizontal overflow at desktop width. Neither issue was rerun,
fixed or waived here. Preserve the corresponding requirements and track runtime
triage separately before claiming a clean full suite or current layout success.

No application source, generated mappings, target, DB, environment, worker or
Browser state was changed. Scoped commit authorized on 2026-10-17. See the TMS source-update review dated
2026-10-16 for the resumed verification and lifecycle disposition.
