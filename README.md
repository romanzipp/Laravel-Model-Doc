# Laravel Model PHPDoc Generator

[![Latest Stable Version](https://img.shields.io/packagist/v/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![Total Downloads](https://img.shields.io/packagist/dt/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![License](https://img.shields.io/packagist/l/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![GitHub Build Status](https://img.shields.io/github/workflow/status/romanzipp/Laravel-Model-Doc/Tests?style=flat-square)](https://github.com/romanzipp/Laravel-Model-Doc/actions)

Generate PHPDoc comments for Laravel Models including **columns**, **relations** and **scopes**.

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
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
    protected $table = 'models'; // 1. Add the corresponding talbtable name
    
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
        // $builder->whereHas('teams', fn (Builder $builder) => $builder->where('name', $name));
    }
}
```

See the [configuration file](config/model-doc.php) for more specific use cases.

## Testing

```
./vendor/bin/phpunit
```
