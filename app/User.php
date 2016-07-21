<?php namespace App;

use Authy\AuthyApi as AuthyApi;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{

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
    protected $hidden = ['password', 'remember_token', 'authy_status', 'authy_id'];

    /**
     * Authy Api Key
     * @var String
     */
    private $_authy_api_key;

    /**
     * Authy Api Client
     * @var String
     */
    private $_authy_api;

    protected function getAuthyApiKey()
    {
        if (empty($this->_authy_api_key)) {
            $this->_authy_api_key = getenv('AUTHY_API_KEY') or die(
            "You must specify your api key. Visit https://dashboard.authy.com/"
            );
        }
        return $this->_authy_api_key;
    }

    protected function getAuthyApi()
    {
        if (empty($this->_authy_api)) {
            $this->_authy_api = new AuthyApi($this->getAuthyApiKey());
        }
        return $this->_authy_api;
    }


    public function register_authy()
    {
        $user = $this->getAuthyApi()->registerUser(
            $this->email,
            $this->phone_number,
            $this->country_code
        ); //email, cellphone, country_code

        if ($user->ok()) {
            $this->authy_id = $user->id();
            $this->save();
            return true;
        } else {
            // something went wrong
            return false;
        }
    }

    public function sendOneTouch($message)
    {
        // reset oneTouch status
        if ($this->authy_status != 'unverified') {
            $this->authy_status = 'unverified';
            $this->save();
        }

        $params = array(
            'api_key' => $this->getAuthyApiKey(),
            'message' => $message,
            'details[Email]' => $this->email,
        );

        $defaults = array(
            CURLOPT_URL => "https://api.authy.com" .
                "/onetouch/json/users/{$this->authy_id}/approval_requests",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $params,
        );

        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $output = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($output);

        return $json;
    }

    public function sendToken()
    {
        $sms = $this->getAuthyApi()->requestSms($this->authy_id);

        return $sms->ok();
    }

    public function verifyToken($token)
    {
        $verification = $this->getAuthyApi()->verifyToken($this->authy_id, $token);

        if ($verification->ok()) {
            return true;
        } else {
            return false;
        }
    }
}
