# ND CORPUS MANIFEST 0001 — Human Acceptance Contract v1

Status: active reference — corpus intake and Decision-0017 mapping normalization completed; Pack 0003 applied/replayed all 25 verified chunks, passed post-Remediation-0003 runtime catalog/coverage validation, and was accepted with Run 001 on 2026-10-11

Owner: DK (consumer reference)

Upstream owner: Notification Delivery

Related Pack: `Modules/DK/docs/ai/packs/AI-PACK-DK-NOTIFICATION-DELIVERY-CORPUS-IMPORT-0003.md`

## Purpose

This document is the portable, immutable locator and integrity record for the Notification Delivery Human Acceptance Contract consumed by DK stage 13.

The raw corpus remains in its upstream repository. It is intentionally not copied into TMS because it is upstream-owned, totals 208,690,528 bytes, and would create a second source of truth. TMS may commit only this reference and later DK-derived mappings, tests, generated skeletons, and lifecycle evidence approved by the normalized Pack.

Execution lifecycle note: all recorded chunks were regenerated with matching size/hash metadata, dry-run, applied in order, and replayed with zero creates/updates. The resulting 24,668 mappings and 139 skeletons passed runtime catalog and coverage validation after the operator-approved exact descriptor imports in project Remediation 0003. The operator accepted Pack 0003 and Run 001 on 2026-10-11. Shared importer/validator prevention remains open in Change Request 0007.

## Source Identity

| Field | Value |
|---|---|
| Logical source repository | `dk` |
| Pinned source repository commit | `7ec4b4dc8573f24252175bf9e1a21364b0a48d9c` |
| Corpus root relative to source repository | `platform/plugins/notification-delivery/docs/ai/testing/contracts/human-v1` |
| Contract version | `1.0.0` |
| Schema version | `1` |
| Source snapshot ID | `nd-0ef0b92d9192d0162060d31efe08f55552b084f7-tms-8fd3b427397a116b7e97f87606f6f1ecab470de0` |
| ND runtime snapshot commit | `0ef0b92d9192d0162060d31efe08f55552b084f7` |
| TMS consumer snapshot commit encoded by the export | `8fd3b427397a116b7e97f87606f6f1ecab470de0` |
| Corpus artifact count | `22` files |
| Corpus byte size | `208,690,528` bytes |

The pinned source repository commit and repository-relative corpus root form the normative locator. An operator-local checkout was used for validation, but its absolute filesystem path is intentionally not authoritative or recorded here.

## Authority and Lifecycle

- The upstream Human Acceptance Contract export was accepted under Notification Delivery Remediation 0063 / Change 0067 and its accepted Run 001.
- The corpus is a requirements export. It does not claim that human tests were executed or that TMS executable coverage exists.
- The operator approved indexing this immutable external source by manifest instead of copying its raw files into TMS.
- Temporary project Decision 0017 accepts the local, single-operator, wholly synthetic risk boundary used by the current mapping assessment. It does not authorize Pack execution or real information.
- This reference does not accept Pack 0003 for execution and does not authorize implementation, import, target access, or commit.

## Verified Cardinality and Source Classification

| Measure | Verified value |
|---|---:|
| Scenario definitions / use cases | 139 |
| Scenario variants / source cases | 24,668 |
| Fixtures | 69 |
| State/action matrix entries | 1,042 |
| Upstream `not-implemented` automation status | 14,877 |
| Upstream `manual-only` automation status | 7,822 |
| Upstream `blocked` automation status | 1,969 |

The three upstream automation statuses total 24,668. They are upstream planning metadata and must not be translated directly into the five TMS Decision-0012 coverage dispositions. That translation requires explicit assertion-level analysis and accepted evidence.

All 24,668 `variant_id` values and all 24,668 `scenario_key` values are present. The measured maximum lengths are 82 and 63 characters respectively; none exceeds the current TMS limits of 128 for source-case IDs and 64 for scenario keys. This length check does not prove semantic uniqueness of derived TMS hierarchy tuples or PHP class names.

## Validation Evidence

The upstream offline validator was run against the pinned source checkout:

```text
php platform/plugins/notification-delivery/docs/ai/testing/inventory/validate-human-acceptance-contract.php --contract=platform/plugins/notification-delivery/docs/ai/testing/contracts/human-v1 --source-root=.
```

