<?php

namespace App\Services;


use App\Authy\Service;
use Illuminate\Support\Facades\Log;

class Authy implements Service
{

    /**
     * @var \Authy\AuthyApi
     */
    private $api;

    public function __construct()
    {
        $this->api = new \Authy\AuthyApi(config('app.authy_api_key'));
    }

    /**
     * @param $message
     * @param $errors
     * @param bool $throw
     * @throws \Exception
     */
    private static function failed($message, $errors, $throw = true)
    {
        foreach($errors as $field => $message) {
            Log::error("Authy Error: {$field} = {$message}\n");
        }
        if($throw) {
            throw new \Exception($message);
        }
        Log::error($message);
    }

    /**
     * @param $email
     * @param $phone_number
     * @param $country_code
     * @return int
     * @throws \Exception
     */
    function register($email, $phone_number, $country_code)
    {
        $user = $this->api->registerUser($email, $phone_number, $country_code);

        if ($user->ok()) {
            return $user->id();
        }

        self::failed("Could not register user in Authy", $user->errors());
    }

    /**
     * @param $authy_id
     * @param $message
     * @return string
     * @throws \Exception
     */
    public function sendOneTouch($authy_id, $message)
    {
        $response = $this->api->createApprovalRequest($authy_id, $message);

        if($response->ok()) {
            return $response->bodyvar('approval_request')->uuid;
        }
        self::failed("Could not request One Touch", $response->errors());
    }

    /**
     * @param $uuid
     * @return bool Verification status
     * @throws \Exception
     */
    public function verifyOneTouch($uuid)
    {
        $response = $this->api->getApprovalRequest($uuid);

        if($response->ok()) {
            return (bool) $response->bodyvar('status');
        }
        self::failed("OneTouch.php verification failed", $response->errors());
    }

    /**
     * @param $authy_id
     * @return bool
     * @throws \Exception
     */
    public function sendToken($authy_id)
    {
        $response = $this->api->requestSms($authy_id);
        if($response->ok()) {
            return (bool) $response->bodyvar('success');
        }
        self::failed("OneTouch.php verification failed", $response->errors());
    }

    /**
     * @param $authy_id
     * @param $token
     * @return bool
     * @throws \Exception Nothing will be thrown here
     */
    public function verifyToken($authy_id, $token)
    {
        $response = $this->api->verifyToken($authy_id, $token);

        if($response->ok()) {
            return $response->ok();
        }
        self::failed("Token verification failed", $response->errors(), false);
    }

    /**
     * @param $authy_id
     * @return \Authy\value status
     * @throws \Exception if request to api fails
     */
    public function verifyUserStatus($authy_id) {
        $response = $this->api->userStatus($authy_id);
        if($response->ok()) {
            return $response->bodyvar('status');
        }
        self::failed("Status verification failed!", $response->errors());
    }
}
