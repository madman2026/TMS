# AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004 — First bounded Notification Delivery Operations Health Browser Acceptance group

Status: `blocked draft — architecture, five recipes, secret-handle boundary, and observation contract approved; awaiting accepted enabling Packs, final source allowlist, and separate execution gate`

Generated: `2026-10-11`

Owner: DK

Decision: accepted project Decisions 0005, 0007, 0012, 0015, temporary 0017, 0021, and 0022; accepted `DK-DECISION-0001` through `DK-DECISION-0004`.

Depends On: accepted DK Packs 0001–0003 and their accepted Runs; accepted project Packs 0019–0020 and their Run 001 records; accepted configuration-surface Remediation 0005; accepted Project Pack 0021 implementing `acceptance-control.v1`; accepted target-project Acceptance Control Agent Pack; accepted target ND Operations Health Driver Pack; accepted Decisions 0022 and DK 0003–0004; a verified deployment secret-provider binding and isolated target manifest; final exact allowlist normalization; and a separate DK target/runtime execution gate.

Documentation commit: authorized on 2026-10-12. Acceptance, execution, target access, tests that launch a browser or connect to a target, and the later implementation commit each retain separate operator gates.

## 1. Task ID

`AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004`

## 2. Task Title

Implement the first bounded ND Browser Acceptance group for Operations Health.

## 3. Goal

Make exactly one imported Notification Delivery scenario group executable through the accepted Browser Acceptance path:

```text
dk / notification-delivery / admin.delivery /
nd-component-operations-health / all 21 imported variants
```

The completed Pack must automate the five health-evidence component cases and the 16 locale/theme/viewport/focus variants, preserve every imported identity and covered-assertion link, perform no product mutation, perform no Provider I/O, and leave the other 138 imported scenarios `not-implemented`.

This Pack is Browser Acceptance only. It does not implement API/HTTP scenarios, expand the ND fake, send notifications, simulate callbacks, run queues/workers, or reconcile the remaining corpus.

## 4. Context

Accepted DK Pack 0003 completed stage 13 by importing 139 scenario definitions and 24,668 deterministic mappings as non-executable skeletons. Decision 0015 assigns the first bounded executable groups to stage 14. `DK-DECISION-0002` selects `nd-component-operations-health` as the first group and limits Pack 0004 to Browser Acceptance.

The selected upstream definition is read-only (`GET`), has no action references, declares `provider_io=none`, uses `non-sensitive-visual` evidence, and depends on `fixture.health.evidence -> fixture.provider.enabled`. Both fixtures declare `provider_io=none`, synthetic isolation, frozen time, reverse-order cleanup, and no real recipients.

The original normalized Draft identified three contract gaps:

1. all generated ND `ScenarioDescriptor` values use one shared `metadata()` result, so one scenario cannot safely become `automated` while the other 138 remain `not-implemented`;
2. the selected `variant_key` and safe target resource context do not reach `AcceptanceScenario::steps()`;
3. DK target-resource resolution, fixture provisioning, oracle evaluation, and cleanup remain fail-closed sentinels.

The first two gaps and the replay-safe generated migration were implemented and accepted through Change Request 0009, project Pack 0019, and Run 001. Pre-execution source inspection on 2026-10-11 then found two additional shared gaps: the runtime resolver did not receive the validated prerequisite handles needed by a target-owned access resolver, and Core had no bounded target-semantic observation port for the required computed/effective/freshness/reason/source facts. Accepted Decision 0021, Change Request 0010, and project Pack 0020 own those shared changes; Pack 0020 / Run 001 is now implemented, validated, and accepted. DK still owns concrete access resolution, selectors, fixtures, observations, oracle, and cleanup. Manual edits inside generated managed regions remain forbidden because Pack-0003 import replay must remain deterministic and idempotent.

Configuration-surface Remediation 0005 and project Packs 0019–0020 / Run 001 are accepted. This Draft is normalized against the actual shared resolver and observation source. On 2026-10-12 the operator accepted `DK-DECISION-0003`, which fixes the run-owned fixture lease, target-clock, reverse-cleanup, and baseline-verification lifecycle, and `DK-DECISION-0004`, which fixes the generic protocol/DK adapter binding, five recipe keys, just-in-time secret resolution, and bounded observation schema. Pack 0004 remains explicitly blocked until Project Pack 0021, the target-project Agent Pack, and the target ND Driver Pack are implemented and accepted, their actual source paths are incorporated into the final allowlist, the deployment binding is verified without exposing values, and this Pack receives its own execution gate.

## 5. Related Release / Phase

Decision 0015, stage 14 of the sixteen-stage roadmap: first independently bounded executable ND scenario group.

## 6. Related Epic / Feature / Story

Notification Delivery Operations Health — objective read-only Browser Acceptance for health evidence, freshness/effective-state projection, responsive rendering, directionality, theme, and focus behavior.

## 7. Source References

