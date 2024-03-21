# Laravel Model PHPDoc Generator

[![Latest Stable Version](https://img.shields.io/packagist/v/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![Total Downloads](https://img.shields.io/packagist/dt/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![License](https://img.shields.io/packagist/l/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![GitHub Build Status](https://img.shields.io/github/actions/workflow/status/romanzipp/Laravel-Model-Doc/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/romanzipp/Laravel-Model-Doc/actions)

Generate PHPDoc comments for Laravel Models including [**database columns**](https://laravel.com/docs/eloquent), [**relationships**](https://laravel.com/docs/eloquent-relationships), [**accessors**](https://laravel.com/docs/eloquent-mutators#accessors-and-mutators), [**query scopes**](https://laravel.com/docs/eloquent#query-scopes) and [**factories**](https://laravel.com/docs/eloquent-factories).

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
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * 
 * @method static \Database\Factoies\MyUserFactory<self> factory($count = null, $state = [])
 */
class MyUser extends Model
{
    use HasFactory;

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
    
    protected static function newFactory()
    {
        return new \Database\Factoies\MyUserFactory();
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

In older Laravel versions, `database.php` config file contained `dbal` section with `types` key, which allowed to define custom database types. Starting Laravel 11, that section was removed and Laravel can retrieve custom database types from the database itself as string.

This package uses the same approach and maps database types to PHP types as defined in [DocumentationGenerator.php](src/Services/DocumentationGenerator.php#L640). All unknown database types are treated as `mixed` by default. If you are using custom database types or want to map them to specific PHP types, you can add custom type mappings to the `model-doc.php` config file. You can either use a class name, string representation of PHP native type or a combination of both. `null` type will be added automatically if the column is nullable, so you don't need to add it manually here.

Here are some examples:

```php
'attributes' => [
    'custom_mappings' => [
        'my_type' => 'int',
        'my_other_type' => 'string|int',
        'my_class_type' => \Illuminate\Support\Carbon::class,
        'my_other_class_type' => \Illuminate\Support\Carbon::class . '|int',
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

### SQLite

```
./vendor/bin/phpunit
```

### MariaDB

Requires [Lando](https://lando.dev/).

```
lando start
lando phpunit
```
