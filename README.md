# Ctor

[![Downloads](https://img.shields.io/packagist/dt/tomasvotruba/ctor.svg?style=flat-square)](https://packagist.org/packages/tomasvotruba/ctor/stats)
[![Code Analysis](https://img.shields.io/github/actions/workflow/status/TomasVotruba/ctor/code_analysis.yaml?branch=main&style=flat-square&label=code%20analysis)](https://github.com/TomasVotruba/ctor/actions/workflows/code_analysis.yaml)

If you can use constructor instead of setters, use it. These PHPStan rules will help you to find such cases.

<br>

## What It Does

This tool collects instances of `new object()` followed by a series of method calls on the same object:

```php
$human = new Human();
$human->setName('Tomas');
$human->setAge(35);
```

...and suggests turning them into constructor arguments:

```php
$human = new Human('Tomas', 35);

// named arguments work too, if the constructor grows wide
$human = new Human(name: 'Tomas', age: 35);
```

Both `set*` and `add*` method prefixes are treated as setters, so `$collection->addItem(...)` chains are flagged the same way.

### Why?

Such chained setters often indicate implicit required dependencies. By moving them to the constructor, you make the object state explicit, safer, and easier to reason about — and even easier to test.

<br>

## Requirements

- PHP `^8.3`
- PHPStan `^2.1`

<br>

## Installation

```bash
composer require tomasvotruba/ctor --dev
```

<br>

## Usage

Use [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer) to load the extension automatically. Run PHPStan and the rule will pick up.

Without the extension installer, include the config manually in your `phpstan.neon`:

```neon
includes:
    - vendor/tomasvotruba/ctor/config/extension.neon
```

<br>

## What You'll See

When the rule fires, PHPStan reports:

```
Class "App\Human" is always created with same 2 setter(s): "setAge()", "setName()"
Pass these values via constructor instead
```

The error identifier is `tv.newOverSetters` — use it to ignore specific cases via PHPStan's `ignoreErrors`.

<br>

## When the Rule Fires (and When It Doesn't)

The rule is intentionally conservative. It only reports a class when:

- The same class is instantiated **at least twice** across the analysed code, **with the same set of setters each time**. A single `new + setters` block on its own is not enough — there must be a repeated pattern.

It deliberately **skips**:

- Doctrine entities (files containing `@ORM\Entity` or `#[Entity]`)
- Symfony `HttpKernel\Kernel` subclasses
- Vendor code
- `new + setters` blocks interrupted by a `return` or `throw` (likely conditional construction)

<br>


Happy coding!
