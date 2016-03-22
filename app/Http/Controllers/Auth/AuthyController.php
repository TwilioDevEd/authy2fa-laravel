<?php namespace App\Http\Controllers\Auth;

use Auth;
use Session;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests;

class AuthyController extends Controller {


	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest');
	}

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