- `docs/project/ai/decisions/TMS-DECISION-0005-testing-only-minimal-safeguards-roadmap.md`
- `docs/project/ai/decisions/TMS-DECISION-0007-nwidart-target-modules-and-component-hierarchy.md`
- `docs/project/ai/decisions/TMS-DECISION-0012-automated-e2e-coverage-and-traceability.md`
- `docs/project/ai/decisions/TMS-DECISION-0015-clean-acceptance-hierarchy-cutover.md`
- `docs/project/ai/decisions/TMS-DECISION-0017-temporary-local-synthetic-data-protection-deferral.md`
- `docs/project/ai/decisions/TMS-DECISION-0021-bounded-target-runtime-input-and-observation.md`
- `docs/project/ai/decisions/TMS-DECISION-0022-target-acceptance-control-protocol.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0001-isolated-target-foundation-boundary.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0002-first-bounded-nd-browser-acceptance-group.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0003-operations-health-fixture-lease-lifecycle.md`
- `Modules/DK/docs/ai/decisions/DK-DECISION-0004-target-control-binding-and-operations-health-recipes.md`
- `Modules/DK/docs/ai/changes/CHANGE-REQUEST-DK-TARGET-CONTROL-BINDING-0001.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0009.md`
- `docs/project/ai/packs/AI-PACK-TMS-ACCEPTANCE-EXECUTION-CONTEXT-0019.md`
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-TARGET-RUNTIME-OBSERVATION-0010.md`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-RUNTIME-OBSERVATION-0020.md`
- `docs/project/ai/packs/AI-PACK-TMS-TARGET-CONTROL-PROTOCOL-0021.md`
- `docs/project/ai/runs/AI-PACK-TMS-TARGET-RUNTIME-OBSERVATION-0020-run-001.md`
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-CORPUS-IMPORT-0003.md`
- `Modules/DK/docs/ai/runs/AI-PACK-DK-NOTIFICATION-DELIVERY-CORPUS-IMPORT-0003-run-001.md`
- `Modules/DK/docs/ai/references/ND-CORPUS-MANIFEST-0001.md`
- current source paths listed in section 10

The manifest pins the authoritative upstream Human Acceptance Contract v1 at commit `7ec4b4dc8573f24252175bf9e1a21364b0a48d9c`. Raw upstream corpus files must not be copied into this Pack or into TMS source.

## 8. Files to Create

The confirmed source-contract portion of the DK-local creation boundary, normalized against accepted project Packs 0019–0020, is:

- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Data/OperationsHealthVariant.php`;
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Catalogs/OperationsHealthVariantCatalog.php`;
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Selectors/OperationsHealthSelectors.php`;
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Fixtures/OperationsHealthFixture.php`;
- `Modules/DK/app/Acceptance/Shared/Target/Contracts/DKSyntheticTargetControl.php`;
- `Modules/DK/app/Acceptance/Shared/Target/DKBrowserTargetRuntime.php`;
- `Modules/DK/app/Acceptance/Shared/Target/DKBrowserTargetRuntimeResolver.php`;
- `Modules/DK/app/Acceptance/Shared/Target/DKTargetControlAdapter.php`;
- `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Catalogs/OperationsHealthVariantCatalogTest.php`;
- `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Scenarios/NdComponentOperationsHealthTest.php`;
- `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Fixtures/OperationsHealthFixtureTest.php`;
- `Modules/DK/tests/Unit/Acceptance/Shared/Target/DKBrowserTargetRuntimeTest.php`;
- `Modules/DK/tests/Unit/Acceptance/Shared/Target/DKBrowserTargetRuntimeResolverTest.php`;
- `Modules/DK/tests/Feature/NotificationDeliveryOperationsHealthAcceptanceTest.php`;
- after separately approved execution, `Modules/DK/docs/ai/runs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004-run-001.md`.

Project Packs 0019–0020 / Run 001 are implemented, validated, and accepted. Decision 0022 and DK Decision 0004 now assign the runtime-only `app-secret` resolution to the shared injected port and the owner-local mapping to `DKTargetControlAdapter`; no secret value or provider implementation belongs in this module. Sections 8–9 remain a provisional, non-executable allowlist until the three enabling Packs are accepted and their actual namespaces/signatures are verified. Any path adjustment must be a Pack normalization, not an inferred implementation.

## 9. Files to Edit

The intended DK-local edit boundary is:

- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Scenarios/NdComponentOperationsHealth.php`;
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php` — edit only the non-managed keyed metadata hook delivered by project Pack 0019; no managed-region edit;
- `Modules/DK/app/Acceptance/DKAcceptanceApp.php` — catalog version only, expected `v3` to `v4` after the executable contract changes;
- `Modules/DK/app/Acceptance/Shared/Target/DKTargetResourceAdapter.php` — only the exact selected identity and synthetic resource lifecycle;
- `Modules/DK/app/Providers/DKServiceProvider.php` — register the DK runtime resolver in the accepted shared registry and no fallback;
- `Modules/DK/tests/Unit/DKAcceptanceAppTest.php`;
- `Modules/DK/tests/Unit/DKTargetResourceAdapterTest.php`;
- `Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php`;
- `Modules/DK/tests/Feature/NotificationDeliveryComponentIntegrationTest.php`;
- `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-OPERATIONS-HEALTH-ACCEPTANCE-0004.md` — normalization/lifecycle only;
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`;
- `Modules/DK/docs/ai/runs/RUNS-INDEX.md` — only after an approved Run Report exists.

No shared project/Core source file is authorized by this DK Pack. Project Pack 0019 owns its exact shared file list, generated migration, and validation.

## 10. Files to Read / Reference

- `AGENTS.md`
- `docs/ai/start/START-HERE.md`
- `docs/ai/start/CONTEXT-MAP.md`
- `docs/project/ai/TMS-AI-PROFILE.md`
- `docs/modules/MODULES-DOCS-INDEX.md`
- `Modules/DK/AGENTS.md`
- `Modules/DK/docs/ai/README.md`
- `Modules/DK/docs/ai/manifest.yaml`
- `Modules/DK/docs/ai/GOVERNANCE-PROFILE.md`
- `Modules/DK/docs/ai/packs/PACKS-INDEX.md`
- exact source references in section 7
- `app/Acceptance/Execution/AcceptanceExecutionService.php`
- `app/Acceptance/Modules/NwidartTargetModuleCreator.php`
- `app/Acceptance/Modules/TargetModuleValidator.php`
- `app/Acceptance/Targets/Data/TargetContext.php`
- `app/Acceptance/Targets/Contracts/TargetBrowserRuntimeResolver.php`
- `app/Acceptance/Targets/TargetBrowserRuntimeRegistry.php`
- `app/Acceptance/Targets/TargetResourceCoordinator.php`
- `app/Services/AcceptanceRunService.php`
- `Modules/Core/app/Contracts/AcceptanceScenario.php`
- `Modules/Core/app/Contracts/BrowserProbe.php`
- `Modules/Core/app/Contracts/BrowserTargetRuntime.php`
- `Modules/Core/app/Contracts/BrowserTargetObserver.php`
- `Modules/Core/app/Contracts/TestContext.php`
- `Modules/Core/app/Data/AcceptanceExecutionIdentity.php`
- `Modules/Core/app/Data/ExecutorRequest.php`
- `Modules/Core/app/Data/RunOptions.php`
- `Modules/Core/app/Data/ScenarioMetadata.php`
- `Modules/Core/app/Data/BrowserTargetObservation.php`
- `app/Acceptance/Prerequisites/Data/PrerequisiteExecutionData.php`
- `Modules/Core/app/Services/AcceptanceRunner.php`
- `Modules/Core/app/Services/BrowserAcceptanceExecutor.php`
- `Modules/DK/app/Acceptance/DKAcceptanceApp.php`
- `Modules/DK/app/Acceptance/Coverage/SourceCaseMappings.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/NotificationDeliveryAcceptanceComponent.php`
- `Modules/DK/app/Acceptance/Components/NotificationDelivery/Scenarios/NdComponentOperationsHealth.php`
- `Modules/DK/app/Acceptance/Shared/Target/DKTargetProfile.php`
- `Modules/DK/app/Acceptance/Shared/Target/DKTargetResourceAdapter.php`
- `Modules/DK/app/Providers/DKServiceProvider.php`
- `Modules/DK/config/config.php`
- nearby DK/Core PHPUnit tests for the exact classes above
- `composer.json`
- `phpunit.xml`
- `docs/ai/rules/AI-PACK-GENERATION-RULES.md`
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`
- `docs/ai/rules/AI-EXECUTION-RULES.md`
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`
- `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`
- `docs/ai/rules/GIT-AND-COMMIT-RULES.md`
- `docs/ai/rules/REPORTING-RULES.md`
- `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`
- `docs/ai/rules/CONFLICT-CHANGE-REMEDIATION-RULES.md`