Result: exit code `0`.

```text
HAC-V05 consumer-context contract_consumer_not_verified
CONTRACT human-v1 contract_structural_integrity_valid definitions=139 variants=24668 fixtures=69
REVIEW HAC-V09..14 contract_expected_outcomes_and_source_universe_require_independent_review
```

Consumer-aware validation against the current TMS checkout does not pass. It reports `HAC-V05 source-manifest/snapshots/tms contract_current_commit` because the export pins TMS commit `8fd3b427397a116b7e97f87606f6f1ecab470de0`, while the current TMS checkout is newer. This is an expected but unresolved compatibility gate, not evidence that the corpus itself failed structural validation.

The validator script passed PHP syntax validation. Its pinned metadata is:

| Artifact | Bytes | SHA-256 |
|---|---:|---|
| `platform/plugins/notification-delivery/docs/ai/testing/inventory/validate-human-acceptance-contract.php` | 132,079 | `73e8e102190127f464431203c2f36aac83d678f68695b590768ea75c175421eb` |

## Corpus Integrity Manifest

All paths below are relative to the corpus root declared under Source Identity.

| Relative path | Bytes | SHA-256 |
|---|---:|---|
| `evidence-policy.json` | 5,724 | `02db6931f2e0833b0e736d97756120f65abeb24122eaee57a9741def1f8ad19c` |
| `fixtures.json` | 167,705 | `adf4a10e94851550c6038e44be2c926806d7d701ab18f8f7dcf4cb1b00511539` |
| `inventory.json` | 2,063,434 | `d648049aa618ec8b4dbd05a99ac0423c38b4566bc7eccb0ff7ae532b4bb202ef` |
| `personas.json` | 848,387 | `3c0ab73c72bdf4cccf01b754274251e50e2d5ff2f35bb7768d2a9c4372787b5d` |
| `README.md` | 22,934 | `c340f4aa26de6e262a14e22eef371d6e692bddb61f59f4a40e490e053e240cec` |
| `scenario-definitions.json` | 2,643,050 | `794951139d88164dfd2713c05b3335b208f7ad6e125ca5daf2d389d9cfa88883` |
| `scenario-variants.json` | 163,354,333 | `04ba1db902f8eafb68367d66bb4a31161e37c37162dbbef44af8647805ed51f7` |
| `schemas/common.schema.json` | 881 | `59cf510c494cc748eb323c1d81fcc9bf33336f3bb9b719033daa8ef718b8271a` |
| `schemas/evidence-policy.schema.json` | 3,265 | `afd09b71e10904c07c200cbbb1016a8233e51c014eb4f024191a174255ac08b2` |
| `schemas/fixtures.schema.json` | 5,252 | `a93e843223e140197b477fc5c2d711b09940b558b9923cb9cc0fd838c74a1057` |
| `schemas/inventory.schema.json` | 32,804 | `1efb2c189dfd7b5c249ae3b3aaaeeab485bf8e4f1b0b225d737e1c595ba2406a` |
| `schemas/personas.schema.json` | 6,304 | `43909845bec3bfaa061d06968b15e2c5a75835522b9cd62e4d3af94574c943e1` |
| `schemas/scenario-definitions.schema.json` | 12,449 | `0a5cc8a4f914c5ee74a66de17d8138d19710a4eea9157bf179c575689a2ccea9` |
| `schemas/scenario-variants.schema.json` | 32,978 | `a50340229d8fbb7ce0dcad67849fb973117f93abd14bb7d09315b4a846d3b740` |
| `schemas/source-manifest.schema.json` | 12,017 | `8ec6593012afdd44de6c64ed51c4e899cafa3363e5508c4fc3df12eceec5e7c2` |
| `schemas/state-action-matrix.schema.json` | 18,281 | `946464b140ccf6e110b9afad926f165e6a33091b79db3ac3296c74a5122f0cb3` |
| `schemas/surfaces.schema.json` | 5,249 | `b8beef834aeca67bcb5d9675f815a3067b8cdf169beb24e518732fce1af20e5f` |
| `schemas/traceability.schema.json` | 29,305 | `388f4792edaa93432bba329fb8eb170d89e73a580aca06ea01a9d0baa66e3079` |
| `source-manifest.json` | 2,000,729 | `e9b5b30329e86169a8361a8a9d364ceff33b078f0b5966c61857a969f95ff772` |
| `state-action-matrix.json` | 24,267,004 | `b40fe720078e834071eaac62ad8505bb9fc7519ee98a6cdfb99d8fca28548b70` |
| `surfaces.json` | 887,104 | `983d428e9ac309f657c13756c6d3c2dcff9824138bcd57f7536d7989ada73cc5` |
| `traceability.json` | 12,271,339 | `5928d7de38646fcbfacf48415095c5036e30db79bf728960cb37df594538d056` |

