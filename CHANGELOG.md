# Changelog

## [1.4.0](https://github.com/tomshaw/laravel-dropbox/compare/v1.3.0...v1.4.0) (2026-04-04)


### Features

* apply pint code style fixes and update linting workflow ([73dad98](https://github.com/tomshaw/laravel-dropbox/commit/73dad98ed45c1d918ea79269c02cfdd0cbd5d154))
* fix pint workflow to use project config and revert incorrect style changes ([b080c5a](https://github.com/tomshaw/laravel-dropbox/commit/b080c5a310b1511b8d231d23f387c4aa709dd714))
* use project pint version in CI instead of external action ([14c46b9](https://github.com/tomshaw/laravel-dropbox/commit/14c46b9535f7b96138d1d6f8b19bededdccbf028))

## [1.3.0](https://github.com/tomshaw/laravel-dropbox/compare/v1.2.0...v1.3.0) (2026-04-04)


### Features

* standardize CI workflows and update dependencies for Laravel 13 ([7ed1b9a](https://github.com/tomshaw/laravel-dropbox/commit/7ed1b9ad556a34a63a054e38afdb6b4a3fe2907c))


### Bug Fixes

* restore double backslashes in namespace paths (corrupted JSON) ([a7cd539](https://github.com/tomshaw/laravel-dropbox/commit/a7cd5395f7337e0acc0800196b5ba6feeaf0c595))


### Miscellaneous Chores

* update workflows and dependencies for compatibility with Laravel 13 ([e18f3ef](https://github.com/tomshaw/laravel-dropbox/commit/e18f3ef0d4ecdfd55f18c760074a4b2477c9c0c2))

## [1.2.0](https://github.com/tomshaw/laravel-dropbox/compare/v1.1.2...v1.2.0) (2026-02-01)


### Features

* add issue templates for bug reports, documentation issues, feature requests, general issues, improvements, and questions ([a5c41bb](https://github.com/tomshaw/laravel-dropbox/commit/a5c41bb567e554104e0d8d484179ce98df44c955))


### Miscellaneous Chores

* update dependencies and improve code structure for compatibility with Laravel 12 ([01d2415](https://github.com/tomshaw/laravel-dropbox/commit/01d24157ab802d71fafffbdaacf28bac7e3975d4))
* update PHPStan and Pint workflows to use latest action versions and PHP 8.5 ([a5c41bb](https://github.com/tomshaw/laravel-dropbox/commit/a5c41bb567e554104e0d8d484179ce98df44c955))
* update run-tests workflow to support Laravel 12 and PHP 8.5 ([a5c41bb](https://github.com/tomshaw/laravel-dropbox/commit/a5c41bb567e554104e0d8d484179ce98df44c955))

## [1.1.2](https://github.com/tomshaw/laravel-dropbox/compare/v1.1.1...v1.1.2) (2025-02-03)


### Miscellaneous Chores

* **deps:** add support for PHP 8.3 and 8.4 in composer.json ([08b54b2](https://github.com/tomshaw/laravel-dropbox/commit/08b54b260183be746bc33da2e79e14b21e40fbb5))

## [1.1.1](https://github.com/tomshaw/laravel-dropbox/compare/v1.1.0...v1.1.1) (2025-02-02)


### Miscellaneous Chores

* **deps:** add support for PHP 8.3 and 8.4 in composer.json ([67aa3a9](https://github.com/tomshaw/laravel-dropbox/commit/67aa3a9de20f29ba34cb019eea3bf25fbcc52340))

## [1.1.0](https://github.com/tomshaw/laravel-dropbox/compare/v1.0.0...v1.1.0) (2024-04-18)


### Features

* **upload:** refactor upload method to accept file paths ([a39999c](https://github.com/tomshaw/laravel-dropbox/commit/a39999c985deb68fb989d6fddbcdaeabfae69f39))

## [1.0.0](https://github.com/tomshaw/laravel-dropbox/compare/v0.2.3...v1.0.0) (2024-04-18)


### ⚠ BREAKING CHANGES

* **http:** The content parameter in HTTP requests is deprecated. Use the body parameter instead.

### Features

* Added Release Please Action ([e97fe99](https://github.com/tomshaw/laravel-dropbox/commit/e97fe99865dc377f47498beb14fab44c71a49c5f))


### Code Refactoring

* **http:** deprecate content parameter in favor of mixed type body ([a306940](https://github.com/tomshaw/laravel-dropbox/commit/a30694029a945064e5085fcf611f2f2976309f9a))
