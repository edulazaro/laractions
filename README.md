![Laractions](art/banner.png)

# Laractions - Actions for Laravel

> Encapsulate your business logic into clean, reusable classes that run **synchronously or asynchronously** in Laravel.

<p align="center">
    <a href="https://packagist.org/packages/edulazaro/laractions"><img src="https://img.shields.io/packagist/dt/edulazaro/laractions" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/edulazaro/laractions"><img src="https://img.shields.io/packagist/v/edulazaro/laractions" alt="Latest Stable Version"></a>
</p>

## Introduction

**Laractions** is a package that introduces an **action-based pattern** for Laravel applications. Actions encapsulate specific pieces of logic that can be executed synchronously or asynchronously, keeping your controllers and models clean.

Instead of placing business logic in controllers, models, or services, **actions** allow you to encapsulate reusable operations in self-contained classes.

✅ **Supports Standalone & Model-Scoped Actions**  
✅ **Allows Asynchronous Execution with Queues**  
✅ **Provides an Artisan Generator for Quick Creation**  

### Why Use Actions?

Instead of bloating **controllers**, **models**, or **services**, Laractions keeps logic **encapsulated** and **reusable**.

| Feature            | Laractions |
|--------------------|-----------|
| **Encapsulation** | Keeps business logic clean & reusable |
| **Async Execution** | Supports Laravel queues & retries |
| **Validation** | Auto-validates parameters before execution |
| **Model-bound** | Works directly with Eloquent models |
| **Fluent API** | `run()`, `dispatch()`, `retry()`, `queue()` |

---

## Installation

Install via Composer:

```bash
composer require edulazaro/laractions
```

Once installed, the package will be available in your Laravel application.

## Naming Convention

Action class names drop the `Action` suffix because the `App\Actions\*` namespace already carries the intent — just like Laravel uses `App\Jobs\SendEmail` rather than `App\Jobs\SendEmailJob`. The `make:action` generator strips a trailing "Action" automatically, so both `php artisan make:action SendEmailAction` and `php artisan make:action SendEmail` produce the same `SendEmail` class.

##  Creating Actions

You can manually create an action or use the **artisan command**:

```bash
php artisan make:action SendEmail
```

This will generate this basic action:

```php
namespace App\Actions;

use EduLazaro\Laractions\Action;

class SendEmail extends Action
{
    public function handle()
    {
        // Your action logic here
    }
}
```

You will place the logic inside the `handle` method:

```php
namespace App\Actions;

use EduLazaro\Laractions\Action;

class SendEmail extends Action
{
    public function handle(string $email, string $subject, string $message)
    {
        // Your action logic here
    }
}
```

You can then run the action via the `run` method:

```php
SendEmail::create()->run('user@example.com', 'Welcome!', 'Hello User');
```

### How `run()` maps arguments to `handle()`

`run()` forwards its arguments to `handle()` by reflection. You can call it in three ways:

```php
// Positional
SendEmail::create()->run('user@example.com', 'Welcome!', 'Hello User');

// Named arguments
SendEmail::create()->run(email: 'user@example.com', subject: 'Welcome!', message: 'Hello User');

// Associative array, mapped to parameters by name
SendEmail::create()->run([
    'email' => 'user@example.com',
    'subject' => 'Welcome!',
    'message' => 'Hello User',
]);
```

When `handle()` declares **more than one parameter**, the keys of an associative
array are matched to the parameter names (the last example above).

When `handle()` declares **a single `array` (or `iterable`/`mixed`/untyped) parameter**,
the array is passed through whole as that one argument — the "attribute bag" pattern:

```php
class CreateInvoice extends Action
{
    public function handle(array $attributes)
    {
        // $attributes === ['concept' => 'x', 'amount' => 10]
        return $attributes['concept'];
    }
}

CreateInvoice::create()->run(['concept' => 'x', 'amount' => 10]);
```

When the single parameter is a **concrete type** (an object or scalar, e.g.
`handle(File $file)`), a single array is **not** treated as the bag: its keys are mapped
by name, and a single value is passed positionally — so both of these bind `$file`:

```php
ProcessFile::create()->run(['file' => $file]);  // mapped by name -> $file = $file
ProcessFile::create()->run($file);              // positional     -> $file = $file
```

This mirrors native PHP: an `array` parameter receives the array whole, while a typed
parameter receives the matching value, never the wrapping array. (A union that includes
`array`/`iterable` — e.g. `array|Foo` — still receives the bag.)

You can customize the constructor. Dependencies will be injected:

```php
namespace App\Actions;

use EduLazaro\Laractions\Action;
use App\Services\MailerService;

class SendEmail extends Action
{
    protected MailerService $mailer;

    /**
     * Inject dependencies via the constructor.
     */
    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Handle the action logic.
     */
    public function handle(string $email, string $subject, string $message)
    {
        // Use the injected service
        $this->mailer->send($email, $subject, $message);
    }
}
```

You can run the action as usually via the `run` method:

```php
SendEmail::create()->run('user@example.com', 'Welcome!', 'Hello User');
```

##  Creating Model Actions

You can manually create a model action or use the **artisan command**:

```bash
php artisan make:action SendEmail --model=User
```

