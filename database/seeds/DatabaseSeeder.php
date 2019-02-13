<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();

		$this->call('UserTableSeeder');
	}
}

class UserTableSeeder extends Seeder {
  public function run()
  {
    DB::table('users')->delete();
    // make sure to update this code with your actual e-mail and phone number
    User::create(array('name'=>'Ricky','email' => 'ricky@twilio.com','phone_number' => '7187533087', 'password' => bcrypt('testtest'), 'country_code' => '+1'));
  }
}
