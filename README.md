# Laravel Paysera
Package that helps to use Paysera API in laravel application.

## Installation
First require package with composer:
```sh
$ composer require artme/laravel-paysera
```
Then add service provider to config/app.php:
```php
'providers' => [
    ...
    Artme\Paysera\PayseraServiceProvider::class,
],
```
Facede to aliases:
```php
'aliases' => [
    ...
    'Paysera'   => Artme\Paysera\PayseraFacade::class,
],
```
And last is to publish config, migrations and view:
```sh
$ php artisan vendor:publish --provider="Artme\Paysera\PayseraServiceProvider"
$ php artisan migrate
```

## Usage

### Get payment methods
```php
Paysera::getPaymentMethods($locale, $groups);
```
Both parameters for this method is optional. `$locale` have to be string of locale key.
By default `$locale` will be set by application locale (`App::getLocale()`). `$groups`
 variable sets what payment method to get and have to be array. For example if we want
 only e-banking methods, we can set `['e-banking']`. Result of this method will be 
 array with payment methods and information about them.
