# Changelog

All notable changes to `flysystem-uploadcare` will be documented in this file.

## [Unreleased]

### Added
- Laravel 12 support

### Changed
- Minimum PHP version requirement increased to PHP 8.2
- Updated development dependencies for Laravel 12 compatibility
  - Updated `nunomaduro/collision` to ^8.0
  - Updated `orchestra/testbench` to ^9.0
  - Updated `pestphp/pest` to ^3.0
  - Updated `pestphp/pest-plugin-laravel` to ^3.0
  - Updated `phpunit/phpunit` to ^11.0
  - Replaced `nunomaduro/larastan` with `larastan/larastan` ^3.0 for Laravel 12 support
  - Updated `phpstan/phpstan` to ^2.1 for Larastan compatibility
  - Updated `phpstan/phpstan-deprecation-rules` to ^2.0
  - Updated `phpstan/phpstan-phpunit` to ^2.0

## [0.1.3] - Previous Release

* v0.1.3 Renamed `public_key`/`private_key` to `public`/`private`
* v0.1.2 Added fileInfo()
* v0.1.0 Initial version