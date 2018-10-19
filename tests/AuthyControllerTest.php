<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use \App\OneTouch;
use \App\User;

class AuthyControllerTest extends TestCase
{

    use WithoutMiddleware;

    /**
     * Test Authy callback url correctly sets one touch status
     *
     * @return void
     */
    public function testCallbackAction()
    {
        $uuid = 'f5c96706-fcb3-4186-b13e-5a2b0943f780';

        OneTouch::create(['uuid' => $uuid]);

        $this->json('POST',
            "/authy/callback",
                [
                    "callback_action" => "approval_request_status",
                    "uuid" => "$uuid",
                    "status" => "approved",
                    "approval_request" => []])
            ->see('ok');

        $one_touch = OneTouch::where('uuid', '=', $uuid)->first();

        $this->assertEquals('approved', $one_touch->status);

        $one_touch->delete();

    }


    public function testStatusAction()
    {
        $uuid = 'f5c96706-fcb3-4186-b13e-5a2b0943f780';

        $one_touch = OneTouch::create(['uuid' => $uuid]);
        $user = User::create(['name' => 'joe', 'password' => 'pass', 'email' => 'joe@example.com',
                      'phone_number' => '123456789', 'country_code' => '+1']);

        $this->withSession(['one_touch_uuid' => $uuid, 'id' => $user->id])->get("authy/status")
            ->seeJson(['status' => 'pending']);

        $one_touch->delete();
        $user->delete();
    }
}
