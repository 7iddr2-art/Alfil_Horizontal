# CakePHP Application Skeleton

[![Total Downloads](https://img.shields.io/packagist/dt/cakephp/app.svg?style=flat-square)](https://packagist.org/packages/cakephp/app)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)

A skeleton for creating applications with [CakePHP](https://cakephp.org) 5.x.

The framework source code can be found here: [cakephp/cakephp](https://github.com/cakephp/cakephp).

## Installation

1. Download [Composer](https://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
2. Run `php composer.phar create-project --prefer-dist cakephp/app [app_name]`.

If Composer is installed globally, run

```bash
composer create-project --prefer-dist cakephp/app
```

In case you want to use a custom app dir name (e.g. `/myapp/`):

```bash
composer create-project --prefer-dist cakephp/app myapp
```

You can now either use your machine's webserver to view the default home page, or start
up the built-in webserver with:

```bash
bin/cake server -p 8765
```

Then visit `http://localhost:8765` to see the welcome page.

## Demo app

Check out the [5.x-demo branch](https://github.com/cakephp/app/tree/5.x-demo), which contains demo migrations and a seeder.
See the [README](https://github.com/cakephp/app/blob/5.x-demo/README.md) on how to get it running.

## Update

Since this skeleton is a starting point for your application and various files
would have been modified as per your needs, there isn't a way to provide
automated upgrades, so you have to do any updates manually.

## Configuration

Read and edit the environment specific `config/app_local.php` and set up the
`'Datasources'` and any other configuration relevant for your application.
Other environment agnostic settings can be changed in `config/app.php`.

## Local development and tests

The application targets PHP 8.2+ and Oracle MySQL 8.4. Create separate, empty
development and test schemas and separate least-privilege database users before
connecting the application. Never point the test datasource at a database
containing data to preserve: `tests/bootstrap.php` runs migrations against it.

Copy `config/app_local.example.php` to the ignored `config/app_local.php` and
provide the connection values through environment variables:

| Variable | Purpose |
|---|---|
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Development/default datasource |
| `TEST_DB_HOST`, `TEST_DB_PORT`, `TEST_DB_DATABASE`, `TEST_DB_USERNAME`, `TEST_DB_PASSWORD` | Dedicated test datasource |
| `SECURITY_SALT` | Local cryptographic salt; use a unique random value |

`config/app_local.php` has precedence over environment-backed defaults. Keep
credentials and salts out of source control. The example config keeps the test
datasource separate and disables URL-based SQLite fallback.

On Windows PowerShell, with PHP and Composer on `PATH`:

```powershell
composer install
php bin\cake.php migrations status
php bin\cake.php server -H 127.0.0.1 -p 8765
```

The first command installs dependencies; migration status is read-only. Apply
development migrations explicitly with `php bin\cake.php migrations migrate`
only after verifying that `DB_DATABASE` names the dedicated development
schema. Run the test suite with `composer test`; it applies migrations only to
`TEST_DB_DATABASE`. The GitHub Actions workflow creates isolated MySQL 8.4 schemas/users, lints
the updated test/configuration files, and runs the suite.

## Layout

The app skeleton uses [Milligram](https://milligram.io/) (v1.3) minimalist CSS
framework by default. You can, however, replace it with any other library or
custom styles.
