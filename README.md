# Laractions - Actions for Laravel

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

##  Creating Actions

You can manually create an action or use the **artisan command**:

```bash
php artisan make:action SendEmailAction
```

This will generate this basic action:

```php
namespace App\Actions;

use EduLazaro\Laractions\Action;

class SendEmailAction extends Action
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

class SendEmailAction extends Action
{
    public function handle(string $email, string $subject, string $message)
    {
        // Your action logic here
    }
}
```

You can then run the action via the `run` method:

```php
SendEmailAction::create()->run('user@example.com', 'Welcome!', 'Hello User');
```

You can customize the constructor. Dependencies will be injected:

```php
namespace App\Actions;

use EduLazaro\Laractions\Action;
use App\Services\MailerService;

class SendEmailAction extends Action
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
SendEmailAction::create()->run('user@example.com', 'Welcome!', 'Hello User');
```

##  Creating Model Actions

You can manually create a model action or use the **artisan command**:

```bash
php artisan make:action SendEmailAction --model=User
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
        'send_email' => SendEmailAction::class,
    ];
}
```

Now, you can execute the action using:

```php
$user->action('send_email')->run('user@example.com', 'Welcome!', 'Hello User');
```

Alternatively, you can still call the action class directly so you don't have to define the action inside the model:

```php
$user->action(SendEmailAction::class)->run('user@example.com', 'Welcome!', 'Hello User');

```

##  Dynamic Parameters

Laractions provides a flexible `with()` method to set action attributes dynamically:

```php
$action = SendEmailAction::create()->with([
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
$user->action(SendEmailAction::class)->run();
```

If the `SendEmailAction` class has a `$user` property, the action will automatically set the model:

```php
class SendEmailAction extends Action
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
$action = SendEmailAction::create()
    ->queue('high')
    ->delay(10)
    ->retry(5)
    ->dispatch('user@example.com', 'Welcome!', 'Hello User');
```
This queues the action instead of executing it immediately. The job will be automatically created and liked to the action, so you don't need to define it.

You can configure how actions are dispatched as jobs:

```php
class SendEmailAction extends Action
{
    protected int $tries = 5;
    protected ?int $delay = 30;
    protected ?string $queue = 'emails';
}
```

##  Mocking Actions for Tests

During unit tests, you can **mock actions**:

```php
$user->mockAction(SendEmailAction::class, new class {
    public function run()
    {
        return 'Mocked!';
    }
});

echo $user->action(SendEmailAction::class)->run(); // Output: 'Mocked!'
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
SendEmailAction::create()
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
$user->act(SendInvoiceAction::class)
     ->on($order)
     ->trace()
     ->run();
```

This automatically sets the actor on the action before executing it.

## Enabling Tracing

Tracing is disabled by default. You can enable it per action like this:

```php
SendEmailAction::create()
    ->trace()
    ->run('user@example.com', 'Welcome!', 'Hello!');
```

You can assign the actor and actionable model like so:

```php
SendEmailAction::create()
    ->actor($user)
    ->on($targetModel)
    ->trace()
    ->run($params);
```

Here is an traced action started by an actor:

```php
$user->act(SendInvoiceAction::class)
     ->on($order)
     ->trace()
     ->run();
```

## License

Laractions is open-sourced software licensed under the [MIT license](LICENSE.md).