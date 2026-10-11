# REMEDIATION-PACK-TMS-CONFIGURATION-SURFACE-0005

Status: `accepted — implementation and validation passed; operator accepted and authorized commit 2026-10-11`

Generated: `2026-10-11`

Owner: TMS project (repository-wide configuration contract with Core and DK read-only ownership evidence)

## 1. Task ID

`REMEDIATION-PACK-TMS-CONFIGURATION-SURFACE-0005`

## 2. Task Title

Complete and verify first-party configuration and environment declarations.

## 3. Related Change Request

`docs/project/ai/changes/CHANGE-REQUEST-TMS-CONFIGURATION-SURFACE-0008.md`

Decision source: `docs/project/ai/decisions/TMS-DECISION-0020-configuration-surface-remediation.md`.

## 4. Goal

Make the versioned repository configuration surface complete and self-checking: every active first-party configuration read must resolve through an owned config source, and every active first-party `env()` key must have a safe, default-preserving declaration in `.env.example` plus an operator-only `.env` synchronization instruction.

## 5. Current Problem

At generation time, the repository had no automated contract tying first-party `config()`/configuration-repository reads to their owning config providers or tying active `env()` calls to `.env.example`.

Generation-time inspection on 2026-10-11 found:

- referenced roots: `acceptance`, `app`, `core`, `database`, `dk`, `modules`, `queue`, and `view`;
- no currently unresolved root after root config, owner-local providers, Laravel, and `nwidart/laravel-modules` are considered;
- 138 distinct active first-party `env()` keys in versioned config;
- 81 active keys absent from `.env.example`;
- no active first-party `env()` call outside config;
- `modules` and `view` are dependency-provided and must not be treated as absent merely because root `config/modules.php` and `config/view.php` do not exist;
- a pre-existing untracked `config/core.php` and modified `phpunit.xml` belong to other/current operator work and must remain unchanged.

After generation, the operator explicitly approved adopting and completing `config/core.php`, synchronizing the module Core config, and adding their four safe defaults to `.env.example`. That exact scope supersedes the earlier `config/core.php` exclusion; `phpunit.xml` remains protected.

The 81 declarations missing in the generation baseline were:

```text
ACCEPTANCE_HEADLESS
APP_PREVIOUS_KEYS
AUTH_GUARD
AUTH_MODEL
AUTH_PASSWORD_BROKER
AUTH_PASSWORD_RESET_TOKEN_TABLE
AUTH_PASSWORD_TIMEOUT
AWS_ENDPOINT
AWS_URL
BEANSTALKD_QUEUE
BEANSTALKD_QUEUE_HOST
BEANSTALKD_QUEUE_RETRY_AFTER
DB_CACHE_CONNECTION
DB_CACHE_LOCK_CONNECTION
DB_CACHE_LOCK_TABLE
DB_CACHE_TABLE
DB_CHARSET
DB_COLLATION
DB_FOREIGN_KEYS
DB_QUEUE
DB_QUEUE_CONNECTION
DB_QUEUE_RETRY_AFTER
DB_QUEUE_TABLE
DB_SOCKET
DB_SSLMODE
DB_URL
DYNAMODB_CACHE_TABLE
DYNAMODB_ENDPOINT
LOG_DAILY_DAYS
LOG_DEPRECATIONS_TRACE
LOG_PAPERTRAIL_HANDLER
LOG_SLACK_EMOJI
LOG_SLACK_USERNAME
LOG_SLACK_WEBHOOK_URL
LOG_STDERR_FORMATTER
LOG_SYSLOG_FACILITY
MAIL_EHLO_DOMAIN
MAIL_LOG_CHANNEL
MAIL_SENDMAIL_PATH
MAIL_URL
MEMCACHED_PASSWORD
MEMCACHED_PERSISTENT_ID
MEMCACHED_PORT
MEMCACHED_USERNAME
MYSQL_ATTR_SSL_CA
PAPERTRAIL_PORT
PAPERTRAIL_URL
POSTMARK_API_KEY
QUEUE_FAILED_DRIVER
REDIS_BACKOFF_ALGORITHM
REDIS_BACKOFF_BASE
REDIS_BACKOFF_CAP
REDIS_CACHE_CONNECTION
REDIS_CACHE_DB
REDIS_CACHE_LOCK_CONNECTION
REDIS_CLUSTER
REDIS_DB
REDIS_MAX_RETRIES
REDIS_PERSISTENT
REDIS_PREFIX
REDIS_QUEUE
REDIS_QUEUE_CONNECTION
REDIS_QUEUE_RETRY_AFTER
REDIS_URL
REDIS_USERNAME
RESEND_API_KEY
SCRIBE_AUTH_KEY
SESSION_CONNECTION
SESSION_COOKIE
SESSION_EXPIRE_ON_CLOSE
SESSION_HTTP_ONLY
SESSION_PARTITIONED_COOKIE
SESSION_SAME_SITE
SESSION_SECURE_COOKIE
SESSION_STORE
SESSION_TABLE
SLACK_BOT_USER_DEFAULT_CHANNEL
SLACK_BOT_USER_OAUTH_TOKEN
SQS_PREFIX
SQS_QUEUE
SQS_SUFFIX
```

