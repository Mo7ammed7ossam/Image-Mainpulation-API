## Laravel Image Manipulation REST API
simple API for resizeing images using laravel and image-intervention framework

## Simple demo

#### Test locally
Download [postman_collection.json](postman_collection.json) file, import it in your postman and test locally.

## Prerequisites

#### PHP Extensions
`php-mbstring - php-dom - php-intl - php-curl - php-mysql - php-gd`

## Basic installation steps 
Before you start the installation process you need to have **installed composer**

1. Clone the project
2. Navigate to the project root directory using command line
3. Run `composer install`
4. Copy `.env.example` into `.env` file
5. Update your `DataBase` parameters. 
   If you want to use Mysql, make sure you have mysql server up and running.
   If you want to use sqlite: 
   1. you can just delete all `DataBase` parameters except `DB_CONNECTION` and set its value to `sqlite`
   2. Then create file `database/database.sqlite`
6. Run `php artisan key:generate --ansi`
7. Run `php artisan migrate`

### Installing locally for development
Run `php artisan serve` which will start the server at http://localhost:8000 <br>
