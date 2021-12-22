# Census (CSPro) dashboard starter kit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/uneca/census-dashboard-starter-kit.svg?style=flat-square)](https://packagist.org/packages/uneca/census-dashboard-starter-kit)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/uneca/census-dashboard-starter-kit/run-tests?label=tests)](https://github.com/uneca/census-dashboard-starter-kit/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/uneca/census-dashboard-starter-kit/Check%20&%20fix%20styling?label=code%20style)](https://github.com/uneca/census-dashboard-starter-kit/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/uneca/census-dashboard-starter-kit.svg?style=flat-square)](https://packagist.org/packages/uneca/census-dashboard-starter-kit)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require uneca/census-dashboard-starter-kit
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="census-dashboard-starter-kit_without_prefix-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --tag="census-dashboard-starter-kit_without_prefix-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="example-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$census-dashboard-starter-kit = new Uneca\CensusDashboardStarterKit();
echo $census-dashboard-starter-kit->echoPhrase('Hello, Uneca!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [UNECA](https://github.com/tech-acs)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
