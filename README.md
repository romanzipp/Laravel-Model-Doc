# Laravel Model PHPDoc Generator

[![Latest Stable Version](https://img.shields.io/packagist/v/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![Total Downloads](https://img.shields.io/packagist/dt/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![License](https://img.shields.io/packagist/l/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![GitHub Build Status](https://img.shields.io/github/workflow/status/romanzipp/Laravel-Model-Doc/Tests?style=flat-square)](https://github.com/romanzipp/Laravel-Model-Doc/actions)

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

See the [configuration file](config/model-doc.php) for more specific use cases.

## Features

- [x] Generate `@property` tags from attributes
- [ ] Look for attributes type casts
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
