<?php

namespace App\Authy;


interface Service
{

    /**
     * Register a new user in authy
     *
     * @param $email
     * @param $phone_number
     * @param $country_code
     * @return string User Authy id
     */
    public function register($email, $phone_number, $country_code);

    /**
     * Verify if the user is registered in Authy (smartphone app installed)
     *
     * @param $authy_id
     * @return mixed
     */
    public function verifyUserStatus($authy_id);

    /**
     * Request a one touch verification
     *
     * @param $authy_id
     * @param $message
     * @return string uuid
     */
    public function sendOneTouch($authy_id, $message);

    /**
     * Check one touch verification status
     *
     * @param $uuid
     * @return string status
     */
    public function verifyOneTouch($uuid);

    /**
     * Send a verification token to user phone
     *
     * @param $authy_id
     * @return bool `true` if token successful sent or ignored
     */
    public function sendToken($authy_id);

    /**
     * Request token verification
     *
     * @param $authy_id
     * @param $token
     * @return bool `true` if token is valid
     */
    public function verifyToken($authy_id, $token);
}
