## Database Queries Logger for Laravel 5.3+
[![Build Status](https://travis-ci.org/klaravel/db-logger.svg)](https://travis-ci.org/klaravel/db-logger)
[![Total Downloads](https://poser.pugx.org/klaravel/db-logger/d/total.svg)](https://packagist.org/packages/klaravel/db-logger)
[![Latest Stable Version](https://poser.pugx.org/klaravel/db-logger/v/stable.svg)](https://packagist.org/packages/klaravel/db-logger)
[![Latest Unstable Version](https://poser.pugx.org/klaravel/db-logger/v/unstable.svg)](https://packagist.org/packages/klaravel/db-logger)
[![License](https://poser.pugx.org/klaravel/db-logger/license.svg)](https://packagist.org/packages/klaravel/db-logger)

This module allows you to log SQL queries (and slow SQL queries) to log file in Laravel framework. It's useful mainly
when developing your application to verify whether your queries are valid and to make sure your application doesn't run too many or too slow database queries.

### Installation:

1. Run
   ```php
   composer require klaravel/db-logger
   ```     
   in console to install this module

2. Open `config/app.php` and in `providers` section add:
 
    ```php
    Klaravel\DbLogger\ServiceProvider::class,
    ```
    
3. Run:
    
    ```php
    php artisan vendor:publish --provider="Klaravel\DbLogger\ServiceProvider"
    ```
    
    in your console to publish default configuration files
    
4. Open `config/dblogger.php` file and adjust settings to your need (by default it uses `.env` file so you can skip this step if you want).

5. In your .env file add the following entries:

    ```
    DB_LOG_QUERIES=true  # if not needed make it false
    DB_LOG_SLOW_QUERIES=true # if not needed make it false
    DB_SLOW_QUERIES_MIN_EXEC_TIME=100
    DB_LOG_OVERRIDE=false
    DB_LOG_DIRECTORY=logs/db
    DB_CONVERT_TIME_TO_SECONDS=false
    DB_LOG_SEPARATE_ARTISAN=false
    ```
    
    and adjust values to your needs. If you have also `.env.sample` it's also recommended to add those entries also in `.env.sample` file just to make sure everyone know about those env variables. Be aware that `DB_LOG_DIRECTORY` is directory inside storage directory. If you want you can change it editing `config/dblogger.php` file.

---
#### Note:
> Make sure defined `DB_LOG_DIRECTORY` directory should be exists default direcotry is `\storage\logs\db\` else application will throw error.