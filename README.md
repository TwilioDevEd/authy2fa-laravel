<a href="https://www.twilio.com">
  <img src="https://static0.twilio.com/marketing/bundles/marketing/img/logos/wordmark-red.svg" alt="Twilio" width="250" />
</a>
# Two-Factor Authentication with Laravel and Authy

In this example application, you will learn how to create a login system
 for Laravel applications secured with 2FA using Authy.

[Learn more about this code in our interactive code walkthrough](https://www.twilio.com/docs/howto/walkthrough/two-factor-authentication/php/laravel).

## Deploy On Heroku

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy?template=https://github.com/TwilioDevEd/authy2fa-laravel)

## Local Development

1. Clone the repository and `cd` into it.

    ```bash
    $ git clone git@github.com:TwilioDevEd/authy2fa-laravel.git
    ```
    
1. The application uses PostgreSQL as persistence layer. If you
  don't have it already, you should install it. The easiest way is by
  using [Postgres.app](http://postgresapp.com/).

1. Create a database.

    ```bash
     $ createdb authy_laravel
    ```

1. Copy the sample configuration file and edit it to match your 
   configuration.

    ```bash
     $ cp .env.example .env 
    ```

1. Install the dependencies with [Composer](https://getcomposer.org/).

    ```bash
    $ composer install
    ```

1. Generate an `APP_KEY`.

    ```bash
    $ php artisan key:generate
    ```
   
1. Start the server.

    ```bash
    $ php artisan serve
    ```   

1. Check it out at [http://localhost:8000](http://localhost:8000).

## Meta

* No warranty expressed or implied. Software is as is. Diggity.
* [MIT License](http://www.opensource.org/licenses/mit-license.html)
* Lovingly crafted by Twilio Developer Education.