## Consumption Rules

1. Resolve the logical source repository at the pinned commit and use only the repository-relative corpus root.
2. Verify every listed byte size and SHA-256 before analysis and again before any approved import execution.
3. Do not copy the raw corpus into TMS, commit it as fixtures, or create a second authoritative corpus representation.
4. Do not treat upstream `automation_status` values as TMS coverage dispositions.
5. Do not copy raw prose, target details, selectors, URLs, payloads, personal data, or other potentially sensitive/free-form fields into TMS-derived artifacts when stable safe IDs suffice.
6. Preserve `scenario_key`, `variant_id`, and assertion-level traceability while deriving DK-owned mappings.
7. Do not access a real target, browser, provider, network endpoint, queue worker, database, environment file, credential, or production data.
8. Re-run compatibility analysis if the pinned corpus, current TMS mapping contracts, importer, or executor capabilities change.
9. Stop and review Decision 0017 before using any real/non-synthetic data, second user, remote/shared access, real target/provider, retained evidence, external executor, production or staging environment.

## Current-TMS Compatibility and Mapping Analysis

The export's TMS snapshot predates the current Browser/HTTP executor contracts, fixed Browser observations, source-case mapping contract, importer, and DK Acceptance source. The upstream `manual-only` reason therefore describes the old consumer snapshot, not current TMS capability.

All 96 distinct assertion texts used by the 83 definitions containing `manual-only` variants are objective. None of those definitions contains a touch or contrast assertion. Current TMS provides Browser visibility, layout, focus, keyboard, scroll, semantics, media, and viewport observations plus Browser/HTTP executor routing. These 7,822 variants are therefore direct automation candidates rather than human-judgment or no-executor exclusions.

The 1,969 upstream `blocked` variants all use `sensitive-no-capture`. Current TMS still has no policy-enforced sensitive execution adapter that constrains all `RunOptions::contextOptions` and evidence channels. Temporary project Decision 0017 accepts that residual risk only for the local, single-operator, wholly synthetic, fake-provider-only boundary and keeps real information prohibited. Inside that complete boundary, absence of generalized capture/telemetry/retention suppression is not by itself a valid no-executor exclusion. The variants remain objectively testable with inert synthetic values and are direct automation candidates. Crossing any Decision-0017 trigger invalidates this assessment and blocks execution pending a dedicated security Pack.

| TMS disposition | Count | Rule |
|---|---:|---|
| `automated_full` | 24,668 | Every upstream variant; current capabilities cover the objective contract, and Decision 0017 temporarily accepts the synthetic-only residual risk for the former `sensitive-no-capture` group. |
| `automated_partial` | 0 | No variant has a proven assertion split after current-capability review. |
| `merged_equivalent` | 0 | All 24,668 semantic signatures remain unique when `tuple_values` are included. |
| `excluded_no_reliable_executor` | 0 | No variant has a remaining executor blocker inside the complete Decision-0017 boundary. |
| `excluded_human_judgment` | 0 | The corpus contains no subjective assertion requiring a human TMS test. |
| **Total** | **24,668** | Exactly one disposition per source case. |

The 24,668 source `variant_id` values remain unchanged as `sourceCaseId`. Direct TMS identities use:

```text
componentKey = notification-delivery
suiteKey     = parent definition suite
scenarioKey  = source scenario_key with every "." replaced by "-"
variantKey   = "v." + the 16-hex suffix after the source variant_id final ".v."
```

