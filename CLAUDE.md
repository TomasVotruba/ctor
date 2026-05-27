# CLAUDE.md

PHPStan extension that flags `new X()` followed by repeated setter calls and suggests moving the values into the constructor.

## Architecture

Single rule built on PHPStan's two-phase collector pattern:

- `src/Collector/NewWithFollowingSettersCollector.php` — per-file pass. Walks statement-bearing nodes (`ClassMethod`, `Function_`, `If_`, `ElseIf_`, `While_`, `Foreach_`, `For_`) and records each `$var = new X()` plus the chain of `set*` / `add*` calls on `$var` that follow it in the same statement list.
- `src/Rules/NewOverSettersRule.php` — `CollectedDataNode` pass. Groups records by class name and emits an error only when the **same class** appears **2+ times** with an **identical sorted setter set**.
- `src/Enum/RuleIdentifier.php` — single identifier: `tv.newOverSetters`.

Wiring: `config/extension.neon` registers the collector + rule. `composer.json` exposes it via `extra.phpstan.includes` for `phpstan/extension-installer`.

## Non-obvious behavior

- **"Setter" = `set*` OR `add*`** prefix (`NewWithFollowingSettersCollector::isSetterName`). Not just `set*`.
- **Threshold is 2 occurrences**, not 1 — see the `count(... ) === 1` continue in `NewOverSettersRule::processNode`. A single `new + setters` block never triggers.
- **Flow-break aborts the file's collection**: a `return` or `throw` anywhere after the first recorded `new` in the same statement list discards all collected records for that scope (`isNodeBreakingFlow`).
- **Skipped classes**: vendor code, anything `is(Symfony\Component\HttpKernel\Kernel)`, and files containing `@ORM\Entity` or starting with `#[Entity]` (Doctrine entities, detected by raw file string search — not reflection).
- Order of setters doesn't matter — names are sorted + uniqued before hashing.

## Commands

```bash
composer phpstan        # analyse src/config/tests (errorFormat: symplify)
composer check-cs       # ECS check
composer fix-cs         # ECS fix
composer rector         # rector --dry-run
vendor/bin/phpunit      # run rule tests
```

## Tests

`tests/Rules/NewOverSettersRule/NewOverSettersRuleTest.php` uses PHPStan's `RuleTestCase`. Fixtures live under `Fixture/`; `Skip*.php` files assert no error, the others assert the standard error. Loaded config: `config/extension.neon`. `Source/` and `Fixture/` are excluded from the main PHPStan run (see `phpstan.neon`).

## Requirements

PHP `^8.3`, `phpstan/phpstan ^2.1.32`. Uses typed class constants (`public const string ...`), so 8.3 is a hard floor.
