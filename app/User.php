<?php namespace App;

use Authy\AuthyApi as AuthyApi;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'email', 'password', 'phone_number', 'country_code'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token', 'authy_id'];

  public function register_authy() {
    $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
    $user = $authy_api->registerUser($this->email, $this->phone_number, $this->country_code); //email, cellphone, country_code

    if($user->ok()) {
     $this->authy_id = $user->id(); 
     $this->save();
     return true;
    } else {
      // something went wrong 
      return false;
    }
  }

  public function sendToken() {
    $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
    $sms = $authy_api->requestSms($this->authy_id);

    return $sms->ok();
  }

  public function verifyToken($token) {
    $authy_api = new AuthyApi(getenv('AUTHY_TOKEN'));
    $verification = $authy_api->verifyToken($this->authy_id, $token);

    if($verification->ok()) {
      return true;
    } else {
      return false;
    }
  }
}
