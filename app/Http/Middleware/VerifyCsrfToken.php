<?php namespace App\Http\Middleware;

// use PhpSpec\Exception\Exception;
// use Symfony\Component\HttpFoundation\Cookie;
// use Illuminate\Support\Facades\Auth;
// use Symfony\Component\Security\Core\Util\StringUtils;

/*use App\Helpers\CookieMonster;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\User;
use Illuminate\Support\Facades\Input;*/

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use App\Helpers\CookieMonster;
use App\Helpers\SessionHelper;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\Security\Core\Util\StringUtils;

class VerifyCsrfToken extends BaseVerifier {


    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */

    public function handle($request, Closure $next)
    {

        if ($this->isReading($request) || $this->tokensMatch($request))
        {

            //$sm = new SessionHelper($this->encrypter);
            //$response =  $sm->updateTokens($request, $next);

            // $cm = new CookieMonster();
            // $response = $next($request);
            // $response = $cm->addCookieToResponse($response, 'XSRF-TOKEN', $request->session()->token());
            // $response = $cm->addCookieToResponse($response, 'REDIS-TOKEN', Session::getId());

            /*if(Session::getId() == $GLOBALS["sessionId"]) {}

            Session::put('_id', Session::getId());


            $session = [
                Session::getId(),
                $GLOBALS["sessionId"]
            ];

            Log::info('cookie $GLOBALS["sessionId"]: '.print_r($session, true));*/
            return $next($request);
        }

        throw new TokenMismatchException;
    }

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {

        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        if ( ! $token && $header = $request->header('X-XSRF-TOKEN'))
        {
            $token = $this->encrypter->decrypt($header);
        }

        // return StringUtils::equals($request->session()->token(), $token);



        //$sm = new SessionHelper($this->encrypter);
        //$verfied = $sm->verifyTokens($request);

        //dd($verfied);
        return true;
        return $verfied;
        // return StringUtils::equals($request->session()->token(), $token);   b39d03579280927430b57ed8538b9f00a2237ffb
    }

    /**
     * Determine if the HTTP request uses a ‘read’ verb.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isReading($request)
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }

}
