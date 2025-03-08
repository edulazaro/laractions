# Keepers for Laravel

<p align="center">
    <a href="https://packagist.org/packages/laravel/folio"><img src="https://img.shields.io/packagist/dt/edulazaro/larakeep" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/laravel/folio"><img src="https://img.shields.io/packagist/v/edulazaro/larakeep" alt="Latest Stable Version"></a>
</p>


## Introduction

Larakeep is a package which allows to create and handle Keepers. Keepers allow to maintain the value of the desired model fields.

Sometimes, a field value must be configured when creating or updating a model instance. A common place to do this are the observers. This can lead to bloat the observers with repeated code or bloating the the models with functions, resulting in big files.

keepers allow to set the value of the desired fields on separate classes, keeping the code cleaner.

## How to install Larakeep

Execute this command on the Laravel root project folder:

```bash
composer require edulazaro/larakeep
```

## How to crate a Keeper

You can create a Keeper manually or using the `make` command:

```bash
php artisan make:keeper MyModelKeeper
```

The Keeper will be created by default for the model `\App\Models\MyModel`.

You can also specify the model you are creating the Keeper for by using a second argument:

```bash
php artisan make:keeper MyClassKeeper  \App\Models\Whatever\MyModel
```

In this case, the keeper will be created for the model `\App\Models\Whatever\MyModel`.

## How to configure a Keeper

After creating a Keeper, you will need to add the `HasKeepers` concern to the referenced model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use EduLazaro\Larakeep\Concerns\HasKeepers;

class MyModel extends Model
{
    use HasKeepers;
}
```

Then, you will need to assign the keeper to the model on the `boot` method of any service provider:

```php
namespace App\Providers;

use App\Models\MyModel;
use App\Keepers\MyModelKeeper;

class AppServiceProvider extends Model
{
    // ...
    public function boot()
    {
        // ...
        MyModel::keep(MyModelKeeper::class);
    }
}
```

You can add many Keepers to a single model:

```php
MyModel::keep(MyModelKeeperA::class);
MyModel::keep(MyModelKeeperB::class);
```

## How to use a Keeper

To use Keeper you need to use the word `get` + the camel case version of the model attribute to maintain. For example, it's common to keep separate the model name and the search string for the model. In this case a keeper method is handy to set the attribute value:

```php
namespace App\Keepers;

use \App\Models\MyClass;

class MyClassKeeper
{
    // ...
    public function getSearchText()
    {
        return $this->myClass->name . ' ' . $this->myClass->tag;
    }
}
```

The method can also accept parameters, adding the keyword `With` to the method name:


```php
namespace App\Keepers;

use \App\Models\MyClass;

class MyClassKeeper
{
    // ...
    public function getSearchTextWith($whatever)
    {
        return $this->myClass->name . ' ' . $this->myClass->tag;
    }
}
```

Now, on an observer, you can maintain the `search_text` field to set its value:

```php
$myClassInstance->maintain('search_text'); // To execute the getSearchText method.
```

Or if the method has parameters:

```php
$myClassInstance->maintainWith('search_text', 'Any string'); // To execute the getSearchTextWith method.
```


You can also pass an array of fields to maintain all of them at the same time:

```php
$myClassInstance->maintain(['search_text', 'word_count']);
```

***The model will still need to be saved.***


## How to add Tasks

You can prepend any other word than`get` to the keeper methods, like `configure`:


```php
namespace App\Keepers;

use \App\Models\MyClass;

class MyClassKeeper
{
    // ...
    public function configureSearchText()
    {
        return $this->myClass->name . ' ' . $this->myClass->tag;
    }
}
```

In the same way, these methods can also accept parameters:


```php
namespace App\Keepers;

use \App\Models\MyClass;

class MyClassKeeper
{
    // ...
    public function configureSearchTextWith($whatever)
    {
        return $this->myClass->name . ' ' . $this->myClass->tag;
    }
}
```

However to maintain these fields with these methods you will need to use the `maintainTask` method, with the name you prepended:

```php
$myClassInstance->maintainTask('configure','search_text');
```

Or if the method has parameters:

```php
$myClassInstance->maintainTaskWith('configure', 'search_text', 'Any string');
```

You can also pass an array of fields to maintain all of them at the same time:

```php
$myClassInstance->maintainTask('configure', ['search_text', 'word_count']);
```

***The model will still need to be saved.***

## License

Larakeep is open-sourced software licensed under the [MIT license](LICENSE.md).