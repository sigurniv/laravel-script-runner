# Laravel package for running script migrations

## Overview
This Laravel package is a slightly modified version of https://github.com/illuminate/database package. It uses the same
migration principle to run user defined scripts.
For example you may need to send a bunch of push notifications. In this case you can create a script migration and run
it after you deploy your code:
```bash
$ php artisan script-runner:migrate 
```

### Installation
You'll have to follow a couple of simple steps to install this package.

### Downloading
Via [composer](http://getcomposer.org):

```bash
$ composer require sigurniv/laravel-script-runner "^1.0" 
```

### Registering the service provider
If you're using Laravel 5.5 or above, you can skip this step. The service provider will have already been 
registered thanks to auto-discovery. Otherwise you need to add Sigurniv\LaravelScriptRunner\LaravelScriptRunnerServiceProvider to your providers
 array.
 
### Publish config
```bash
$ php artisan vendor:publish
```
This will create script-runner.php inside config folder. Additionally it will create database/script-runner-migrations
folder to keep your generated script migration files.
You can configure your script migration table name:
```php
// config/script-runner.php
return [
    'migration_table' => 'laravel_script_runner_migrations'
];
```

### Available commands
If you now run `php artisan` you will see new commands in the list:
- `make:script-runner:migration`
- `script-runner:migrate`

These are analogs of default database migrate commands.