This will create the next basic model action:

```php
namespace App\Actions\User;

use EduLazaro\Laractions\Action;
use App\Models\User;

class SendEmail extends Action
{
    protected User $user;

    public function handle()
    {
        // Implement action logic for user here
    }
}
```

The user instance will be available inside the `$user` attribute.

Models using actios should use the `HasActions` trait, so you can register actions inside the `$actions` array of the model:

```php
class User extends Model
{
    use HasActions;

    protected array $actions = [
        'send_email' => SendEmail::class,
    ];
}
```

Now, you can execute the action using:

```php
$user->action('send_email')->run('user@example.com', 'Welcome!', 'Hello User');
```

Alternatively, you can still call the action class directly so you don't have to define the action inside the model:

```php
$user->action(SendEmail::class)->run('user@example.com', 'Welcome!', 'Hello User');

```

##  Dynamic Parameters

Laractions provides a flexible `with()` method to set action attributes dynamically:

```php
$action = SendEmail::create()->with([
    'email' => 'user@example.com',
    'subject' => 'Welcome!',
    'message' => 'Hello User'
])->run();
```

This avoids passing long parameter lists in the `run()` method. Please note that these values will be set as action attributes, so you would access them via:

```php
$this->email;
```

##  Actions and Models 

When calling an action from a model, the model is automatically injected into the action:

```php
$user->action(SendEmail::class)->run();
```

If the `SendEmail` class has a `$user` property, the action will automatically set the model:

```php
class SendEmail extends Action
{
    protected User $user; // Automatically injected

    public function handle()
    {
        Mail::to($this->user->email)->send(new WelcomeMail());
    }
}
```

##  Running Actions Asynchronously

Laractions allows dispatching actions asynchronously as jobs:

```php
$action = SendEmail::create()
    ->queue('high')
    ->delay(10)
    ->retry(5)
    ->dispatch('user@example.com', 'Welcome!', 'Hello User');
```
This queues the action instead of executing it immediately. The job will be automatically created and liked to the action, so you don't need to define it.

You can configure how actions are dispatched as jobs:

```php
class SendEmail extends Action
{
    protected int $tries = 5;
    protected ?int $delay = 30;
    protected ?string $queue = 'emails';
}
```

##  Mocking Actions for Tests

During unit tests, you can **mock actions**:

```php
$user->mockAction(SendEmail::class, new class {
    public function run()
    {
        return 'Mocked!';
    }
});

echo $user->action(SendEmail::class)->run(); // Output: 'Mocked!'
```

This allows testing without executing real logic.

##  List Available Actions

To list all registered actions in your application, run:

```php
php artisan list:actions
```

## Logging Actions

Enable logging for any action:

```php
SendEmail::create()
    ->enableLogging()
    ->run('user@example.com', 'Welcome!', 'Hello User');
```

Logs will be written to Laravel's log files.

## Acting as an Actor

You can make any model an actor (like a User) by using the `IsActor` trait:

```php
use EduLazaro\Laractions\Concerns\IsActor;

class User extends Model
{
    use IsActor;
}
```

Then, call actions like this:

```php
$user->act(SendInvoice::class)
     ->on($order)
     ->trace()
     ->run();
```

This automatically sets the actor on the action before executing it.

## Enabling Tracing

Tracing is disabled by default. You can enable it per action like this:

```php
SendEmail::create()
    ->trace()
    ->run('user@example.com', 'Welcome!', 'Hello!');
```

You can assign the actor and actionable model like so:

```php
SendEmail::create()
    ->actor($user)
    ->on($targetModel)
    ->trace()
    ->run($params);
```

Here is an traced action started by an actor:

```php
$user->act(SendInvoice::class)
     ->on($order)
     ->trace()
     ->run();
```

## LaraClaude integration

Laractions is supported by [LaraClaude](https://github.com/edulazaro/laraclaude), a Laravel toolkit plugin for [Claude Code](https://claude.ai/code). It ships two skills that work with your actions:

```
/lc:generate-action Property/ToggleFeatured
/lc:extract-action PropertyController:store
```

- **`/lc:generate-action`** scaffolds a new action class with the right boilerplate and registers it on the corresponding model, following your project's [naming convention](#naming-convention).
- **`/lc:extract-action`** pulls business logic out of a controller or Livewire component method into a standalone action, and replaces the original code with the action call.

Both skills read the installed Laractions API so they only use what your version actually has, match the conventions of your existing actions, and run a syntax check on the generated class.

Install the plugin in Claude Code with `/plugin install github:edulazaro/laraclaude`.

## Sponsors

Laractions is supported by the following sponsors. Thank you for keeping it growing:

<p>
  <a href="https://kenodo.com"><img src="art/logo-kenodo.png" width="24" alt="Kenodo"></a>&nbsp;<a href="https://kenodo.com">Kenodo</a>&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="https://andorradev.com"><img src="art/logo-andorradev.png" width="24" alt="AndorraDev"></a>&nbsp;<a href="https://andorradev.com">AndorraDev</a>
</p>

## Author

Created by [Edu Lazaro](https://edulazaro.com)

## License

Laractions is open-sourced software licensed under the [MIT license](LICENSE.md).