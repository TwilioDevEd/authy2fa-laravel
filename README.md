# Building 2FA in Laravel 5 with Authy

## Laying our Foundation
Create a new app:

```
laravel new aquarius
```

We'll be using MySQL to store information about our user. So make sure you create a MySQL database and ensure database configuration in `config/database.php` is correct. Then update the database migration file (`database/migrations/2014_10_12_000000_create_users_table.php`) to add `phone_number` and `authy_id`:

```php
      $table->string('country_code');
      $table->string('phone_number');
      $table->string('authy_id')->nullable();
```

We're going to seed our database with a single user so we can test out our 2FA setup. To do so, first uncomment the line in the seed file (`database/seeds/DatabaseSeeder.php`) that says: 

```php
// $this->call('UserTableSeeder');
```

Then copy this code to the bottom of that file and make sure you update it with your email and phone number. After we create the user, we want to register that user with Authy using the `register_authy` method which we'll write shortly. 

```php
class UserTableSeeder extends Seeder {
  public function run()
  {
    DB::table('users')->delete();
    // make sure to update this code with your actual e-mail and phone number
    $user = User::create(array('name' => 'bob', 'email' => 'hi@example.com','phone_number' => '5555555555', 'country_code' => '1', 'password' => bcrypt('test')));
    $user->register_authy();
  }
}
```

In order to use our User model we need to import the User model at the top of the seed file:

```php
use App\User;
```

Now that we have our database information in place, we need to update our User model to reflect this. First, add `phone_number` and `country_code` to $fillable fields in `app/User.php`:

```php
  protected $fillable = ['name', 'email', 'password', 'phone_number', 'country_code'];
```

Then, add `authy_id` to $hidden fields in `app/User.php`:

```php
  protected $hidden = ['password', 'remember_token', 'authy_id'];
```

When we create a user we want to register them with Authy, so we need to make sure we have the Authy-php library installed. Run this at the command line: 

```
composer require authy/php
```

Then import the AuthyAPI library at the top of your User model:

```php
use Authy\AuthyApi as AuthyApi;
```

Now we can add the `register_authy` function inside the User model in `app/User.php`:

```php
  public function register_authy() {
    $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
    $user = $authy_api->registerUser($this->email, $this->phone_number, $this->country_code); //email, cellphone, country_code

    if($user->ok()) {
     $this->authy_id = $user->id();
     $this->save();
    } else {
      // something went wrong 
    }
  }
```

One last piece of setup. We want to set some variables for our environment (like our Authy Token). Create a .env file with this content:

```
APP_ENV=local
APP_DEBUG=true
AUTHY_TOKEN=OURAUTHYTOKEN
```

Now we can run the commands to update and seed our database:

```
php artisan migrate
php artisan db:seed
```

## Getting Authy With It

Laravel 5 provides us with a really great built in Authentication framework. We don't want to reinvent the wheel so we'll be expanding on what Laravel already provides us. Before we get too far ahead of ourselves, let's try how what they've got. Start up your Laravel application:
```
php artisan serve
```

Let's also update our base route in `routes.php` to point at our Home controller, which will provide :
```
Route::get('/', 'HomeController@index');
```

