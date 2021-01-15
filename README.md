# Laravel Model Doc Generator

[![Latest Stable Version](https://img.shields.io/packagist/v/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![Total Downloads](https://img.shields.io/packagist/dt/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![License](https://img.shields.io/packagist/l/romanzipp/Laravel-Model-Doc.svg?style=flat-square)](https://packagist.org/packages/romanzipp/laravel-model-doc)
[![GitHub Build Status](https://img.shields.io/github/workflow/status/romanzipp/Laravel-Model-Doc/Tests?style=flat-square)](https://github.com/romanzipp/Laravel-Model-Doc/actions)

## Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Testing](#testing)

## Installation

```
composer require romanzipp/laravel-model-doc
```

## Configuration

Copy configuration to config folder:

```
$ php artisan vendor:publish --provider="romanzipp\ModelDoc\Providers\ModelDocServiceProvider"
```

## Usage

## Testing

```
./vendor/bin/phpunit
```