Direct use of dotted source scenario keys is unsafe because the current importer passes the key to `Str::studly`; for example, it would produce the invalid PHP class name `Nd.component.attempts.show`. Dot-to-hyphen normalization yields 139 unique valid TMS scenario keys, 139 unique valid PHP class names, and no path/class/direct-identity collision. All 24,668 direct variant keys are valid and unique.

## Assertion-ID Projection

Each mapping's assertion universe is the union of its parent definition assertions and its variant-specific expected assertions:

- every `steps[].assertions[]`, namespaced by `step_id`;
- every `validation_obligations[]`, `expected.ui[]`, `expected.state[]`, positive/negative side effect, `expected.audit`, `expected.log`, `expected.error`, `security[]`, and `cleanup[]` value;
- variant `expected.outcome`, `expected.error_class`, `expected.state`, `expected.side_effects[]`, and `expected.no_side_effects[]` values.

For each assertion, frame every identity segment as `<UTF-8-byte-length>:<exact-segment-bytes>`, concatenate the frames, hash with SHA-256, and emit `a.` plus the first 20 lowercase hexadecimal characters. Definition IDs use `scenario_key`, semantic field/step slot, and exact assertion text. The exact definition slots are `steps.<step_id>.assertions`, `validation_obligations`, `expected.ui`, `expected.state`, `expected.positive_side_effects`, `expected.negative_side_effects`, `expected.audit`, `expected.log`, `expected.error`, `security`, and `cleanup`. Variant-expected IDs use `variant_id`, one of `expected.outcome`, `expected.error_class`, `expected.state`, `expected.side_effects`, or `expected.no_side_effects`, and the exact value. Remove duplicate IDs and sort with binary string order.

The projection produced 150,724 unique assertion IDs with zero truncated-hash collision. Each source mapping contains 21–64 IDs, below the `SourceCaseMapping` limit of 256. The 24,668 full mappings contain 767,744 covered-assertion references. There are zero uncovered-assertion references and no exclusion reason.

## Deterministic Mapping Chunks

Mapping records omit nullable/default fields, use canonical field order, and are sorted by binary `source_case_id`. Each chunk is one compact UTF-8 JSON array encoded with unescaped slashes and Unicode. A record is added only when the resulting chunk remains strictly below 1,048,576 bytes.

