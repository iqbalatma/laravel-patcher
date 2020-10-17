LARAVEL PATCHER 
--
*A (migration like) patcher for a smoldering production update.* <br>

Already tested on: 
* Laravel: 6.* | 7.\* | 8.\* 

##### Background : 
Our team has been made a stupid thing that affect database in our project. 
It was happens many times, and we are usually go tinkering or direct edit on a 
database to fix those problems. The problem is we need to record those change, 
so we made this package. Besides, we sometime need to doing 
bulk insert user for our application, so ***patch*** will be the best solution.

### INSTALLATION
do either of this methods below.
* via shell 
```shell script
composer require jalameta/jps-patcher
``` 
* adding to `composer.json`
```json
{
  "require": {
    ...
    "jalameta/jps-patcher": "^2.0",
    ...
  }
}
```
### POST INSTALLATION 
> this process is optional, you can skip it though. 

1. applying into your project.
    * Laravel >= 5.8
        * Automatically loaded :)
    
    * Laravel <= 5.8 
        * Add the `\Jalameta\Patcher\PatcherServiceProvider` into `providers` 
        array in `config/app.php`

2. patches table creation.
    ```shell script
     php artisan patcher:install
    ```
   
### USAGE 
#### CREATE NEW PATCH 
for creating new patch you just need to run these following command 
```shell script
php artisan make:patch what_do_you_want_to_patch
```
wait, do you feels familiar huh?, we do too. 
This library using laravel migration under the hood.
after that, you will see those file in `patches` folder. Those file will be like:
```php
<?php

use Jalameta\Patcher\Patch;

class WhatDoYouWantToPatch extends Patch
{
    /**
     * Run patch script.
     *
     * @return void
     * @throws \Exception
     */
    public function patch()
    {
        // 
    }
}
```
Method `patch` on these file will be filled with your logic. 
in ```Jalameta\Patcher\Patch``` there is some useful properties 
that you can use for supporting your patch such as: 
1. `$container: \Illuminate\Container\Container`
2. `$command: \Illuminate\Console\Command`

we frequently used `$command` property to print process that we're doing.
example: 
```php
$this->command->warn('i patch something danger!');
$this->command->confirm('do you wish to continue?');
```
you can learn more about `\Illuminate\Console\Command` [here](https://laravel.com/api/8.x/Illuminate/Console/Command.html).

#### SHOW PATCH STATUS
```shell script
php artisan patcher:status
```
Example: 
```shell script
my_project on  master [$!] via ⬢ v14.14.0 via 🐘 v7.4.11 on 🐳 v19.03.13 
➜ php artisan patcher:status
+------+---------------------------------------+-------+
| Ran? | Patch                                 | Batch |
+------+---------------------------------------+-------+
| Yes  | 2020_09_29_190531_fix_double_sections | 1     |
| Yes  | 2020_10_09_124616_add_attachment_beep | 1     |
+------+---------------------------------------+-------+
```

#### RUN A PATCH(ES)
```shell script
php artisan patcher:run
```
Example:
```shell script
my_project on  master [$!] via ⬢ v14.14.0 via 🐘 v7.4.11 on 🐳 v19.03.13 
➜ php artisan patcher:status
Patches table created successfully.
Patching: 2020_09_29_190531_fix_double_sections
Patched:  2020_09_29_190531_fix_double_sections (0.03 seconds)
Patching: 2020_10_09_124616_add_attachment_beep
Patched:  2020_10_09_124616_add_attachment_beep (0.06 seconds)
```

