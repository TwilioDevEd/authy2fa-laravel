<?php namespace App\Http\Controllers\Auth;

use App\Authy\Service;
use App\OneTouch;
use Auth;
use Session;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use function Stringy\create;


class AuthController extends Controller
{

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
     * @param  \Illuminate\Contracts\Auth\Guard $auth
     * @param  \Illuminate\Contracts\Auth\Registrar $registrar
     * @param  \App\Authy\Service $authy
     */
    public function __construct(Guard $auth, Registrar $registrar, Service $authy)
    {
        $this->auth = $auth;
        $this->registrar = $registrar;
        $this->authy = $authy;

        $this->middleware('guest', ['except' => 'getLogout']);
    }

    public function postLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::validate($credentials)) {
            $user = User::where('email', '=', $request->input('email'))->firstOrFail();

            Session::set('password_validated', true);
            Session::set('id', $user->id);

            if ($this->authy->verifyUserStatus($user->authy_id)->registered) {
                $uuid = $this->authy->sendOneTouch($user->authy_id, 'Request to Login to Twilio demo app');

                OneTouch::create(['uuid' => $uuid]);

                Session::set('one_touch_uuid', $uuid);

                return response()->json(['status' => 'ok']);
            } else
                return response()->json(['status' => 'verify']);

        } else {
            return response()->json(['status' => 'failed',
                'message' => 'The email and password combination you entered is incorrect.']);
        }
    }

    public function getTwoFactor()
    {
        $message = Session::get('message');

        return view('auth/two-factor', ['message' => $message]);
    }

    public function postTwoFactor(Request $request)
    {
        if (!Session::get('password_validated') || !Session::get('id')) {
            return redirect('/auth/login');
        }

        if (isset($_POST['token'])) {
            $user = User::find(Session::get('id'));
            if ($this->authy->verifyToken($user->authy_id, $request->input('token'))) {
                Auth::login($user);
                return redirect()->intended('/home');
            } else {
                return redirect('/auth/two-factor')->withErrors([
                    'token' => 'The token you entered is incorrect',
                ]);
            }
        }
    }

    public function postRegister(Request $request)
    {
        $validator = $this->registrar->validator($request->all());
        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }
        $user = $this->registrar->create($request->all());

        Session::set('password_validated', true);
        Session::set('id', $user->id);

        $authy_id = $this->authy->register($user->email, $user->phone_number, $user->country_code);

        $user->updateAuthyId($authy_id);

        if ($this->authy->verifyUserStatus($authy_id)->registered)
            $message = "Open Authy app in your phone to see the verification code";
        else {
            $this->authy->sendToken($authy_id);
            $message = "You will receive an SMS with the verification code";
        }

        return redirect('/auth/two-factor')->with('message', $message);
    }
}
