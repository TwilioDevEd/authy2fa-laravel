
# Two Factor Auth (TFA or 2FA) in Laravel 5.0

## Setup 

my guess is that we're assuming you already have this done, documenting for my benefit

### MAMP or similar? 

### [Install composer](https://getcomposer.org/download/)
if you haven't already
  
```
curl -sS https://getcomposer.org/installer | php
```

Or maybe update composer: 
```
/usr/local/bin/composer self-update
```

### Install Laravel 5.0

```
composer global require "laravel/installer=~1.1"
```

### Install homestead for VM? 
* punting on this for now

### Create a new project

```
laravel new two-factor-auth
```

Start MAMP for MySQL
Update ```database.php``` with mysql settings


## Include Authy PHP developer kit

(Following copy is from [PHP Client for Authy Github](https://github.com/authy/authy-php))

Include it in your ```composer.json```
```json
{
    "require": {
        "authy/php": "2.*"
    }
}
```

## Users

This is from Authy and Laravel post mentioned below in resources: 

```
php artisan make:migration create_users_table
```

Make it look like this: 

```php
<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('users', function(Blueprint $table)
    {
      $table->increments('id');
      $table->string('email');
      $table->string('username');
      $table->string('password');
      $table->string('authy')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('users');
  }

}
```

To use this client you just need to use Authy_Api and initialize it with your API KEY

```
$authy_api = new Authy\AuthyApi('#your_api_key');
```

Creating users is very easy, you need to pass an email, a cellphone and optionally a country code:

```
$user = $authy_api->registerUser('new_user@email.com', '405-342-5699', 1); //email, cellphone, country_code
```

You can easily see if the user was created by calling ok(). If request went right, you need to store the authy id in your database. Use user->id() to get this id in your database.

```
if($user->ok())
  // store user->id() in your user database
else
  foreach($user->errors() as $field => $message) {
    printf("$field = $message");
  }
```

it returns a dictionary explaining what went wrong with the request. Errors will be in plain English and can be passed back to the user.

## Resources
  * Laracasts - [What's new in 5.0?](https://laracasts.com/series/whats-new-in-laravel-5)
  * [Laravel 5 docs](http://laravel.com/docs/5.0)
  * [This dude's post](http://blog.enge.me/post/installing-two-factor-authentication-with-authy) on Authy and Laravel
  * [Authy's PHP developer kit](https://github.com/authy/authy-php)

## Targeted Search Phrases: 
Think we're just going hard after combinations of: 
* Laravel
* Laravel 5.0
* Two factor auth
* 2FA
* TFA