| Chunk | Records | Bytes | SHA-256 | First source case | Last source case |
|---:|---:|---:|---|---|---|
| 001 | 901 | 1,047,589 | `7f6ad1db3a35afb7f2ea0762a855bdfe8fb667ff8623c68166990594fd78ddd1` | `nd.component.attempts.show.v.0ac4e2627f2daf8f` | `nd.component.css.admin-form.v.bb5fdacdbac2ac62` |
| 002 | 1,000 | 1,047,898 | `35c791ce8f970b5d5203671b043e277da9e9024a318956e4f354e549da2b5ac1` | `nd.component.css.admin-form.v.bb6617b99ed56c92` | `nd.component.css.contextual-help.v.542919691e8bb0a1` |
| 003 | 1,017 | 1,048,417 | `586d6258c5a160fbfb43f41fde4fcd62761d1bbfd76860b8adc5fed51a6bb31b` | `nd.component.css.contextual-help.v.542f01f59da5cf2a` | `nd.component.css.detail-pages.v.f88e72431f76b7ad` |
| 004 | 1,005 | 1,047,543 | `e266154d2a19f063900a6d88861240704258852316db796256f4b93143b42d02` | `nd.component.css.detail-pages.v.f98977c0e376e36a` | `nd.component.css.provider-form.v.f1a8667860976fcc` |
| 005 | 921 | 1,048,096 | `aa36e6e3a4f9344755c3a9217fa175020b34807fcc9baf8b3f85d081304f97c0` | `nd.component.css.provider-form.v.f255986b52fa18a7` | `nd.component.js.contextual-help.v.746ff03b1fe0a236` |
| 006 | 979 | 1,048,010 | `2b336c6c1b71a48891df0674dd7310a4da211b46fe80d3a90e91092bd5f8ca77` | `nd.component.js.contextual-help.v.7499d2d436aed86d` | `nd.component.js.navigation-sections.v.cecc5f6f14ff9dd6` |
| 007 | 979 | 1,048,199 | `f47388d57721c0508aa2354a31fcda40c821f4b84a75206a7dce8432b6e33993` | `nd.component.js.navigation-sections.v.ceeafd61c696b377` | `nd.component.shared.contextual-help.v.349a74f5ec9f1e10` |
| 008 | 965 | 1,048,570 | `195119d3fe20102fecc3cd2ed57fc6bb1283b6c1d318bd45d9d93e6c754b80e4` | `nd.component.shared.contextual-help.v.34c03d4a44e6a89a` | `nd.component.tables.filter.v.17a4e65131cffc0c` |
| 009 | 1,140 | 1,047,832 | `e6a27ad5d55a1983d638490bbfe9c482b6736491a345f8063ae5c8c2df3abbef` | `nd.component.tables.filter.v.18d0a300cd77643f` | `nd.delivery.callbacks.index.v.3f89b9d65f203029` |
| 010 | 1,084 | 1,048,360 | `6fcc483325bba01d710473a8a17939cbd2fb590d321b824706edb49416022bca` | `nd.delivery.callbacks.index.v.412be2b87d61bbc4` | `nd.delivery.dead-letters.retry.v.4d53ea8c4a6e8ad5` |
| 011 | 1,001 | 1,047,596 | `31e89652a9480a50c5a0150553c762e8609c76b0a65e0b73fabab5b63ba57a7c` | `nd.delivery.dead-letters.retry.v.4dff246216a7ba7c` | `nd.delivery.messages.cancel.v.c442c28d9956e3eb` |
| 012 | 987 | 1,048,436 | `cb580b5dc8998ae4ab8c0f62be63c225df0de781c4cc691c0883120461b9a4cb` | `nd.delivery.messages.cancel.v.c44dcc290c96fb47` | `nd.delivery.messages.retry.v.248d2751c05ef57d` |
| 013 | 917 | 1,048,024 | `d5fc1682ebfef9029ac326af6cc2eb39272f369c112503e771f070690478042e` | `nd.delivery.messages.retry.v.24a613a941f7febe` | `nd.delivery.messages.retry.v.5a952e772ee5c0cd` |
| 014 | 917 | 1,047,624 | `2dc4acf12e682e5697afd1627e1678f4c3032eb0c2b79a7d8d79f6ea55edd61f` | `nd.delivery.messages.retry.v.5a9c857bb1e504dd` | `nd.delivery.messages.retry.v.8fec2a055e90aa9e` |
| 015 | 917 | 1,047,449 | `347a684641938476ab32fdb03b3b9ac4660203653944cb67a50a6864585293ea` | `nd.delivery.messages.retry.v.8ff48b4a6ceab35c` | `nd.delivery.messages.retry.v.c30ddbf580c572e1` |
| 016 | 917 | 1,047,899 | `61d267ad0ad68ac4939f28cfbc61cece9877f46786118201a35ca98a065b13eb` | `nd.delivery.messages.retry.v.c32fa9325f674c69` | `nd.delivery.messages.retry.v.fccf4b3db783a3c4` |
| 017 | 971 | 1,047,319 | `aa704162109c5eac955ff547879337eaf74156e64d045ad8ddaebb10d79ae31c` | `nd.delivery.messages.retry.v.fcd62aa2bad1c74f` | `nd.delivery.settings.update.v.7fecf40cd580b133` |
| 018 | 949 | 1,048,180 | `e19218d7c944e5094cb3f1ada08ac23ae7244dc8ce1d6a25cbacbfaf204f46b0` | `nd.delivery.settings.update.v.8118dac40d0eb5ed` | `nd.provider-methods.test-form.v.3588792fc618eb3a` |
| 019 | 1,143 | 1,048,195 | `f4adac8553fd553963687bb58eaf23fc854da327d5ba4dd45dffabc96e2d4078` | `nd.provider-methods.test-form.v.35a3667f2885e309` | `nd.provider-methods.test-send.v.2e66460b94c6c9b4` |
| 020 | 1,074 | 1,048,247 | `6cce6347e82fd31e6d2fd838cfd9365b03eb5b5c538100ebf3240136a7e40e1f` | `nd.provider-methods.test-send.v.2e77807620e9f667` | `nd.providers.ajax.v.a5b0cafb3e3f04d0` |
| 021 | 1,059 | 1,047,875 | `eaef7b24796b09841231472bfaeda99dd925bee01062509cb7b9c3fb0de61900` | `nd.providers.ajax.v.a7092c48e4e6e8b8` | `nd.routing-rules.preview.v.36f09d864b3be254` |
| 022 | 1,036 | 1,047,792 | `a1f045998dc5a5985b5499b946691fb6f1716fd5580059dbfde82900567c454c` | `nd.routing-rules.preview.v.39e42d2b12e057d5` | `nd.sender-lines.test-send.v.0aaf8770c1fe2c67` |
| 023 | 1,017 | 1,048,298 | `d064e7d08cc92e56cf5ab90d6431a9718587a54d8408431e4b1a71dcd6d27f37` | `nd.sender-lines.test-send.v.0aeda7aaaa3fd2d2` | `nd.source-services.review.v.8cf2d207848903fe` |
| 024 | 1,029 | 1,048,540 | `a6267e9422c78de207c85b2ff909f0d4da71e981d29ceaab88826beeceaca587` | `nd.source-services.review.v.8e71381e297dd9db` | `nd.templates.test-send.v.49e47eb526b39c3e` |
| 025 | 743 | 826,947 | `3d1463110549877ae385d9740e9bc810b02fe67cd0da2fbd5128688fb4c1bbee` | `nd.templates.test-send.v.4a2ebc539f569a83` | `nd.upstream.session.v.a905989b5dbb0478` |

