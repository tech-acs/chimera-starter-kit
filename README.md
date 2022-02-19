# Census (CSPro) dashboard starter kit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/uneca/census-dashboard-starter-kit.svg?style=flat-square)](https://packagist.org/packages/uneca/census-dashboard-starter-kit)
[![Total Downloads](https://img.shields.io/packagist/dt/uneca/census-dashboard-starter-kit.svg?style=flat-square)](https://packagist.org/packages/uneca/census-dashboard-starter-kit)

Census dashboard starter kit is a CSPro questionnaire dashboard application scaffolding for Laravel. It provides the perfect starting point for your dashboard and includes various features.

It is built on top of Laravel Jetstream starter kit.

## Installation

Once you have created a fresh laravel project, you can install the package via composer:

```bash
composer require uneca/census-dashboard-starter-kit
```

Then install the kit using:

```bash
php artisan chimera:install
```

Then edit your .env file so as to add your postgres database (you also need to add the postgis extension to it) details etc.

Then run the migrations

```bash
php artisan migrate
```

After installing the kit, you should install and build your NPM dependencies:
```bash
npm install
npm run dev
```

Finally you can run the adminify command to create a super admin user with which you can access your new dashboard
```bash
php artisan adminify
```


## Usage

Coming soon...

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
