<?php namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

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
     * @param $authy_id string
     */
    public function updateAuthyId($authy_id) {
        if($this->authy_id != $authy_id) {
            $this->authy_id = $authy_id;
            $this->save();
        }
    }

    /**
     * @param $status string
     */
    public function updateVerificationStatus($status) {
        // reset oneTouch status
        if ($this->authy_status != $status) {
            $this->authy_status = $status;
            $this->save();
        }
    }

    public function updateOneTouchUuid($uuid) {
        if ($this->authy_one_touch_uuid != $uuid) {
            $this->authy_one_touch_uuid = $uuid;
            $this->save();
        }
    }
}