### Project / Framework Source Reference Rule

Use the project/DK profiles, actual Laravel 12/Nwidart source, accepted shared Acceptance contracts, and nearby PHPUnit conventions. Use Laravel Boost documentation search only when version-sensitive installed behavior cannot be resolved from current source.

Do not bulk-read unrelated scenario groups, Packs, Runs, Reviews, archives, vendor code, `.env`, external target data, or the entire raw corpus.

## 11. Configuration / Settings Requirements

Configuration / Settings Impact: runtime setup required; no versioned configuration change is approved.

### Config File Requirements

No config file changes are currently authorized. Accepted Remediation 0005 established the authoritative root/Core `core.acceptance.*` surface, while current `Modules/DK/config/config.php` exposes only the fail-closed `dk.target.enabled` and `dk.target.environment` keys. Preserve `config/core.php`, `Modules/Core/config/config.php`, and `Modules/DK/config/config.php`. If the approved target-access procedure requires any additional configuration key, environment declaration, or secret-manager binding, stop and renormalize the exact file allowlist before implementation.

### Environment Variable Requirements

No environment variables are introduced. Never read or edit `.env`. No URL, session, cookie, credential, token, account, or access-bearing value may be documented or committed.

### Admin Settings Requirements

No Admin Settings changes required.

### External Integration / Secret Settings Requirements

No secret value or external-integration setting is introduced by this Draft. An opaque `app-secret://dk/...` handle may be carried only as validated prerequisite data; it is not a usable secret and must remain inert until an approved DK-owned runtime boundary resolves it. Current DK source contains no concrete handle resolver or binding, so its source and resolution procedure remain blocking. Runtime target/account/session values must be short-lived, synthetic-only, confined to private per-execution memory, and absent from descriptors, logs, reports, source, and persisted Step/Test data.

### Test / Development Setup Requirements

Before an actual Browser Acceptance run, the operator must separately authorize an isolated local synthetic target capable of:

- resolving one dedicated synthetic super-user session at runtime;
- reaching the existing Operations Health route without recording its URL in source/docs;
- provisioning `fixture.provider.enabled` and `fixture.health.evidence` with a frozen synthetic clock;
- preventing real Provider, queue worker, scheduler, callback, SMS, notification, or external-network activity;
- cleaning only run-owned resources and proving unrelated state equals the baseline.

The exact endpoint and credential-provider values remain runtime-only and must be verified without disclosure at Pack 0004's own pre-execution gate. The shared call shape is fixed: `DKBrowserTargetRuntimeResolver::resolve(TargetContext $context, PrerequisiteExecutionData $prerequisites): BrowserTargetRuntime` receives the final context and validated prerequisite handles. The accepted DK-owned `DKTargetControlAdapter` delegates to the project client/secret-resolution ports, returns one fresh per-execution `DKBrowserTargetRuntime`, and is never discovered through the container or another service locator. The runtime then prepares the Browser context and opens only the approved semantic Operations Health surface.

### Settings Validation

```text
Validation required:
- Config keys checked: Yes, by accepted Remediation 0005; no Pack-owned changes
- Env placeholders documented: Not applicable
- Admin settings created or documented: Not applicable
- Sensitive settings masked: Omitted; raw values forbidden
- Tests/API test artifacts updated for setting-dependent behavior: Browser Acceptance tests only; API artifacts not applicable
```

### Operator Setup Notes

Operator manual setup is required before the real Browser run and must be stated without access-bearing values at Pack 0004's pre-execution gate. Unit/catalog/runtime-contract tests must not depend on that setup.

## 12. Do Not Change

- any scenario other than `nd-component-operations-health` or any variant outside its exact 21-key set;
- any imported source-case identity, covered assertion, disposition, suite, scenario, or variant mapping;
- the other 138 scenario skeletons or their `not-implemented` disposition;
- API/HTTP executors, routes, API artifacts, request/response contracts, or HTTP scenario coverage;
- ND fake contracts, fake implementation, callbacks, worker/time/audit simulation, or Pack-0002 behavior;
- real Provider/SMS/notification behavior, queues/workers/scheduler, inbound callbacks, or external network;
- target product source, routes, views, translations, permissions, database schema, or migrations;
- shared project/Core source through this DK Pack;
- managed generated regions by manual replacement outside the accepted importer/generator migration path;
- `.env`, `.env.example`, dependency files, lockfiles, vendor, public/generated assets, application cache, or production/staging systems;
- `config/core.php`, `Modules/Core/config/config.php`, or any file owned by accepted configuration-surface Remediation 0005;
- accepted historical Packs, Runs, Reviews, Decisions, Changes, or Remediations except lifecycle/index updates explicitly allowed here.

## 13. Clarification Questions Before Implementation

The shared contract and configuration questions are resolved by approved Change Requests 0009–0010, accepted project Packs 0019–0020, and accepted Remediation 0005. Source-contract renormalization is complete. Before Pack 0004 can be accepted or executed, the operator must still confirm:

1. project Pack 0019 has accepted Run 001 and its implemented contract matches this normalized scope — confirmed 2026-10-11;
2. project Pack 0020 has an accepted Run and its actual resolver/observer source matches Decision 0021 — confirmed 2026-10-11;
3. the exact isolated local synthetic target startup/access procedure and constructor-injected owner-local resolver for the opaque `app-secret` handle are available without placing URL/session/credential values in source, docs, chat output, logs, or persisted Test/Step data;
4. the fixture lease lifecycle in `DK-DECISION-0003` is accepted — confirmed 2026-10-12; the exact five-case recipes and concrete local target-control binding/path remain to be approved;
5. one exact observation key, the complete bounded fact-key set, every allowed token value, and the stable source-owned semantic selectors are approved for the five health cases and presentation matrix;
6. the Browser can reach only that local target and every run-owned reference can be deterministically cleaned.

## 14. Multilingual / Translation Requirements

```text
Multilingual / Translation Impact: Yes — existing behavior is tested
RTL/LTR Impact: Yes
API Message Translation Impact: No
Admin UI Translation Impact: No source change; existing rendering observed
Owner-defined Default Content Translation Impact: No
```

No translation file or visible text is created or edited. The 21 variants must verify existing `en/ltr` and `fa/rtl` rendering. Assertions must use stable semantic roles/labels or approved source-owned selectors and must not hardcode new translated UI text when a language-neutral observable is available.

Human review of translation wording is outside Pack scope; objective visibility, direction, focus, and layout behavior remain automated.

## 15. UI / Admin UI Requirements

```text
UI Impact: No source change; existing Admin UI is observed
Admin UI Impact: Acceptance observation only
Public UI Impact: No
Settings UI Impact: No
Dashboard / Monitoring UI Impact: Operations Health read-only surface only
Table / Form Impact: No source change
Action / Button Impact: No product action
Navigation / Menu Impact: No
Permission-gated UI Impact: Existing super-user route access only
```