Now go visit [http://localhost:8000/](http://localhost:8000/). You can log in to our basic site by entering the credentials we set when we seeded our database. Looks great, right? It's going to look even better when we hook in 2FA!

The first step is adding a method on our User model that sends our SMS token using Authy. Add the `sendToken` function in `app/User.`.php:

```php
  public function sendToken() {
    $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
    $sms = $authy_api->requestSms($this->authy_id);

    return $sms->ok();
  }
```

Now let's add a method to our AuthController in `app/Http/Controllers/Authcontroller.php` to validate our users credentials and send the token when we post to the login form:
```php
  public function postLogin(Request $request) {
    $credentials = $request->only('email','password');
    if(Auth::validate($credentials)) {
      $user = User::where('email', '=', $request->input('email'))->firstOrFail();
      $user->sendToken();
      Session::set('password_validated', true);
      Session::set('id', $user->id);
      return redirect('/auth/twofactor');
    } else {
        return redirect('/auth/login')->withErrors([
            'email' => 'The email and password combination you entered is incorrect.',
        ]);
    }
  }
```

We need to import the libraries we're using in this method. Add these to the top of `AuthController.php`:
```php
use Auth;
use Session;
use App\User;
use Illuminate\Http\Request;
```

You'll notice we're using a Session to store some information about what part of the 2FA process our user is in. Let's generate a key to make sure our sessions are secure:

```
php artisan key:generate
```

Now we want to create a view that houses the form to verify the token we'll receive from Authy. Create a new file called `resources/views/auth/twofactor.blade.php`: 

```php
@extends('app')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">
      <div class="panel panel-default">
        <div class="panel-heading">Login</div>
        <div class="panel-body">
          @if (count($errors) > 0)
            <div class="alert alert-danger">
              <strong>Whoops!</strong> There were some problems with your input.<br><br>
              <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form class="form-horizontal" role="form" method="POST" action="/auth/twofactor">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="form-group">
              <label class="col-md-4 control-label">Enter Token</label>
              <div class="col-md-6">
                <input id="authy-token" type="string" class="form-control" name="token" value="">
              </div>
            </div>

            <div class="form-group">
              <div class="col-md-6 col-md-offset-4">
                <button type="submit" class="btn btn-primary" style="margin-right: 15px;">
                  Verify
                </button>

              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
```

Authy has a client JavaScript library that enhances some of our elements, we need to make sure we include this in the `<head>` of our HTML by adding it to `resources/views/app.blade.php`:
```
  <!-- Authy js -->
  <link href="https://www.authy.com/form.authy.min.css" media="screen" rel="stylesheet" type="text/css">
  <script src="https://www.authy.com/form.authy.min.js" type="text/javascript"></script>
```

Now we want to add the route in our AuthController (`app/Http/Controllers/Auth/AuthController.php`) that loads this view:
```php
  public function getTwofactor() {
    return view('auth/twofactor');
  }
``` 

Now that we're validating our user and sending our Token using Authy. Add `verifyToken` function in app/User.php:

```php
  public function verifyToken($token) {
    $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
    $verification = $authy_api->verifyToken($this->authy_id, $token);

    if($verification->ok()) {
      return true;
    } else {
      return false;
    }
  }
```

Now we can add the code to `AuthController.php` that handles verifying the token when our form is POSTed:

```php
  public function postTwofactor(Request $request) {
    if(!Session::get('password_validated') || !Session::get('id')) {
      return redirect('/auth/login');
    }

    if(isset($_POST['token'])) {
     $user = User::find(Session::get('id'));
     if($user->verifyToken($request->input('token'))) {
       Auth::login($user);
       return redirect()->intended('/home');
     } else {
        return redirect('/auth/twofactor')->withErrors([
            'token' => 'The token you entered is incorrect',
        ]);
     }
    }
  }
```

We can test with the user we seeded in the database, but it is also super easy to update our registration form to accept a country code and phone number too. Add these fields to the registration blade template(`resources/views/auth/register.blade.php`):
```html
            <div class="form-group">
              <label class="col-md-4 control-label">Country Code</label>
              <div class="col-md-6">
                <select data-show-as="number" id="authy-countries" name="country_code"></select>
              </div>
            </div>

            <div class="form-group">
              <label class="col-md-4 control-label">Phone Number</label>
              <div class="col-md-6">
                <input type="phone_number" class="form-control" name="phone_number"  id="authy-cellphone" >
              </div>
            </div>
```

We're taking advantage of the Authy js code we included early to fill our list of country codes. Since we put the authy-countries id on the field, Authy will take care of filling that with everything we need.

And then update our Registration Service (`app/Services/Registrar.php`) to validate and store our phone number information. Add these two lines inside the `validator()` function: 

```php
      'country_code' => 'required',
      'phone_number' => 'required|min:7|unique:users',
```

And add these two lines to the `create()` function: 
```php
      'country_code' => $data['country_code'],
      'phone_number' => $data['phone_number']
```

Now we just need to add the method to our AuthController that validates the registration, creates the users and sends their token:
```php
  public function postRegister(Request $request)
  {
    $validator = $this->registrar->validator($request->all());
    if ($validator->fails())
    {
      $this->throwValidationException(
        $request, $validator
      );
    }
    $user = $this->registrar->create($request->all());
    $user->register_authy();
    $user->sendToken();
    Session::set('password_validated', true);
    Session::set('id', $user->id);
    return redirect('/auth/twofactor');
  }
```

Visit [localhost:8000/auth/register](http://localhost:8000/auth/register) and register as a new user to give it a try!