This list is baseline evidence, not permission to add commented/non-executable references. Approved execution must recompute it from active PHP tokens and reconcile any legitimate source changes made before execution.

Core configuration slice execution evidence on 2026-10-11:

- `config/core.php` and `Modules/Core/config/config.php` are byte-identical and expose the complete approved Core config contract;
- `.env.example` declares all four approved Core browser variables with safe defaults;
- the active first-party env inventory is now 141 keys, with 80 declarations still missing from `.env.example` for the broader remediation;
- both Core config files passed `php -l`;
- `AcceptanceRunCommandTest::test_omitted_runtime_options_use_configured_defaults` passed with 10 assertions;
- no active first-party `env()` call was found outside config paths;
- `git diff --check` passed;
- `.env`, `phpunit.xml`, dependency config, databases, queues, caches, and external systems were not changed by this slice.
- the operator accepted this Core configuration slice on 2026-10-11; no commit was authorized.

Environment-manifest execution evidence on 2026-10-11:

- the operator authorized adding the remaining declarations to `.env` and `.env.example`;
- the repository profile prohibition on AI access to `.env` was applied, so only `.env.example` was changed and `.env` remains an operator-only manual action;
- the recomputed comment-aware inventory contains 140 distinct active first-party env keys;
- `.env.example` now represents every active key exactly once: zero missing declarations and zero duplicate declarations;
- fixed safe scalar fallbacks are enabled, while optional, sensitive, driver-dependent, and computed values remain null-safe or commented examples;
- the committed example parses successfully with `Dotenv\\Dotenv::parse` and exposes 104 enabled assignments;
- no real secret or environment-specific value was added;
- `git diff --check` passed;
- `tests/Feature/ConfigurationSurfaceTest.php` now enforces manifest completeness/uniqueness, config-only environment reads, required namespace resolution, dependency-config non-publication, and sensitive placeholder safety;
- the first focused run exposed a 512 MB memory exhaustion after one passing test because the scanner tokenized a very large first-party generated mapping; the bounded correction prefilters files without an `env()` call and excludes `bootstrap/cache` artifacts;
- the final focused run passed 4 tests and 19 assertions using 30 MB;
- `AcceptanceRunCommandTest::test_omitted_runtime_options_use_configured_defaults` passed again with 10 assertions;
- PHP syntax checks and scoped Pint validation passed;
- the operator accepted the result and separately authorized commit on 2026-10-11.

## 6. Required Change

1. Add the missing active keys to `.env.example`, grouped beside their owning subsystem.
2. Preserve effective defaults:
   - use an enabled assignment only when a safe scalar value exactly matches the existing config fallback;
   - use `null`, an empty safe placeholder, or a commented example according to the existing semantics;
   - do not enable an empty declaration when absence triggers a computed fallback;
   - never include a real secret, token, credential, production URL, target identifier, personal value, or runtime payload.
3. Add `tests/Feature/ConfigurationSurfaceTest.php` to enforce active-token env coverage, absence of first-party `env()` outside config, boot-time resolution of required roots/keys, and safe treatment of sensitive examples.
4. Verify `modules` and `view` from their installed providers; do not publish or copy dependency config.
5. If a genuinely unresolved first-party root appears, stop. Amend and re-approve this Pack with its exact owner and file path before creating it.
6. Produce a manual operator checklist containing the reviewed `.env` additions. The AI Agent must not read, diff, write, or validate `.env`.
7. Complete root and module Core config with synchronized `name`, `browser`, `headless`, `timeout_ms`, and `slow_mo_ms` values backed by `ACCEPTANCE_BROWSER`, `ACCEPTANCE_HEADLESS`, `ACCEPTANCE_BROWSER_TIMEOUT_MS`, and `ACCEPTANCE_BROWSER_SLOW_MO_MS`.