No UI file is created or edited. The scenario must navigate to the existing semantic Operations Health surface and assert only objective existing behavior:

The following items describe required outcomes only; they are not an implementation schema. Before execution, section 17 must contain the approved single observation key, exact fact keys and token values, and source-owned selectors.

- computed, effective, freshness, reason, sample-rate, and latency values remain distinguishable;
- expired evidence yields the source-defined unknown effective state;
- absent `checked_at` yields the source-defined no-evidence freshness state;
- the current evidence source is `delivery_attempt` and `remote_probe_supported=false`;
- desktop/narrow, light/dark, English/Persian, LTR/RTL, normal/focus states remain observable without overflow or focus loss defined by the source contract.

No screenshot, video, trace, HAR, raw DOM, console, or network artifact is retained. `non-sensitive-visual` is an evidence classification, not authority to add recording.

## 16. Operator Documentation Requirements

```text
Operator Documentation Impact: No current command/workflow change
Operator Documentation Update Required: Not applicable for this Draft
Guide File: docs/project/ai/guides/ACCEPTANCE-CLI.fa.md
```

No operator-facing behavior changes in this Draft. If the accepted enabling/target-integration work introduces a new setup, command option, or troubleshooting path, the owning Pack must update the Persian guide before Pack 0004 can be accepted.

## 17. Implementation Rules

After accepted project Packs 0019–0020, incorporation of the blocking DK-local contracts, and this Pack's separate execution approval:

1. Represent the exact 21 variants as immutable DK-owned typed data keyed by the imported `variant_key`.
2. Preserve the imported source identity and assertion projection; do not copy raw corpus prose or the full corpus into runtime source.
3. Resolve variant data only from `TestContext`'s exact `AcceptanceExecutionIdentity`. Reject missing, unknown, or mismatched App/Component/Suite/Scenario/Variant identity before semantic navigation.
4. Use exactly five ordered scenario phases: prepare, reach component, exercise component lifecycle, observe safe results, cleanup.
5. Use semantic/accessibility-first selectors owned under the ND Component. No unstable generated CSS path, database identifier, URL literal, translated free text, or personal/sensitive value may become an identity.
6. Override only `metadataFor('nd-component-operations-health')` outside managed regions as `AutomationDisposition::AUTOMATED`, `EvidenceMode::NON_SENSITIVE_VISUAL`, and Browser capability. Keep every other keyed call on the generated `NOT_IMPLEMENTED`/`METADATA_ONLY` default.
7. Implement `DK-DECISION-0003` through `OperationsHealthFixture` and a constructor-injected `DKSyntheticTargetControl` port. Acquire one exclusive lease owned by the exact `lifecycleId`, capture only a bounded baseline digest/count, freeze the clock inside the isolated target, provision `fixture.provider.enabled` then `fixture.health.evidence`, and return an opaque `ResourceReference` for every acquired resource, including partial acquisition on failure. Cleanup must remove evidence then provider, restore the target clock, prove baseline equality, close the lease, and account for every reference. Never delete by broad query or unverified ownership.
8. Keep `provider_io=none`; do not call or expand the existing ND fake. Prove that Provider, notification, callback, queue, worker, scheduler, and external-network behavior do not occur.
9. Keep product behavior read-only. Rendering, local focus, and viewport interactions must not generate a domain mutation or Audit.
10. Increment the DK catalog version exactly once only when the externally visible executable contract changes.
11. Register one stateless `DKBrowserTargetRuntimeResolver` for App `dk` with the exact accepted signature `resolve(TargetContext $context, PrerequisiteExecutionData $prerequisites): BrowserTargetRuntime`; it must validate the final provisioned context and prerequisite data, delegate opaque-handle resolution to a constructor-injected DK-owned boundary, create a fresh per-execution `DKBrowserTargetRuntime`, and retain no access-bearing value in the singleton registry/resolver. Container lookup and service-location inside the resolver/runtime are forbidden.
12. For this scenario group, `DKBrowserTargetRuntime` must implement both `BrowserTargetRuntime` and the otherwise optional `BrowserTargetObserver` extension. It may keep resolved access values only in private per-execution memory, prepare Browser-local session state, and open only an allowlisted semantic surface key. Its observer must return `BrowserTargetObservation` for the same requested key with an associative fact map of 1–16 entries; observation/fact keys must match `[a-z0-9][a-z0-9._-]*` with a maximum length of 64, and values must remain within the accepted boolean/null, bounded finite numeric, or grammar-constrained token-string contract. It must expose no raw URL/session/reference getter, raw DOM/text/selector fact, array/JSON/serialization surface, or debug payload.
13. Scenario code must request the approved observation only through `TestContext::observeTarget($observationKey)`. It must not cast, downcall to, or directly obtain the runtime/observer. The final execution-normalized Pack must replace the descriptive UI statements in section 15 with one exact observation key, the complete fact-key set, and every allowed token value; none may be invented from this Draft.
14. Target-clock control must be scoped to the run/session. If the target supports only a global clock, execution requires one exclusive isolated target instance and parallel runs are forbidden. TMS/PHPUnit time helpers may control unit-test time but do not satisfy target-clock control.

### Exact Variant Set

Component-case variants:

| Variant key | Case | Locale/direction | Theme | Viewport |
|---|---|---|---|---|
| `v.005502ef537b4a85` | `no-evidence` | `fa/rtl` | light | 1440x900 |
| `v.2197c878348bb598` | `stale` | `fa/rtl` | light | 1440x900 |
| `v.3f982418b8fb90aa` | `config-invalid` | `fa/rtl` | light | 1440x900 |
| `v.b2ee9a53fab09e29` | `fresh` | `fa/rtl` | light | 1440x900 |
| `v.de2c1cb3da9d87f3` | `empty-providers` | `fa/rtl` | light | 1440x900 |

Presentation matrix variants:

| Variant key | Locale/direction | Theme | Viewport | State |
|---|---|---|---|---|
| `v.1586b7aa8872f011` | `en/ltr` | dark | desktop | normal |
| `v.3b6dd77897b98280` | `en/ltr` | light | desktop | focus |
| `v.409086a3f7ea716c` | `en/ltr` | dark | narrow | normal |
| `v.5d5ea9c6f7091a2e` | `en/ltr` | light | narrow | normal |
| `v.60c2d47b88f49444` | `en/ltr` | dark | narrow | focus |
| `v.664312d320688977` | `fa/rtl` | light | narrow | normal |
| `v.68112113f9e6b4f6` | `fa/rtl` | dark | narrow | focus |
| `v.798c9da3e4155865` | `fa/rtl` | light | desktop | normal |
| `v.7e0e7faddc435fee` | `fa/rtl` | dark | narrow | normal |
| `v.987b893eeeab92b5` | `en/ltr` | light | desktop | normal |
| `v.acb807843d0fa3b6` | `en/ltr` | light | narrow | focus |
| `v.be7939d7e43d403b` | `fa/rtl` | dark | desktop | focus |
| `v.be81b57e2f6394f4` | `en/ltr` | dark | desktop | focus |
| `v.c51c5e04047f90a5` | `fa/rtl` | dark | desktop | normal |
| `v.d7e5a9544b75803b` | `fa/rtl` | light | narrow | focus |
| `v.e1b6f42e950f61e0` | `fa/rtl` | light | desktop | focus |

