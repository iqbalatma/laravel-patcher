LARAVEL PATCHER 
--
*A (migration like) patcher for a smoldering production update.* <br>

[![Total Downloads](https://poser.pugx.org/dentro/laravel-patcher/downloads)](https://packagist.org/packages/dentro/yalr)
![GitHub Workflow Status](https://github.com/digital-entropy/laravel-patcher/workflows/tests/badge.svg)

#### Requirements:
* PHP : 8.\*
* Laravel: 9.\* 

##### Background : 
Once upon a time, our team do a stupid thing that affect our production database. 
It was happens many times, and we are usually go tinkering or direct edit on a 
database to fix those problems.
Then, our team leader [Rifki Alhuraibi](https://github.com/veelasky/) comes up with
the idea about the package that handle history change of our patch activity (like the 
one in database migration), so we made this package. 
Also, we commonly need to bulk insert data to our application, this package also help
us in those activity.

### INSTALLATION
do either of this methods below.
* via shell 
```shell script
composer require dentro/laravel-patcher
``` 
* adding `"dentro/laravel-patcher": "^1.0"` to `composer.json`
```json
{
  "require": {
    ...
    "dentro/laravel-patcher": "^1.0"
  }
}
```
### POST INSTALLATION 
> this process is optional, you can skip it though. 

patches table creation.
```shell script
 php artisan patcher:install
```
    
### USAGE 
#### CREATE NEW PATCH 
for creating new patch you just need to run these following command 
```shell script
php artisan make:patch what_do_you_want_to_patch
```
after run those command, you will see new file in `patches` folder. 
Those file will be like:
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
in ```Dentro\Patcher\Patch``` there is some useful properties 
that you can use for supporting your patch such as: 
1. `$container: \Illuminate\Container\Container`
2. `$command: \Illuminate\Console\Command`

    > we frequently used `$command` property to print process that we're doing.
    example: 
    > ```php
    > $this->command->warn('i patch something danger!');
    > $this->command->confirm('do you wish to continue?');
    > ```
    > you can learn more about `\Illuminate\Console\Command` [here](https://laravel.com/api/9.x/Illuminate/Console/Command.html).

3. `$logger: \Illuminate\Log\Logger`

    > `$logger` will store log in `storage/logs/patches.log`. if you want to change it, add this line below in your `config/logging.php` in channels section.  
    > ```php
    > [
    >     'channels' => [
    >         'patcher' => [
    >              'driver' => 'patcher', // you can change me if you want
    >              'path' => storage_path('logs/patches.log'), // change me
    >          ],
    >     ],
    > ];
    > ```
    > you can learn more about `\Illuminate\Log\Logger` [here](https://laravel.com/api/8.x/Illuminate/Log/Logger.html)
#### SHOW PATCH STATUS
```shell script
php artisan patcher:status
```
Example: 
```shell script
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
➜ php artisan patcher:status
Patches table created successfully.
Patching: 2020_09_29_190531_fix_double_sections
Patched:  2020_09_29_190531_fix_double_sections (0.03 seconds)
Patching: 2020_10_09_124616_add_attachment_beep
Patched:  2020_10_09_124616_add_attachment_beep (0.06 seconds)
```