## 7. Files to Read

- repository startup files and project profile;
- Core and DK routers, entries, manifests, and governance profiles;
- Decision 0020 and Change Request 0008;
- `docs/ai/rules/AI-EXECUTION-RULES.md`;
- `docs/ai/rules/SCOPE-CONTROL-RULES.md`;
- `docs/ai/rules/TEST-AND-VALIDATION-RULES.md`;
- `docs/ai/rules/ERROR-HANDLING-AND-LOGGING-RULES.md`;
- `docs/ai/rules/GIT-AND-COMMIT-RULES.md`;
- `docs/ai/rules/REPORTING-RULES.md`;
- `.env.example`;
- first-party PHP source under `app/`, `bootstrap/`, `config/`, `database/`, `Modules/Core/`, `Modules/DK/`, `resources/`, `routes/`, and `tests/`, excluding docs/generated/cache artifacts;
- `vendor/laravel/framework/config/view.php` and the provider that merges it, read-only;
- `vendor/nwidart/laravel-modules/config/config.php` and `LaravelModulesServiceProvider.php`, read-only;
- `composer.lock`, read-only, only to verify installed package versions/paths;
- nearby PHPUnit tests for project naming and assertion conventions.

## 8. Files to Edit

- `.env.example`
- `config/core.php`
- `Modules/Core/config/config.php`
- `docs/project/ai/remediations/REMEDIATION-PACK-TMS-CONFIGURATION-SURFACE-0005.md` only for lifecycle/evidence synchronization
- `docs/project/ai/remediations/REMEDIATIONS-INDEX.md` only for lifecycle synchronization
- `docs/project/ai/changes/CHANGE-REQUEST-TMS-CONFIGURATION-SURFACE-0008.md` and `CHANGES-INDEX.md` only when implementation status changes
- `docs/project/ai/decisions/TMS-DECISION-0020-configuration-surface-remediation.md` and `DECISIONS-INDEX.md` only when review/resolution changes
- `docs/project/ai/runs/RUNS-INDEX.md` only after Run Report authorization

Only the two explicitly listed Core config files are authorized for config edits. No other PHP config file is authorized.

## 9. Files to Create

- `tests/Feature/ConfigurationSurfaceTest.php`
- `docs/project/ai/runs/REMEDIATION-PACK-TMS-CONFIGURATION-SURFACE-0005-run-001.md` only after the post-execution reporting gate

No first-party config file is currently authorized for creation. Discovery of one is a stop condition requiring an amended Pack.

## 10. Do Not Change

- `.env` and every other non-example environment file;
- the pre-existing modified `phpunit.xml`;
- all existing root, Core, and DK PHP config files other than the two explicitly listed Core config files;
- `vendor/`, dependencies, lockfiles, package configuration, generated files, caches, databases, queues, logs, external systems, and target environments;
- API/routes/UI/translations/models/migrations/jobs/runtime behavior;
- unrelated source, tests, formatting, or lifecycle records.

## 11. Configuration / Settings Impact

```text
Configuration / Settings Impact: Yes
Config Files Changed: Yes — root and module Core config only
Environment Variables Required: Yes
Admin Settings Changed: No
External Integration / Secret Settings Changed: No
Queue / Cache / Logging Settings Changed: Documentation/example parity only; runtime behavior unchanged
Test / Development Setup Required: Yes
Operator Manual Setup Required: Yes
```

### Configuration / Settings Changes