All 21 variants use the super-user persona, `fixture.health.evidence`, `provider_io=none`, and `non-sensitive-visual` evidence.

## 18. Architecture Constraints

- Preserve `App -> Component -> Suite -> Scenario -> Variant -> Step` and full tuple identity from selection through execution/result reporting.
- DK owns ND access resolution, scenario/selector/fixture/oracle/semantic mapping behavior; shared orchestration and Core Browser contracts remain target-neutral and project/Core-owned.
- Generated metadata must support one executable scenario without creating duplicate descriptors or making unrelated scenarios executable after import replay.
- Target references remain opaque. Scenario code receives exact identity through `TestContext`, opens the target only through `BrowserTargetRuntime`, and consumes only bounded semantic observations through the project-Pack-0020 contract; it must not receive prerequisite data, raw `TargetContext`, `ResourceReference`, DOM, text, selector, or access values.
- The resolver/access boundary uses explicit constructor injection. Unit tests construct the resolver/runtime with small typed doubles and do not boot the Laravel container merely to discover dependencies.
- No service locator, static mutable variant state, global current-variant variable, filesystem scan, database-defined executable workflow, or scenario-name parsing is allowed.
- Do not bypass the target-resource lifecycle. Readiness, account/session resolution, fixture setup, final-context runtime resolution, execution, oracle, and cleanup remain ordered and fail closed.
- Do not turn the existing ND fake into the target integration. No fake/API/callback/queue capability is added in this Pack.

## 19. Validation Rules

- Verify the exact 21-key set and deterministic order against the accepted Pack-0003 catalog and manifest.
- Verify exactly one Scenario descriptor is executable and all other 138 remain non-executable.
- Verify all 21 resolutions carry their own exact variant identity and cannot collapse into one default behavior.
- Replay the accepted import path in an isolated test and prove no duplicate descriptor/resolution and no loss of executable metadata.
- Verify fixture dependency order, frozen time, reverse cleanup, baseline equality, and no cleanup of unrelated records.
- Verify no Provider/fake/API/HTTP/callback/queue/worker/scheduler/external-network interaction occurs.
- Verify safe results contain only bounded step names, stable error codes, durations, and full language-neutral hierarchy identity.
- Verify the runtime resolver receives the exact same validated `PrerequisiteExecutionData` produced by prerequisite execution, creates a fresh runtime, and never resolves dependencies through global/container state.
- Verify `TestContext::observeTarget()` is the only scenario observation path; the returned key exactly matches the requested key, facts satisfy the accepted 1–16 entry/key/value bounds, and the final DK schema rejects unknown keys or token values.
- Verify no URL, session, cookie, credential, token, recipient, personal data, raw DOM, raw target reference, or target exception appears in descriptors, persisted test data, output, logs, or reports.
- Verify PHP syntax, scoped formatting, focused tests, affected DK/project regressions, full suite, documentation/index consistency, and `git diff --check`.
- Run real Browser variants only after the operator approves the exact isolated local synthetic target setup. Do not claim Browser E2E validation otherwise.

## 20. Security Rules

- Decision 0017 applies only while execution is local, single-operator, wholly synthetic, non-retained, and disconnected from every real target/provider/customer/production/staging system.
- If any value is not demonstrably inert and synthetic, stop.
- Never read/store/log/display/return/commit a real URL, account, password, token, cookie, session, Authorization header, recipient, phone number, payload, personal identifier, target resource reference, or environment value.
- Do not retain screenshots, video, trace, HAR, raw DOM, console, network, telemetry, or other evidence artifacts.
- Block stray outbound HTTP/network requests during tests. The Browser may reach only the separately approved isolated local synthetic target.
- No external fake/mock service is introduced. Existing Pack-0002 fake source remains unchanged.

## 21. Error Handling / Logging / Traceability Requirements

### Error Handling Impact

```text
Error Handling Impact: Yes
API Error Contract Impact: No
UI / Admin UI Error Impact: No product-source change
Internal Exception Impact: No new public exception contract
External Integration Error Impact: No external integration
Callback / Inbound-event Error Impact: No
Logging Impact: No new log event
Traceability Impact: Yes
Error Code Impact: No new code; reuse accepted lifecycle/browser codes
Security / Sensitive Data Impact: Yes
```

### Error Scenarios

| Scenario | Existing stable code | Expected behavior |
|---|---|---|
| missing/unknown/mismatched executable identity | `acceptance_configuration_invalid` | fail before Browser navigation; permanent; no target mutation |
| missing/invalid target observer, invalid observation data, or returned-key mismatch | `acceptance_configuration_invalid` | fail closed through `TestContext::observeTarget()`; arbitrary observer exceptions are normalized; no raw target exception |
| opaque target-access handle cannot be resolved by the approved DK boundary | `resource_unavailable` | fail before Browser start; retain no access-bearing value; cleanup any acquired reference |
| account/session reference unavailable | `resource_unavailable` | fail at account stage; cleanup any acquired reference |
| synthetic fixture setup fails | `fixture_setup_failed` | do not execute scenario; cleanup acquired references |
| Browser cannot start | `acceptance_browser_start_failed` | preserve existing retry/admin classification; no raw exception |
| objective step assertion fails | `acceptance_step_failed` | report the bounded failed step; continue only when the approved step is non-critical |
| Browser/probe execution fails | existing `browser_execution_failed` or approved `browser_probe_*` code | no raw Playwright/target error exposure |
| target oracle fails | `oracle_failed` | execution result is not accepted as success |
| cleanup is incomplete | `cleanup_failed` | cleanup failure remains visible and may override apparent success under the accepted lifecycle contract |

No new error code, HTTP response, translation message, exception family, retry rule, or state transition is authorized. If an existing code cannot express a required observable failure, stop and route that contract to the project owner.

### Logging Requirements

No new log event is required. Existing execution logging must retain the full App/Component/Suite/Scenario/Variant tuple and correlation/operation identifiers while omitting target references and sensitive values.

### Traceability Requirements

The exact `AcceptanceExecutionIdentity` must connect selection, target lifecycle, Browser execution, Step results, persisted Test/Run data, CLI output, and coverage mapping. No second independently parsed or inferred identity is allowed.

### Error Handling Test Requirements

