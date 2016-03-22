<?php namespace App\Http\Middleware;

use Closure;

class ValidateAuthyRequest {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */

	protected function check_bool($value) {
		if(is_bool($value)) {
			$value = ($value) ? 'true' : 'false';
		} else {
			$value = (is_null($value)) ? '' : $value;
		}
		return $value;
	}

	protected function sort_params($params) {
		$new_params = array();
		foreach ($params as $k => $v) {
			if (is_array($v)) {
				ksort($v);
				$new_params[$k] = $v;
				foreach ($v as $k2 => $v2) {
					if (is_array($v2)) {
						ksort($v2);
						$new_params[$k][$k2] = $v2;
						foreach ($v2 as $k3 => $v3) {
							$v3 = $this->check_bool($v3);
							$new_params[$k][$k2][$k3] = $v3;
						}
					} else {
						$v2 = $this->check_bool($v2);
						$new_params[$k][$k2] = $v2;
					}
					
				}
			} else {
				$v = $this->check_bool($v);
				$new_params[$k] = $v;
			}
		}
		ksort($new_params);
		return $new_params;
	}

	public function handle($request, Closure $next)
	{
		$key = env('AUTHY_API_KEY');
		$url = $request->url();
		$params = $request->all();
		$nonce = $request->header("X-Authy-Signature-Nonce");
		$theirs = $request->header('X-Authy-Signature');

		$sorted_params = $this->sort_params($params);
		$query = http_build_query($sorted_params);
		$message = $nonce . '|' . $request->method() . '|' . $url . '|' . $query;

		$s = hash_hmac('sha256', $message, $key, true);
		$mine = base64_encode($s);

		if ($theirs != $mine) {
			return "Not a valid Authy request.";
		} else {
			return $next($request);
		}
	}

}
