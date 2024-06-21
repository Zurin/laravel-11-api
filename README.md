<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


# Library API

Simple RESTful API for a library management system


## Requirement

- [Laravel 11](https://laravel.com/docs/11.x/releases#laravel-11)
- [PHP 8.2](https://www.php.net/releases/8.2/en.php) - [PHP 8.2](https://www.php.net/releases/8.3/en.php)
- [Composer](https://getcomposer.org/)
- [MySQL](https://www.mysql.com/)
- [Redis](https://redis.io/)

## Installation

- Create sql database first, then make sure setting it correctly on **.env**, **.env.testing**, and **phpunit.xml** like this:

#### .env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=library
DB_USERNAME=root
DB_PASSWORD=
```

#### .env.testing
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=testing_database
DB_USERNAME=root
DB_PASSWORD=
```

#### .phpunit.xml
```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="testing_database"/>
<env name="DB_USERNAME" value="root"/>
<env name="DB_PASSWORD" value=""/>
```

**Note: please make sure to separate application & testing database to prevent data loss*

- Install dependency with composer
```bash
composer install
```

- Migrate database schema
```bash
php artisan migrate
```

- Run it with
```bash
php artisan serve
```


## API Reference
API are accessible with base url [{your_base_url}/api/]()

All API documentations could be accessed at swagger on:
```
{your_base_url}/api/documentation
```
![Logo](https://i.ibb.co.com/HNC4qh1/Screenshot-2024-06-21-204850.png)



## Unit Testing

For unit testing, just run this single command:
```bash
php artisan test
```
![Logo](https://i.ibb.co.com/rMqddDq/Screenshot-2024-06-21-210052.png)