```text
Configuration / settings changes:
- Setting / File / Key: .env.example / all active first-party env() keys
  Change: add the 81 declarations missing in the generation baseline using existing config fallback semantics
  Reason: make the committed setup manifest complete and testable
  Sensitive: Mixed; secret-capable keys must remain empty/commented and never contain real values
  Operator action required: Yes — review and manually apply desired values to .env

- Setting / File / Key: first-party configuration namespaces
  Change: verify ownership/resolution; complete the already-existing root Core config and synchronize it with the module Core config; create no new namespace file
  Reason: dependency-provided namespaces are already resolved and copying them would create drift
  Sensitive: No
  Operator action required: No unless a genuinely unresolved namespace is discovered

- Setting / File / Key: config/core.php and Modules/Core/config/config.php / core.acceptance.*
  Change: expose synchronized browser, headless, timeout, and slow-motion defaults through four environment variables
  Reason: make the approved root Core configuration complete without creating ineffective or misleading env declarations
  Sensitive: No
  Operator action required: Optional manual .env override only
```

### Required Follow-up Updates

- Operator manually synchronizes reviewed declarations into `.env`; AI execution is forbidden from reading or changing that file.
- Operator reviews whether the completed root `config/core.php` should be tracked in the eventual commit; no commit is authorized yet.

---

## 12. Multilingual / Translation Impact

```text
Multilingual / Translation Impact: No
Visible Text Changed: No
Admin UI Labels Changed: Not applicable
API Messages Changed: Not applicable
Validation Messages Changed: Not applicable
Translation Files Changed: Not applicable
RTL/LTR Impact: Not applicable
Human Review for Locale Wording Required: No
```

No multilingual or translation impact.

### Required Follow-up Updates

No multilingual or translation follow-up required.

---

## 13. UI / Admin UI Impact

```text
UI / Admin UI Impact: No
Admin UI Changed: Not applicable
Settings UI Changed: Not applicable
Tables / Forms Changed: Not applicable
Actions / Buttons Changed: Not applicable
Menus / Navigation Changed: Not applicable
Dashboard / Monitoring UI Changed: Not applicable
Permission-gated UI Changed: Not applicable
```

No UI or Admin UI impact.

### Required Follow-up Updates

No UI/Admin UI follow-up required.

## 14. Implementation Rules

- Parse PHP tokens or use an equivalently comment-aware method; commented `env()` examples must not count as active references.
- Restrict the env scan to versioned first-party config files. Do not scan `vendor/` into the first-party environment contract.
- Treat a commented declaration in `.env.example` as documented only when leaving it enabled would change the effective fallback or when it represents an optional/secret value.
- Keep declaration names unique and grouped with their subsystem.
- Do not turn static config constants into environment variables outside the four approved Core browser settings.
- Do not use `env()` outside config.
- Do not publish dependency config or copy it into root `config/`.
- Preserve line-ending/style conventions and all unrelated `.env.example` entries.
- Preserve unrelated working-tree changes exactly.

## 15. Architecture Constraints

- Root project owns the cross-owner configuration contract and `.env.example`.
- Core and DK retain ownership of their module config files.
- Laravel and `nwidart/laravel-modules` remain owners of their dependency defaults; local copies are not created without a separately approved reason.
- Config-cache compatibility requires application code to consume `config()`, not `env()`.
- The test may inspect versioned files but must not inspect `.env` or expose environment values.

## 16. Data Model / Migration / Relationship Impact

```text
Data / Migration Impact: No
New Tables Required: No
Existing Tables Modified: No
Foreign Keys Changed: Not applicable
Model Relationships Changed: Not applicable
Indexes / Unique Constraints Changed: Not applicable
Data Backfill Required: Not applicable
Rollback Impact: No
Data-loss Risk: No
```

No database changes required.

No foreign key changes required.

No model relationship changes required.

No snapshot impact.

### Required Follow-up Updates

No data model or migration follow-up required.

## 17. Validation Rules

- Recompute active first-party env references with a comment-aware parser.
- Assert the recomputed missing set against `.env.example` is empty.
- Assert no duplicate active/commented declaration exists for the same variable.
- Assert no active `env()` call exists outside approved config paths.
- Boot the application and assert the required current keys resolve with the expected types/defaults, including `acceptance`, `core`, `dk`, `modules`, and `view` representatives.
- Assert dependency-provided roots resolve without root copies.
- Verify sensitive example entries are empty/commented and contain no suspicious real values.
- Run syntax, focused PHPUnit, `git diff --check`, scoped diff, and final status checks.
- Do not run config cache, dependency, database, queue, browser, external-network, or generated-file commands.

## 18. Error Handling / Logging / Traceability Impact

