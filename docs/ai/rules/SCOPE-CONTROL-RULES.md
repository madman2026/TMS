# Scope Control Rules

## Allowed Changes

The Agent may only create files listed in `Files to Create` and edit files listed in `Files to Edit`.

## Read-Only Files

Files listed in `Files to Read / Reference` are read-only unless also listed under `Files to Edit`.

## Do Not Change

Files listed under `Do Not Change` must not be modified.

## No Opportunistic Refactor

The Agent must not perform refactoring, formatting-only changes, renaming, cleanup, dependency updates, or unrelated improvements unless the current AI Pack explicitly asks for them.

## Owner and Profile Resolution Rule

Before creating or editing any file, the Agent must:

1. determine the content owner from scope, audience, source of truth, consumers, and dependencies;
2. load the applicable project and owner profiles declared by the routing documents or owner manifest;
3. resolve the real path and repository convention from the current tree;
4. confirm that the resolved path is an allowed equivalent of a path named by the Pack.

A filename, prefix, current folder, or technology name is supporting evidence only. It must not be the sole ownership or path decision.

Pack paths may be generic, suggested, outdated, or different from the actual repository structure. The Agent must not blindly use them. Path resolution does not expand Pack scope.

If the correct path is unclear, multiple valid locations exist, or resolving the path would cross an owner boundary not authorized by the Pack, the Agent must stop and ask the operator.

## Owner-Local Development Rule

When an owner profile declares an implementation root, the Agent must keep new owner-specific implementation inside that root unless the current Pack, an approved decision, or explicit operator approval allows another location.

The applicable profile may define:

- an implementation root and expected internal structure;
- protected areas;
- generated or published output locations;
- extension-point preferences;
- cross-owner dependency restrictions;
- stop conditions.

These profile bindings tighten this shared rule. They do not expand Pack scope or waive approval requirements.

## Protected Areas

Protected areas are resolved through the applicable project and owner profiles.

Direct changes to a protected area are allowed only when:

1. the current Pack explicitly lists the protected file under `Files to Edit`, or the operator explicitly approves the protected-area change;
2. no suitable extension point can satisfy the requirement;
3. the change is minimal, isolated, and rollback-friendly;
4. the change does not introduce unrelated refactoring, formatting-only edits, cleanup, or dependency updates.

Generated or published output must be changed at its source unless the Pack or operator explicitly authorizes editing the generated location.

The Agent must stop and ask for operator guidance when an implementation appears to require a protected-area change, no safe extension point can be found, or the only apparent solution crosses an unauthorized owner boundary.

## Architecture Boundary Scope Rule

The Agent must respect the architecture boundary declared by applicable owner Canonical files and profiles.

Path, scope, implementation, or Pack instructions must not silently move an owner outside its approved responsibilities or make it own decisions assigned to another bounded context.

If implementation would cross or redefine such a boundary, execution stops and follows the conflict, decision, change, or remediation workflow.

## Governance Maintenance Exception

AI Packs define the implementation scope of the work.

Required governance and documentation maintenance updates are allowed outside the Pack implementation file list only when active AI documentation rules require them.

Allowed governance and maintenance updates may include applicable owner indexes, Run Reports, Reviews, Decision Log entries, Change Requests, Remediation Packs, Canonical updates explicitly required by approved decisions, and shared routing indexes.

These updates are not unauthorized scope violations only when all of the following are true:

1. an active governance rule requires the update;
2. the update is directly related to the current Pack, Run, Review, Decision, Change Request, Remediation Pack, or Canonical update;
3. the update introduces no unrelated content;
4. the Agent Final Report and Run Report identify it;
5. the update does not modify a file under `Do Not Change`.

Governance files not required by the current work must not be edited. If allowance is unclear, report the update under `Required Follow-up Updates` instead of editing silently.

## Out-of-Scope Discovery

If the Agent finds a required change outside the Pack scope, it must stop and report it. The operator can then decide whether to create a Change Request, Remediation Pack, or new AI Pack.

This rule does not apply to governance or documentation maintenance updates covered by the Governance Maintenance Exception.