Tests must prove every row above at its owning boundary, including failure stage, exact stable code, cleanup behavior, no product mutation, and omission of synthetic sentinels/raw references. Merely asserting that an exception was thrown is insufficient.

### Required Follow-up Updates

- Accepted Run 001 for project Pack 0019 and confirmation that its implemented contract matches this normalized scope — completed 2026-10-11.
- Project Pack 0020 Run 001 acceptance and Pack 0004 source-contract renormalization — completed 2026-10-11.
- Exact runtime-only isolated local target startup/access procedure and constructor-injected DK-owned handle resolver at Pack 0004's pre-execution gate — blocking.
- Fixture lease, target-clock, reverse-cleanup, baseline, ownership, and stable-error lifecycle — resolved by accepted `DK-DECISION-0003` on 2026-10-12.
- Exact recipes for the five health cases and the concrete local synthetic target-control binding/path — blocking.
- Exact observation key, fact/token schema, and stable source-owned selectors — blocking.
- Operator-guide update only if the final local setup changes an operator workflow — conditionally blocking.

## 22. Data Model / Migration / Relationship Requirements

```text
Data / Migration Impact: No TMS schema change
New Tables Required: No
Existing Tables Modified: No
Foreign Keys Required: No
Model Relationships Required: No
Indexes / Unique Constraints Required: No
Data Backfill Required: No
Soft Delete / Retention Impact: No
Audit / History Impact: no product mutation or retained evidence
Rollback Impact: source-only plus cleanup of run-owned synthetic target fixtures
```

No TMS database, migration, model relationship, snapshot, index, constraint, backfill, seeder, factory, or persistent evidence record is created. Synthetic target fixture records, if required by the accepted target integration, are ephemeral run-owned resources and must be fully removed by cleanup.

## 23. Commenting Requirements

Follow `docs/ai/rules/COMMENTING-RULES.md`. Add comments only where the exact variant mapping, no-Provider-I/O boundary, semantic selector rationale, or cleanup ownership cannot be expressed through names/types. Do not copy corpus prose into comments.

## 24. Testing Requirements

```text
Test Quality Required: Yes
Behavioral Assertions Required: Yes
Contract Assertions Required: Yes
Database Assertions Required: No TMS database; target cleanup oracle required
Side-effect Assertions Required: Yes, primarily absence
Negative/Error Scenario Tests Required: Yes
Authorization/Permission Tests Required: exact super-user route access only; broader matrix deferred
Idempotency Tests Required: import replay and cleanup idempotency
Queue/Event/Job Assertions Required: assert none dispatched/released
External Integration Fake/Mock Required: No new fake/mock; block stray external I/O
Security/Secret Masking Tests Required: omission required
```

Tests must follow nearby PHPUnit structure, use descriptive `test_...` names, Arrange/Act/Assert separation, exact `assertSame()`/subject-specific assertions, keyed data providers for uniform variant cases, fixed known expected values, frozen time, and independent setup. Resolver/runtime unit tests must use direct construction with explicit typed doubles; they must not boot the application container or use a service locator merely to obtain dependencies.

### Behavior to Prove

- the exact 21 variants retain distinct typed inputs and expected observable behavior;
- only the selected scenario is executable;
- locale/direction/theme/viewport/focus values reach the Browser context and scenario;
- all five health-evidence cases produce their source-defined objective projection;
- scenario observation occurs only through `TestContext::observeTarget()` with the exact approved observation key and bounded `BrowserTargetObservation` facts;
- fixture/account/oracle/cleanup lifecycle is ordered and fail closed;
- no API, fake expansion, Provider I/O, callback, queue, worker, scheduler, external HTTP, mutation Audit, or retained evidence occurs;
- the other 138 scenarios and the total 139/24,668 catalog/mapping cardinalities remain unchanged.

### Test Data Requirements

Use only immutable synthetic records derived from the 21 accepted variant identities. Time values are offsets from a frozen synthetic clock. Do not use real account/session/URL/recipient/provider values. Cleanup is mandatory for every integration run, including failure paths.

## 25. Acceptance Checklist

- [x] project Pack 0019 is implemented, validated, and Run 001 is accepted;
- [x] project Pack 0020 is implemented, validated, and Run 001 is accepted;
- [x] configuration-surface Remediation 0005 is accepted;
- [x] this Draft is source-contract renormalized against the accepted project Pack 0020 implementation;
- [x] the run-owned fixture lease, target-clock, reverse-cleanup, baseline, and ownership lifecycle is accepted in `DK-DECISION-0003`;
- [x] exact five recipe keys, generic control binding, secret-handle boundary, and bounded observation schema are accepted in `DK-DECISION-0004`;
- [ ] the three enabling Packs are accepted and their exact source signatures, deployment binding, and final Pack-0004 allowlist are incorporated; until then this Draft remains non-executable;
- [ ] exact 21 variants exist once, in deterministic order, with no identity drift;
- [ ] only `nd-component-operations-health` is executable and the other 138 remain non-executable;
- [ ] each selected variant receives its exact identity and Browser context;
- [ ] five component cases and the complete 16-case presentation matrix pass objective assertions;
- [ ] fixture/account/oracle/cleanup lifecycle is synthetic, isolated, ordered, and complete;
- [ ] import replay is idempotent and preserves executable metadata without duplicate managed lines;
- [ ] no API/Fake/Provider/callback/queue/worker/scheduler/external-network scope was added;
- [ ] no product mutation, mutation Audit, unrelated cleanup, or retained evidence occurred;
- [ ] focused tests, affected regressions, full suite, PHP syntax, scoped formatting, safe scans, and diff checks pass;
- [ ] all 21 actual Browser runs pass against the separately approved local synthetic target, or the Run is honestly reported as blocked/incomplete;
- [ ] Run/index lifecycle documentation is synchronized;
- [ ] no implementation commit occurs without separate operator authorization.

## 26. Tests to Add

### `OperationsHealthVariantCatalogTest`

- Purpose: protect the exact bounded variant contract.
- Behavior proven: exact 21-key set/order; five component cases; complete 16-case locale/theme/viewport/focus matrix; super-user persona; health fixture; `provider_io=none`; non-sensitive visual evidence; imported identity/assertion traceability.
- Main assertions: exact keys and typed values; uniqueness; counts by dimension; no unknown field or raw corpus prose.
- Required setup: production catalog/registry plus small typed expected matrix; no Laravel target boot.
- Expected side effects: none.
- Negative/error cases: duplicate, missing, unknown, or mismatched key is rejected.
- Related acceptance criteria: variant identity and bounded scope.

### `NdComponentOperationsHealthTest`