```text
Error Handling Impact: No
API Error Contract Impact: Not applicable
UI / Admin UI Error Impact: Not applicable
Stable Error Code Impact: Not applicable
Exception Handling Impact: No
External Error Normalization Impact: Not applicable
Callback / Inbound-event Error Impact: Not applicable
Retry / Permanent Failure Impact: Not applicable
Message / Attempt Status Impact: Not applicable
Structured Logging Impact: No
Traceability Impact: No
Sensitive Data Impact: Yes — non-exposure validation only
Error-specific Test Impact: No
Operator Documentation Impact: Yes — manual local setup checklist only
```

No error handling, logging, or traceability behavior changes. The only safety correction is preventing real sensitive values from entering examples, tests, reports, or output.

### Existing Defect and Required Correction

```text
Existing defective behavior at generation time:
- .env.example omitted 81 active first-party environment keys; the manifest implementation has now closed this gap.
- no regression test detects configuration-root or environment-manifest drift.

Evidence:
- comment-aware token inspection of versioned first-party config on 2026-10-11.

Root cause:
- config and environment declarations evolved without a repository-level parity contract.

Required corrected behavior:
- all active keys are represented safely and every referenced namespace is provably resolved.

Regression risk:
- an enabled empty or inaccurate value could override an existing computed/default value.

Previously stored data affected: No
Previously generated logs affected: No
Existing API consumers affected: No
Existing Admin UI workflow affected: No
```

No error scenarios changed by this remediation.

No API error contract or stable error-code changes required.

No UI or Admin UI error changes required.

No exception or external-error normalization changes required.

No structured logging changes required.

No traceability changes required.

### Sensitive Data Correction

- Sensitive data category: credentials, tokens, webhook/service secrets, production URLs, target identifiers
  Exposure location: `.env.example`, tests, reports, or command output
  Current unsafe behavior: no exposure was observed; incomplete examples create future copy/paste risk
  Required handling: omit real values; use empty/commented safe placeholders
  Authorized visibility: variable names and safe placeholders only
  Existing stored data cleanup required: No
  Regression test required: Yes

Historical cleanup required: No
Credential or token rotation required: No
Security review required: No unless execution discovers a real value.

### Error-specific Validation Requirements

No error-specific validation is required because no application error behavior changes. The configuration test must fail with variable/key names only and must never print actual environment values.

### Compatibility and Historical Impact

```text
Backward compatibility impact:
- none when declarations preserve current fallback semantics

Existing API client impact:
- none

Existing database record impact:
- none

Existing message / attempt history impact:
- none

Existing log history impact:
- none

Migration or data repair required: No
Reprocessing required: No
Rollback risk:
- local bootstrap behavior can drift if optional/computed variables are enabled with wrong empty values
```

### Required Follow-up Updates

No error handling, logging, or traceability follow-up required.

## 19. Tests to Add

- Test name: `test_every_active_first_party_environment_reference_is_declared_once_in_env_example`
  - Purpose: prevent environment-manifest drift.
  - Behavior proven: comment-aware active `env()` names in first-party config have exactly one enabled or commented declaration in `.env.example`.
  - Main assertions: missing set is empty; duplicate set is empty; failure output lists names only.
  - Required setup: repository files only; no application data or `.env` access.
  - Expected side effects: none.
  - Negative/error cases: commented PHP examples are ignored; duplicate env declarations fail.
  - Related acceptance criteria: environment parity and safe reporting.

- Test name: `test_first_party_source_reads_environment_only_from_configuration_files`
  - Purpose: preserve Laravel config-cache compatibility.
  - Behavior proven: active `env()` calls do not occur in application/module/runtime/test code outside config.
  - Main assertions: violation list is empty and paths only are reported.
  - Required setup: first-party source scan with documented exclusions.
  - Expected side effects: none.
  - Negative/error cases: comments and vendor files do not cause false positives.
  - Related acceptance criteria: no first-party runtime `env()` reads.

- Test name: `test_required_configuration_namespaces_and_keys_resolve_after_application_boot`
  - Purpose: prove the actual runtime configuration contract rather than filenames alone.
  - Behavior proven: representative root/project/Core/DK/framework/package keys exist and have expected types/default-safe values.
  - Main assertions: `acceptance`, `core`, `dk`, `modules`, and `view` representative keys resolve; dependency roots resolve without local copies.
  - Required setup: normal Laravel test application boot only.
  - Expected side effects: none.
  - Negative/error cases: absent namespace/key fails with its name, not environment contents.
  - Related acceptance criteria: zero unresolved config roots.

