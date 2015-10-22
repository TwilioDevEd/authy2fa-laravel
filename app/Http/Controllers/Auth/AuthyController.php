<?php namespace App\Http\Controllers\Auth;

use Session;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;

class AuthyController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest');
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */

	// Check status of user 
	public function status(Request $request) {
    $user = User::find(Session::get('id'));
    $status = $user->authy_status;
    if($status == 'approved') {
      Auth::login($user);
    }
    return response()->json(['status' => $status, 'response' => Session::get('request')]);
  }

  // Public webhook for Authy
	public function callback(Request $request) {
		$authy_id = $request->input('authy_id');
		$user = User::where('authy_id', '=', $authy_id)->firstOrFail();
		if(isset($user)) {
		  $user->authy_status = $request->input('status');
		  $user->save();
		  return "ok";
		} else {
		  return "invalid";
		}  
  }

}