- Purpose: prove scenario metadata, ordered Steps, variant dispatch, objective assertions, and safe failures independently from a real target.
- Behavior proven: selected scenario is Browser-automated/non-sensitive-visual; exact variant context drives viewport/locale/theme/case; critical failures stop appropriately; Step results contain only safe bounded fields.
- Main assertions: exact metadata; exact ordered step names/results/codes; exact `observeTarget()` key and bounded returned facts; no direct runtime/observer access and no mutable/global current variant.
- Required setup: accepted deterministic `TestContext`/Browser/observer doubles and one keyed data-provider case per distinct behavior.
- Expected side effects: none outside the double.
- Negative/error cases: unknown identity, unavailable observation, assertion mismatch, and closed page.
- Related acceptance criteria: executable scenario behavior and safe output.

### `OperationsHealthFixtureTest`

- Purpose: prove the accepted run-owned lease, target-clock, fixture dependency, partial-failure, and cleanup contract without a real target.
- Behavior proven: lease acquisition and bounded baseline precede target clock freeze; provider precedes health evidence; cleanup removes evidence then provider, restores the clock, verifies baseline equality, and closes the lease; all acquired references are reported.
- Main assertions: exact call order and `lifecycleId`; constructor-injected fake control; opaque reference set; `fixture_setup_failed` preserves partial references; cleanup is idempotent only for the matching lease; no broad delete, raw data, Provider, queue, worker, scheduler, or network call.
- Required setup: directly constructed `OperationsHealthFixture` with a small typed fake `DKSyntheticTargetControl`; fixed known clock/case inputs; no Laravel container, Browser, database, or target boot.
- Expected side effects: none outside the fake.
- Negative/error cases: lease conflict, clock-freeze failure, provider failure, evidence failure, ownership mismatch, clock-restore failure, baseline mismatch, cleanup timeout, and repeated matching cleanup.
- Related acceptance criteria: `DK-DECISION-0003`, deterministic fixture lifecycle, failure safety, and complete cleanup.

### `NotificationDeliveryOperationsHealthAcceptanceTest`

- Purpose: prove DK catalog, target lifecycle, Browser executor wiring, traceability, cleanup, and preservation of the remaining corpus.
- Behavior proven: exactly 21 selected variants plan/dispatch; all other scenarios remain non-executable; account/fixture/execution/oracle/cleanup order is correct; failed setup skips execution; cleanup runs after success/failure; no Provider/external side effect exists.
- Main assertions: full tuple at every boundary; exact lifecycle stage/code; exact catalog/mapping counts; unchanged unrelated baseline; zero queue/event/notification/HTTP work.
- Required setup: isolated synthetic adapters for automated tests; separately authorized local target only for the real Browser validation phase.
- Expected side effects: run-owned synthetic fixture resources only, then zero after cleanup.
- Negative/error cases: unavailable account, fixture failure, Browser failure, oracle failure, cleanup failure, stale/mismatched identity.
- Related acceptance criteria: end-to-end bounded execution and lifecycle safety.

### `DKBrowserTargetRuntimeTest`

- Purpose: prove Browser preparation, semantic Operations Health navigation, and bounded target observation without exposing raw target access data.
- Main assertions: the runtime implements both accepted runtime and observer contracts; preparation precedes Page use; only the allowlisted semantic surface opens; the approved observation key returns an exact `BrowserTargetObservation`; fact keys/values remain within the 1–16 bounded contract; unknown surface/observation and invalid final references fail safely; runtime cannot serialize or export raw values.
- Required setup: explicit Browser/Page doubles and inert synthetic reference sentinels; no real target.
- Negative/error cases: missing/duplicate reference, unsupported surface, prepare/open failure, and leakage scan.

### `DKBrowserTargetRuntimeResolverTest`

- Purpose: prove the final provisioned DK `TargetContext` plus the exact validated `PrerequisiteExecutionData` become one per-execution runtime with exact identity and no singleton access-bearing state.
- Main assertions: exact App/Component/Suite/Scenario/Variant validation; required reference types present; the exact prerequisite object reaches the injected owner adapter; a fresh runtime per resolution; no fallback or container lookup.
- Required setup: final synthetic `TargetContext`, inert opaque `app-secret` handle, exact `PrerequisiteExecutionData`, and a constructor-injected fake target-access adapter; no Browser or Laravel target boot.
- Negative/error cases: wrong identity, missing reference, unsafe/unknown reference type, and repeated resolution isolation.

Existing DK catalog/target/component tests must be edited only for the normalized executable contract and must preserve all Pack-0001–0003 assertions.

## 27. Tests to Run

After the blocking DK-local contracts and final exact allowlist are incorporated, Pack acceptance, and separate execution approval:

1. `php -l` for every created/edited PHP file in the final normalized sections 8–9.
2. Scoped `vendor/bin/pint --test` for the same files; any formatting write must stay within that final list.
3. Focused PHPUnit:
   - `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Catalogs/OperationsHealthVariantCatalogTest.php`
   - `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Scenarios/NdComponentOperationsHealthTest.php`
   - `Modules/DK/tests/Unit/Acceptance/Components/NotificationDelivery/Fixtures/OperationsHealthFixtureTest.php`
   - `Modules/DK/tests/Unit/Acceptance/Shared/Target/DKBrowserTargetRuntimeTest.php`
   - `Modules/DK/tests/Unit/Acceptance/Shared/Target/DKBrowserTargetRuntimeResolverTest.php`
   - `Modules/DK/tests/Feature/NotificationDeliveryOperationsHealthAcceptanceTest.php`
   - `Modules/DK/tests/Unit/DKAcceptanceAppTest.php`
   - `Modules/DK/tests/Unit/DKTargetResourceAdapterTest.php`
   - `Modules/DK/tests/Feature/DKTargetFoundationIntegrationTest.php`
   - `Modules/DK/tests/Feature/NotificationDeliveryComponentIntegrationTest.php`
4. Accepted Pack-0020 regression tests, without editing shared source through this DK Pack:
   - `Modules/Core/tests/Unit/BrowserTargetObservationTest.php`
   - `Modules/Core/tests/Unit/TestContextTargetObservationTest.php`
   - `tests/Feature/AcceptanceBrowserRuntimeHandoffTest.php`
5. The focused regression set named by accepted project Pack 0019 when its shared contract is consumed, without editing shared source through this DK Pack.
6. The full PHPUnit suite after focused and cross-owner regressions pass.
7. Guarded CLI list/plan checks restricted to the exact DK Component/Suite/Scenario so the 24,668-item catalog limit is not exceeded.
8. Exactly 21 Browser Acceptance runs against the separately approved isolated local synthetic target, with cleanup verification after every run.
9. Sensitive-value/external-I/O/managed-region/cardinality scans and `git diff --check`.

Do not run browsers, target commands, database/fixture setup, queue workers, servers, Composer/npm, cache/config commands, or external network from this Draft.

## 28. Expected Output

