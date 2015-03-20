<?php namespace App\Helpers;
/**
 * Created by PhpStorm.
 * User: molinski
 * Date: 12/03/15
 * Time: 10:16
 */

use Closure;
use App\User;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Input;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SessionHelper extends BasicObject {

    protected $encrypter = null;
    protected $cm = null;

    public function __construct(Encrypter $encrypter) {
        $this->encrypter = $encrypter;
        $this->cm = new CookieMonster('', $this->encrypter);
    }

    public function verifyTokens($request) {
        // pass the dummy Closure
        $this->updateTokens($request, function($n){return $n;});
        return true;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function updateTokens($request, Closure $next)
    {
        Log::info('SessionHelper::updateTokens');
        $response = $next($request);
        $response = CookieMonster::addCookieToResponse($response, 'xsrf-token', $request->session()->token());

        $Redis = Redis::connection();
        $cm = $this->cm->parseCookieStr($request->header('COOKIE'));

        $previousSession = null;
        $userId = 0;
        $activeSessionToken = '';
        $previousSessionRedisId = $cm->get(Config::get('session.cookie'));
        if($previousSessionRedisId) {
            $previousSession = unserialize(unserialize($Redis->get('laravel:' . $previousSessionRedisId)));
        }
        Log::info('$previousSession :'.print_r(['session.cookie'=>Config::get('session.cookie'), '$previousSessionRedisId'=>$previousSessionRedisId, '$previousSession'=>$previousSession], true));

        /*
         * NOTE: We have still not logged in the user
         * Now we need to check if a previous session for the user exists using the passed token.  If it exists, check
         * if it correctly matches the active user session.  Restore the session.
         *
         * If it doesn't exist we add one.
         *
         */

        // If the previous session exist we check if it is also the active session for the user
        if ($previousSession) {
            Log::info('Has $previousSession');
            // Set next session id
            $response = CookieMonster::addCookieToResponse($response, Config::get('session.cookie'), Session::getId());
            // $GLOBALS["sessionId"] = Session::getId();
            // Do we have an active current active session?

            $login = Input::get('Login');
            // Are we logging in for the first time
            if(!empty($login)) {
                Log::info('Has Login: '.print_r([$login], true));
                $user = User::where('Login', '=', $login)->first();
                Log::info('$user: '.print_r([$user], true));
                Session::put('userId', $user->ID);
                // bad naming convention that continues to get carried over.
                Session::put('userID', $user->ID);
                Session::put('userName', $user->FirstName.' '.$user->LastName);

                // If we are logging in for the first time we stop here
                return $response;
            } else {
                // Log::info('Has User Token');
                if(!isset($previousSession['userId'])) {
                    // Log::info('No UserId in $previousSession');
                    // Session::put('userId', $user->ID);
                    // Session::put('userID', $user->ID); // bad naming convention that continues to get carried over.
                } else {
                    $userId = $cm->get('user-token');
                    $user = User::where('ID', '=', $userId)->first();
                    // Log::info('$userId: '.print_r($userId, true));
                    // Log::info('User: '.print_r($user, true));
                    if(!empty($user)&&($user->ID == $previousSession['userId'])) {
                        Log::info('UserId Match');
                        Session::put('userId', $user->ID);
                        // bad naming convention that continues to get carried over.
                        Session::put('userID', $user->ID);
                        Session::put('userName', $user->FirstName.' '.$user->LastName);

                    } else if(!isset($previousSession['userId'])) {
                        // If it is not set this is the first time to check and should be added for future cases
                        Log::info('$user->ID Doesn\'t match $previousSession $user->ID: '.print_r(['$user->ID'=>$user->ID, '$previousSession'=>$previousSession], true));
                        //throw new TokenMismatchException;
                    }
                }
            }

            $activeSessionToken = $Redis->get('User:' . $userId);

            Log::info('$activeSessionToken: '.print_r(['$activeSessionToken'=>$activeSessionToken],true));

            if($activeSessionToken) {
                // Do they match
                Log::info('$activeSessionToken Exist: '.print_r([$activeSessionToken, $previousSession], true));
                if((isset($previousSession['_id']))&&($activeSessionToken == $previousSession['_id'])) {
                    Auth::login($user);
                    Log::info('Session Are Equal');
                    // Restore the session
                    $exclude = [];
                    foreach($previousSession as $k => $value) {
                        // lets exclude special cases and private values (anything with an underscore at the beginning)
                        if(!in_array($k, $exclude)&&(stripos($k, '_') != 0)) {
                            Session::put($k, $value);
                        }
                    }
                    // Update the current session id to reflect the captured session

                    Session::put('_id', Session::getId());
                    $Redis->set('User:' . $user->ID, Session::getId());
                    Log::info(Session::getId());
                    $response = CookieMonster::addCookieToResponse($response, 'user-token', $user->ID);
                    $response = CookieMonster::addCookieToResponse($response, Config::get('session.cookie'), Session::getId());


                    $dd = [
                        '$previousSession' => $previousSession,
                        '$userId' => $userId,
                        // '$cm' => $cm,
                        '$cm.token' => $cm->get('xsrf-token'),
                        '$cm.redis|previous redis.laravel:SessionId' => $cm->get(Config::get('session.cookie')),
                        '$cm.user' => $cm->get('user-token'),
                        '$activeSessionToken|previous SessionId' => $activeSessionToken,
                        '$previousSession._token|matches $cm.token?' => $previousSession['_token'] == $cm->get('xsrf-token'),
                        'sessionId|next' => Session::getId(),
                        // 'server|next' => $request->session()->token(),
                    ];

                    // Auth::login($user);
                    Log::info('VERIFIED!!!!! FdP!: '.print_r($dd, 1));
                    return $response;
                }
            }

            // DONT DO THIS: its a security risk to create a redis user:id, if they have both user-token and session-token it could produce a auto login
            /*else if($cm->get('user-token')) {
                // Start a new one
                $Redis->set('User:' . $user->ID, Session::getId());
            }*/
        }

        $dd = [
            '$previousSession' => $previousSession,
            '$userId' => $userId,
            // '$cm' => $cm,
            '$cm.token' => $cm->get('xsrf-token'),
            '$cm.redis|previous redis.laravel:SessionId' => $cm->get(Config::get('session.cookie')),
            '$cm.user' => $cm->get('user-token'),
            '$activeSessionToken|previous SessionId' => $activeSessionToken,
            '$previousSession._token|matches $cm.token?' => $previousSession['_token'] == $cm->get('xsrf-token'),
            'sessionId|next' => Session::getId(),
            // 'server|next' => $request->session()->token(),
        ];
        Log::info('results updateTokens: '.print_r($dd, 1));

        return $response;
    }

}