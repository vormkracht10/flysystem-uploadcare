# Flysystem driver for Uploadcare for Laravel.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vormkracht10/flysystem-uploadcare.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/flysystem-uploadcare)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/flysystem-uploadcare/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/vormkracht10/flysystem-uploadcare/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/vormkracht10/flysystem-uploadcare/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/vormkracht10/flysystem-uploadcare/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vormkracht10/flysystem-uploadcare.svg?style=flat-square)](https://packagist.org/packages/vormkracht10/flysystem-uploadcare)

Flysystem driver for Uploadcare for Laravel 10 and up.

## Installation

You can install the package via composer:

```bash
composer require vormkracht10/flysystem-uploadcare
```

Add the following config to the `disk` array in config/filesystems.php

```php
[
    'uploadcare' => [
        'driver' => 'uploadcare',
        'public_key' => env('UPLOADCARE_PUBLIC_KEY'),
        'secret_key' => env('UPLOADCARE_SECRET_KEY'),
    ]
]
```

Then set the `FILESYSTEM_DISK` to `uploadcare` in your .env

```env
FILESYSTEM_DISK=uploadcare
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Vormkracht10](https://github.com/vormkracht10)
- [Mathieu](https://github.com/casmo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
