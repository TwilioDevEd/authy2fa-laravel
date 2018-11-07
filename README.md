# Two-Factor Authentication with Laravel and Authy

In this example application, you will learn how to create a login system for Laravel applications secured with 2FA using Authy.

[Learn more about this code in our interactive code walkthrough](https://www.twilio.com/docs/howto/walkthrough/two-factor-authentication/php/laravel).

## Run the Application

1. Clone the repository and `cd` into it.
1. Install the application dependencies with [Composer](https://getcomposer.org/)

   ```bash
   $ composer install
   ```
1. The application uses PostgreSQL as persistence layer. If you
  don't have it already, you should install it. The easiest way is by
  using [Postgres.app](http://postgresapp.com/).

1. Create a database.

   ```bash
   $ createdb authy_laravel
   ```
1. Copy the sample configuration file and edit it to match your configuration.

   ```bash
   $ cp .env.example .env
   ```

   You can find your Authy Api Key for Production at https://dashboard.authy.com/.

1. Generating an `APP_KEY`:

   ```bash
   $ php artisan key:generate
   ```
1. Running the migrations:

   ```bash
   $ php artisan migrate
   ```

1. Expose your application to the wider internet using ngrok. You can look
   [here](#expose-the-application-to-the-wider-internet) for more details. This step
   is important because the application won't work as expected if you run it through the
   localhost.

   ```bash
   $ ngrok http 8000
   ```

   Once ngrok is running, open up your browser and go to your ngrok URL.
   It will look something like this: `http://9a159ccf.ngrok.io`

1. Running the application using Artisan.

   ```bash
   $ php artisan serve
   ```

1. Go to your https://dashboard.authy.com. On the menu to the right you'll find the
   **Settings**. Go to **OneTouch settings** and update the _Endpoint/URL_ with the
   endpoint you created. Something like this:

   `http://[your-ngrok-subdomain].ngrok.io/authy/callback`

   If you deployed this application to production, the the Endpoint/URL should look like this:

   `http://[your-domain].com/authy/callback`

## Run the tests

1. Download phpunit version 4

    ```bash
    $ wget https://phar.phpunit.de/phpunit-4.0.9.phar -O phpunit.phar
    ```

1. Run phpunit

    ```bash
    $ php phpunit.phar
    ```

## Meta

* No warranty expressed or implied. Software is as is. Diggity.
* [MIT License](http://www.opensource.org/licenses/mit-license.html)
* Lovingly crafted by Twilio Developer Education.
