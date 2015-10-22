<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier {

	protected $except_urls = [
    'authy/*',
  ];
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
    $regex = '#' . implode('|', $this->except_urls) . '#';

    if ($this->isReading($request) || $this->tokensMatch($request) || preg_match($regex, $request->path()))
    {
      return $this->addCookieToResponse($request, $next($request));
    }

    throw new TokenMismatchException;
  }

}
