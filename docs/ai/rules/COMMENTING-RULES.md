# Commenting Rules

## Purpose

Comments should explain intent, business rules, invariants, constraints, and trade-offs. They must not repeat obvious code.

## General Rules

1. Prefer self-documenting code over comments.
2. Use the applicable owner profile's documentation-comment convention for public business-facing classes and methods.
3. Do not add comments that repeat the code.
4. Add comments for non-obvious business rules, invariants, provider behavior, retry behavior, idempotency, state transitions, and intentional trade-offs.
5. Use inline comments only when the reason behind the code is not obvious.
6. Do not add large comment blocks inside methods.
7. If a comment explains a temporary decision, also register it as a Decision Record or technical debt item.
8. Comments must stay consistent with canonical decisions.

## Documentation Comments Should Be Used For

- Service classes
- Action classes
- Interfaces
- DTOs
- Value Objects
- Enums when they contain behavior or business meaning
- Jobs
- Events
- Listeners
- public methods with business logic
- methods with complex input/output contracts

## Documentation Comments Are Usually Not Required For

- obvious Controller methods
- simple getters/setters
- simple migrations
- obvious properties
- code that is already clear through type hints and naming

## Good Inline Comment Pattern

Explain why, not what.

Bad:

```php
// Set status to pending
$status = 'pending';
```

Good:

```php
// The request must stay pending until the provider-specific message is created.
$status = NotificationStatus::PENDING;
```

## Example Documentation Comment

```text
/**
 * Creates a domain record from an upstream command.
 *
 * This method only persists intent. External execution is handled by later
 * pipeline steps.
 */
```

Concrete syntax and language-specific documentation rules come from the applicable owner profile.
