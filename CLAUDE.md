# CLAUDE.md

Guidance for working in this repository.

## What this is

**Laractions** (`edulazaro/laractions`) is a Laravel package that implements an
action-based pattern: business logic lives in self-contained `Action` classes
that run synchronously or async (queued). Published on Packagist as a library —
**there is no application here**, only `src/` (the package) and `tests/`.

- PHP `^8.2`, Laravel `>=10.0`.
- PSR-4: `EduLazaro\Laractions\` → `src/`, `EduLazaro\Laractions\Tests\` → `tests/`.
- Auto-discovered provider: `EduLazaro\Laractions\LaractionsServiceProvider`.

## Critical project rules

> These come from accumulated project memory. Treat them as hard constraints.

- **The existing tests are an untouchable non-regression baseline.** The green
  test suite is what guarantees the package keeps working for the 600+ apps that
  depend on it. **Never edit existing tests to make a change pass** — if a change
  breaks a test, the change is wrong, not the test. Add new tests for new behavior.
- **`run()` argument handling must not regress.** `run()` supports three calling
  styles and the single-array "attribute bag" is load-bearing — see below. Any
  change to `Action::run()` must preserve all three styles for both single- and
  multi-parameter `handle()` signatures.

## Commands

```bash
composer install                 # install deps (incl. orchestra/testbench)
vendor/bin/phpunit               # run the full suite
vendor/bin/phpunit --filter run  # run a single test by name
```

Tests run on Testbench with sqlite `:memory:` (`phpunit.xml`).

## Architecture

`src/`:

- **`Action.php`** — abstract base. Subclasses implement `handle(...)`. Public API:
  `create()` (resolves via container, injecting deps), `run()`, `dispatch()` (async),
  and the fluent chain `with()`, `on()`, `actor()`, `retry()`, `delay()`, `queue()`,
  `trace()`, `enableLogging()`. Validation via the `$rules` array.
- **`Jobs/ActionJob.php`** — the queued wrapper. `dispatch()` builds one of these
  automatically; on `handle()` it rehydrates the actionable model and calls
  `$action->run($this->params)`.
- **`Concerns/HasActions.php`** — model trait. `$model->action('key'|Class::class)`
  resolves + binds the model via `on()`. Supports `mockAction()` for tests.
- **`Concerns/IsActor.php`** — model trait. `$model->act(Class::class)` sets the actor.
- **`ActionTrace.php`** — Eloquent model storing trace rows (actor/target morphs +
  params) when `trace()` is enabled.
- **`Console/Commands/`** — `make:action` (generator, stubs in `stubs/`) and
  `list:actions`.

### `on()` model injection
`on($model)` walks the model's class + parents and assigns it to a matching
`protected` property named after the (lcfirst) class basename — e.g. a `$user`
property receives a `User`. This is why model actions just declare `protected User $user;`.

### `run()` argument mapping — the load-bearing detail
`run()` forwards to `handle()` by reflection. Three styles, all must keep working:

```php
$a->run('x', 'y');                       // positional
$a->run(email: 'x', subject: 'y');       // named args (PHP assoc)
$a->run(['email' => 'x', 'subject' => 'y']); // assoc array mapped to param names
```

The **single-array bag** is the subtle case: when `handle()` declares **exactly one
parameter** and `run()` gets **one array**, the array is forwarded *whole* as that
argument (matches native PHP `$fn(['k'=>v])` on `fn(array $a)`) instead of being
spread as named args. Multi-param `handle()` maps array keys to parameter names.
Missing params fall back to `handle()` defaults, then `null`. See `BagActionTest`
and `tests/Support/{BagAction,SingleScalarAction}.php` for the contract.

## Conventions

- Action class names **drop the `Action` suffix** (`SendEmail`, not `SendEmailAction`);
  the `App\Actions\*` namespace carries the intent. `make:action` strips a trailing
  "Action" automatically. Model actions live under `App\Actions\{Model}\`.
- Match the surrounding style: heavy docblocks on every method, fluent `static`
  returns, `protected` properties.