The 25 chunk counts sum to 24,668. Chunk JSON is a transient execution input and must not be committed as a second mapping registry.

## Scenario-Variants Structure Assessment

Verdict: the v1 structure is strong as a closed, frozen, ND-specific requirements export, but it is not a good generic standard for unrelated contracts or future variant families without a versioned redesign.

Strengths:

- versioned envelope, immutable source snapshot, closed schemas, typed joins, stable parent/variant IDs, explicit dimension tuples, fixtures, personas, expected effects, safety policy, and provenance;
- complete parent-to-variant joins, one matrix and tuple reference per variant, and no semantic duplicate when `tuple_values` are included;
- objective expected outcomes are separated from execution evidence and no human execution is fabricated.

Generic-reuse limitations:

- the single variant file is 163,354,333 bytes (about 6,622 bytes per variant), making review, diffing, streaming, and bounded import unnecessarily expensive;
- all 34 variant fields are required even when empty or invariant, causing heavy repetition;
- the schema embeds 363 ND-specific `input_class` enum values and 68 fixed `tuple_values` properties, so another contract changes the schema rather than supplying a new dimension registry;
- `variant_id`, refs, reasons, and many semantic strings permit up to 20,000 characters; `variant_id` has no schema pattern, arrays generally have no `maxItems`, and viewport has no upper bound;
- the JSON Schema has no conditional status rules; relationships among `safe_to_automate`, status, and reasons depend on the separate validator;
- assertions are free-form strings without stable IDs or declared required executor/oracle capabilities;
- automation assessment is mixed into the source variant. Current TMS drift proved that this metadata ages independently from the behavior contract.

Recommended successor shape:

1. Keep invariant behavior in a Contract definition with stable `contract_id`, version, steps, and structured assertion objects containing `assertion_id`, oracle, required capability, and expected observable.
2. Keep each Variant small: `variant_id`, `contract_ref`, dimension assignments, fixture/persona refs, and explicit overrides only.
3. Move dimensions and allowed values into a contract-owned registry instead of a global hard-coded `input_class` enum and fixed tuple property dictionary.
4. Move consumer-specific automation disposition and evidence assessment into a separate versioned coverage-assessment artifact.
5. Align key/reason bounds with consumers, add `maxItems`, conditional invariants, and bounded viewport dimensions.
6. Shard variants by contract/scenario or use a line-oriented canonical format with a hash manifest so consumers can stream and review bounded units.
7. Preserve closed schemas, immutable provenance, typed references, exact tuple coverage, and the current safety/evidence separation.

Pack 0003 may consume frozen v1 through the deterministic projection above. The upstream v1 files must not be edited in place; generic improvements require an upstream versioned successor.
