# Laravel Supervisor Config Generator

[![Build Status](https://travis-ci.org/matriphe/laravel-supervisor-config-generator.svg?branch=master)](https://travis-ci.org/matriphe/laravel-supervisor-config-generator)
[![Total Download](https://img.shields.io/packagist/dt/matriphe/supervisor.svg)](https://packagist.org/packages/matriphe/supervisor)
[![Latest Stable Version](https://img.shields.io/packagist/v/matriphe/supervisor.svg)](https://packagist.org/packages/matriphe/supervisor)

This package generates Supervisor config that used by Laravel to monitor queue worker. Make sure Supervisor is installed properly.

## Installation

Using [Composer](https://getcomposer.org/), just run this command below.

```bash
composer require matriphe/supervisor
```

## Configuration

### Laravel < 5.5

After installed, open `config/app.php` and find this line.

```php
Matriphe\Supervisor\ServiceProvider::class
``` 

### Laravel > 5.5

Nothing to do, this package is using package auto-discovery.

## Usage

Using `root` access, run

```bash
php artisan supervisor:config
```

By default, this will save the configuration file to `/etc/supervisord/conf.d` directory. To change this, use `--path` option on the command.

For more info, just use `--help` option to see what options available.

### Outout

The output of the config file is like this.

```conf
[program:appname-default]
command=php /Volumes/data/Development/php/laravel/55/artisan queue:work --queue=default --tries=3 --timeout=60
process_num=5
numprocs=5
process_name=%(process_num)s
priority=999
autostart=true
autorestart=unexpected
startretries=3
stopsignal=QUIT
stderr_logfile=/var/log/supervisor/appname-default.log
```

The file will be named `/etc/supervisord/conf.d/appname-default.conf`.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
