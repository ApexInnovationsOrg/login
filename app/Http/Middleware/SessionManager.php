<?php namespace App\Http\Middleware;

use Closure;
use App\User;
//use App\Helpers\CookieMonster;
use App\Helpers\SessionHelper;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Input;
class SessionManager {

    protected $encrypter = null;
    //protected $cm = null;

    public function __construct(Encrypter $encrypter) {
        $this->encrypter = $encrypter;
        //$this->cm = new CookieMonster('', $this->encrypter);
    }

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{


        $sm = new SessionHelper($this->encrypter);
        $response = $sm->updateTokens($request, $next);
        // dd($response);
        return $response;
	}

}
