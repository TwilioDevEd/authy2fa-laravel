<?php namespace App\Http\Controllers\Auth;

use Auth;
use Session;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller {

  /*
  |--------------------------------------------------------------------------
  | Registration & Login Controller
  |--------------------------------------------------------------------------
  |
  | This controller handles the registration of new users, as well as the
  | authentication of existing users. By default, this controller uses
  | a simple trait to add these behaviors. Why don't you explore it?
  |
  */

  use AuthenticatesAndRegistersUsers;

  /**
   * Create a new authentication controller instance.
   *
   * @param  \Illuminate\Contracts\Auth\Guard  $auth
   * @param  \Illuminate\Contracts\Auth\Registrar  $registrar
   * @return void
   */
  public function __construct(Guard $auth, Registrar $registrar)
  {
    $this->auth = $auth;
    $this->registrar = $registrar;

    $this->middleware('guest', ['except' => 'getLogout']);
  }

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

  public function getTwofactor() {
    return view('auth/twofactor');
  }

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

}
