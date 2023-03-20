# Laravel Model PHPDoc Generator

[![Latest Stable Version](https://img.shields.io/packagist/v/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![Total Downloads](https://img.shields.io/packagist/dt/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![License](https://img.shields.io/packagist/l/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![GitHub Build Status](https://img.shields.io/github/actions/workflow/status/romanzipp/Laravel-Model-Doc/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/romanzipp/Laravel-Model-Doc/actions)

Generate PHPDoc comments for Laravel Models including [**database columns**](https://laravel.com/docs/8.x/eloquent), [**relationships**](https://laravel.com/docs/8.x/eloquent-relationships), [**accessors**](https://laravel.com/docs/8.x/eloquent-mutators#accessors-and-mutators) and [**query scopes**](https://laravel.com/docs/8.x/eloquent#query-scopes).

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Features](#features)
- [Testing](#testing)

## Installation

```
composer require romanzipp/laravel-model-doc --dev
```

## Configuration

Copy configuration to config folder:

```
php artisan vendor:publish --provider="romanzipp\ModelDoc\Providers\ModelDocServiceProvider"
```

## Usage

```
php artisan model-doc:generate
```

See the [configuration file](config/model-doc.php) for more specific use cases.

### Prepare your models

1. Add the corresponding **table name**
2. Add **relation** methods return **types** 
3. Add **accessor** methods return **types**

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MyModel extends Model
{
    protected $table = 'models'; // 1. Add the corresponding table name
    
    public function teams(): HasMany // 2. Add relation methods return types
    {
        return $this->hasMany(Team::class);
    }
    
    public function getNameAttribute(): string // 3. Add accessor methods return types
    {
        return ucfirst($this->name);
    }
}
```

### Example

```php
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $title
 * @property string $pretty_title
 * @property string|null $icon
 * @property int $order
 * @property bool $enabled
 * @property array $children
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property \Illuminate\Database\Eloquent\Collection|\App\Models\Team[] $teams
 * @property int|null $teams_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder whereTeamName(string $name)
 */
class MyUser extends Model
{
    protected $table = 'users';

    protected $casts = [
        'children' => 'array',
    ];

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function scopeWhereTeamName(Builder $builder, string $name)
    {
        $builder->where('name', $name);
    }

    public function getPrettyTitleAttribute(): string
    {
        return ucfirst($this->title);
    }
}
```

### Set custom path

You can set a custom base path for the generator using the `usePath` static method.

```php
use Illuminate\Support\ServiceProvider;
use romanzipp\ModelDoc\Services\DocumentationGenerator;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        DocumentationGenerator::usePath(fn () => base_path('app/Models'));
    }
}
```

See the [configuration file](config/model-doc.php) for more specific use cases.

### Use verbose mode

If you get an error when generating the documentation for a model, you can use the `--v` option to get more information about the error.

```
php artisan model-doc:generate --v
```

### Custom database types

If (in verbose mode) you get an error like `Unknown database type enum requested`, you can add that custom type mapping in Laravel's `database.php` config file. Laravel uses the [Doctrine DBAL](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html) package for database types. You can find a list of supported types [here](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html#mapping-matrix).
Laravel provides an example for `timestamp` type mapping [here](https://laravel.com/docs/10.x/migrations#modifying-columns-on-sqlite).

Here is an example for `enum` type mapping in `database.php` config file:

```php
'dbal' => [
    'types' => [
        'enum' => Doctrine\DBAL\Types\StringType::class,
    ],
],
```

## Features

- [x] Generate `@property` tags from attributes
- [x] Look for attributes type casts
- [x] Do not generate attribute `@property` tag if accessor exists
- [x] Generate `@method` tags from relationships
- [x] Generate `@property` tags from relationships
- [x] Generate `@property` tags from relationship counts
- [x] Generate `@method` tags query scopes
- [x] Generate `@property` tags from accessors
- [ ] Only generate `@property-readonly` if accessor has no real attribute or mutator

## Testing

```
./vendor/bin/phpunit
```