- one executable Browser scenario group with exactly 21 variants consuming the accepted project Pack 0020 contracts;
- five health-evidence cases plus the full 16-case presentation matrix;
- per-scenario metadata and exact variant propagation through an accepted replay-safe shared contract;
- isolated runtime-only synthetic target resources with complete cleanup;
- existing 139 scenarios and 24,668 source mappings preserved;
- 138 scenarios still non-executable;
- DK catalog version incremented once after the executable contract becomes visible;
- no API, fake expansion, Provider/SMS/notification/callback/queue/worker/scheduler, schema, UI source, translation source, or external-network change;
- one Run Report and synchronized indexes only after approved execution.

## 29. Operator Execution Checklist

### Before AI Execution

- Confirm project Change Request 0009 is approved and project Pack 0019 has an accepted valid Run.
- Confirm project Decision 0021 / Change Request 0010 are approved and project Pack 0020 has an accepted valid Run.
- Confirm accepted configuration-surface Remediation 0005 remains unchanged.
- Confirm the exact final file list, constructor-injected `app-secret` handle resolver, runtime-only local synthetic target/session procedure, fixture/frozen-clock/cleanup mechanism, and observation key/fact/token/selector contract without disclosing access-bearing values.
- Confirm Browser access is limited to the isolated local synthetic target and no real/staging/production/provider system is reachable.

### After AI Execution

- Verify the CLI/catalog exposes exactly the selected 21 executable variants and no additional executable ND scenario.
- Verify each of the five health cases and all 16 presentation variants passed with its own identity.
- Verify zero Provider/API/callback/queue/worker/scheduler/external-network activity and zero retained evidence.
- Verify cleanup reports no remaining run-owned resource and unrelated target state matches baseline.

## 30. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md` and include the Documentation Maintenance section from `docs/ai/rules/DOCUMENT-MAINTENANCE-RULES.md`.

Report exact files, prerequisite versions, final scope, 21-variant matrix, target setup classification without values, metadata/identity propagation, lifecycle/error behavior, commands/results, Browser results, cleanup evidence, cardinalities, safe scans, deviations, limitations, lifecycle/index updates, and required follow-ups. Never include target URLs, sessions, credentials, resource references, raw DOM/evidence, or synthetic values that resemble usable access data.

## 31. Review Checklist

- Review ownership and confirm shared changes occurred only in their accepted project-owned Pack.
- Review exact 21-key identity/matrix and assertion traceability.
- Review generated-source replay behavior and absence of duplicate descriptor/resolution lines.
- Review that only one scenario became executable and 138 remain non-executable.
- Review semantic selectors, objective assertions, locale/direction/theme/viewport/focus behavior, and absence of product redesign.
- Review fixture dependency order, frozen clock, oracle, cleanup, baseline equality, and failure precedence.
- Review no API/Fake/Provider/callback/queue/worker/scheduler/external-network expansion.
- Review test value, deterministic data, exact assertions, full-suite result, real Browser evidence classification, and sensitive-output omission.
- Review Run/index synchronization and separate execution/acceptance/commit gates.

## 32. Rollback / Safety Notes

Rollback must be targeted and source-only in TMS: revert only final Pack-owned DK source/test edits, restore only the selected generated descriptor/resolutions through the accepted generator/import mechanism, restore the DK catalog version, and synchronize lifecycle indexes. Do not use broad Git reset/restore/clean commands.

Every interrupted target run must execute bounded cleanup for its acquired references. Never delete by broad query, unresolved namespace, glob, or non-run-owned identifier. If cleanup cannot prove ownership, stop and report `cleanup_failed` rather than deleting uncertain data.

No database migration or persistent evidence rollback exists.

## 33. Stop Conditions

Stop before Pack acceptance or execution if:

- project Pack 0019 lacks an accepted Run or its implemented contract changes the assumptions in sections 8–10;
- accepted project Pack 0020 source no longer matches the resolver/observer assumptions recorded here;
- any enabling Pack is unaccepted, its actual implementation contradicts Decisions 0022/DK 0003–0004, the deployment binding cannot be verified safely, or the final exact create/edit allowlist cannot be derived from the accepted implementations;
- accepted configuration-surface files overlap this Pack or would need modification;
- exact variant identity or safe target context cannot reach scenario execution without static/global state or manual managed-region edits;
- importer replay would duplicate, overwrite, or demote the selected executable scenario;
- any scenario/variant outside the exact 21-key set must change;
- any API, fake expansion, Provider, SMS, notification, callback, queue, worker, scheduler, schema, UI source, translation source, or external-network capability becomes necessary;
- any target/data/value is real, staging, production, shared, remote, retained, or not demonstrably inert and synthetic;
- a real URL, account, credential, token, cookie, session, recipient, payload, personal identifier, or raw target reference would enter source, config, docs, output, logs, reports, or Test/Step persistence;
- cleanup cannot identify and remove only run-owned resources or cannot restore the frozen clock/baseline;
- an existing stable error code cannot express the observed failure safely;
- unrelated working-tree changes overlap a final allowed file;
- a destructive, dependency-changing, environment-reading, broad formatting, database-changing, cache-changing, or unapproved browser/target command appears necessary.

## 34. Open Questions

Blocking before execution:

1. Accepted implementation evidence and exact version/capability signatures from Project Pack 0021, the target-project Acceptance Control Agent Pack, and the target ND Driver Pack.
2. Verified deployment binding for the non-production HTTPS endpoint, secret provider, isolated target/database, and Browser lease-context handoff, recorded without values.
3. Final exact sections 8–9 allowlist normalized to the accepted implementations.

Resolved for normalization:

- Change Request/Pack identifiers: project CR 0009 and Pack 0019.
- Safe shared shape: exact identity in `TestContext`; target access only through a per-execution `BrowserTargetRuntime` resolved from final `TargetContext` plus exact validated `PrerequisiteExecutionData` by the App-keyed resolver registry; scenario observation only through `TestContext::observeTarget()` and bounded `BrowserTargetObservation`.
- Missing shared capability routing: resolved and accepted through Decision 0021, Change Request 0010, and project Pack 0020 / Run 001.
- Pack 0004 source-contract renormalization against the accepted resolver/observer implementation: completed 2026-10-11.
- Run-owned fixture lease, target-clock, reverse-cleanup, baseline verification, ownership, partial-failure, and stable-error lifecycle: accepted in `DK-DECISION-0003` on 2026-10-12.
- Generic target-control/DK adapter binding, five recipe keys, just-in-time secret-handle resolution, exact observation key, and sixteen-fact ceiling: accepted in `DK-DECISION-0004` on 2026-10-12.
- DK adapter paths: `DKBrowserTargetRuntime` and `DKBrowserTargetRuntimeResolver` under `Acceptance/Shared/Target`.
- Configuration: accepted Remediation 0005 and the existing `core.acceptance.*` surface.

Deferred by `DK-DECISION-0002`, not blocking this Draft:

- API/HTTP scenario groups;
- ND fake expansion or new fake behaviors;
- additional bounded ND scenario groups;
- stage-15 full coverage reconciliation.