- Test name: `test_sensitive_environment_examples_do_not_contain_real_values`
  - Purpose: protect the committed example manifest.
  - Behavior proven: secret-capable keys are empty/commented placeholders and no high-risk literal is added.
  - Main assertions: approved placeholder form; no credential-like value.
  - Required setup: `.env.example` only.
  - Expected side effects: none.
  - Negative/error cases: populated token/secret/password/webhook values fail without echoing the value.
  - Related acceptance criteria: sensitive-data non-exposure.

## 20. Tests to Run

After separate execution approval, from repository root:

```powershell
php -l tests/Feature/ConfigurationSurfaceTest.php
php vendor/bin/phpunit --do-not-cache-result tests/Feature/ConfigurationSurfaceTest.php
git diff --check
git diff --name-only
git status --short --branch
```

Command classification: PHP syntax and Git inspection are safe read-only; PHPUnit is validation-or-test and is authorized only after execution approval. No formatter mutation, config cache, database command, dependency command, browser, server, worker, or external-network command is authorized.

## 21. Acceptance Checklist

- [x] active env inventory is recomputed with comments excluded;
- [x] all active first-party env keys are declared once in `.env.example`;
- [x] defaults/placeholders preserve current fallback semantics;
- [x] no real secret or environment value is present;
- [x] no first-party `env()` use exists outside config;
- [x] all required current config roots/keys resolve at boot;
- [x] no vendor/framework config is copied or edited;
- [x] no speculative first-party config file is created;
- [x] root and module Core config contain the synchronized approved keys/defaults;
- [x] `.env` was not accessed or changed, and the pre-existing `phpunit.xml` modification was preserved unchanged by this remediation;
- [x] focused tests, syntax, diff check, and scope inspection pass;
- [x] manual `.env` synchronization checklist is delivered;
- [x] no commit occurs without separate operator authorization.

## 22. Rollback / Safety Notes

Rollback reverts only `.env.example`, removes the new test, and synchronizes related lifecycle documents. Never use broad restore/clean/reset commands. Do not read, overwrite, delete, or restore `.env`, `config/core.php`, `phpunit.xml`, or unrelated user files. If execution discovers a real secret, stop without reproducing it and request security review.

## 23. Operator Review Checklist

Before execution:

- approve the exact implementation allowlist (`.env.example` and one new test file);
- accept that no PHP config file is currently missing after provider resolution;
- accept that `modules` and `view` remain dependency-provided;
- confirm `phpunit.xml` must remain untouched and only the exact Core config files may change;
- approve the focused PHPUnit command;
- confirm `.env` will be updated manually by the operator after review.

After execution:

- review every `.env.example` declaration for correct enabled/commented form;
- verify no real credential, token, URL, target identifier, or personal value appears;
- review focused test output and scoped diff;
- manually apply desired safe values/placeholders to `.env` without sharing its contents;
- accept or reject the remediation result;
- authorize a Run Report and commit separately if accepted.

## 24. Operator Documentation Impact

```text
Operator Documentation Impact: Yes
Operator Documentation Update Required: No — the Pack/run checklist owns this one-time repository setup action
Guide File:
- Not applicable
```

### Operator-facing Remediation

```text
Operator-facing behavior changed:
- local environment setup gains a complete reviewed variable checklist; application behavior must remain unchanged
```

### Guide Sections to Update

No operator documentation update required.

### Screenshot Requirements

```text
Screenshots required: No
Screenshot type:
- Not applicable

Suggested screenshot path:
- Not applicable
```

### Required Follow-up Updates

- Operator-only `.env` synchronization after reviewing the completed `.env.example` diff.
- Operator review of whether the now-completed root `config/core.php` should be tracked in the eventual commit.

## 25. Agent Final Report

Follow `docs/ai/rules/REPORTING-RULES.md`. Report baseline and final env-key counts, config-root ownership mapping, files changed, tests actually run, preserved unrelated changes, `.env` non-access, sensitive-data checks, documentation/index maintenance, manual operator actions, and commit status.
