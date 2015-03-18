<?php namespace App\Helpers;
/**
 * Created by PhpStorm.
 * User: molinski
 * Date: 12/03/15
 * Time: 10:16
 */


use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Cookie;

class CookieMonster extends BasicObject {

    protected $_infoArray = [];
    protected $encrypter = null;
    protected $domain = '/';

    public function __construct($headerCookieStr = null, Encrypter $encrypter = null)
    {
        //dd($encrypter);
        // Log::info('$headerCookieStr: '.$headerCookieStr);
        $this->encrypter = $encrypter;
        $this->parseCookieStr($headerCookieStr);
    }

    public function parseCookieStr($cookieStr)
    {
        // Log::info('$cookieStr: '.$cookieStr);
        if($cookieStr) {
            // Clean up the string
            $cookieStrArray = explode(';', $cookieStr);
            foreach ($cookieStrArray as $k => $subStr) {
                $keyValuePair = explode('=', trim($subStr));
                if($this->encrypter) {
                    $this->__set($keyValuePair[0], $this->encrypter->decrypt(rawurldecode($keyValuePair[1])));
                }
            }
        }
        return $this;
    }

    static public function addCookieToResponse($response, $name, $value)
    {
        $domain = Config::get('session.domain');
        $path = Config::get('session.path');
        Log::info(Config::get('session.domain'));
        $time = time() + 60 * Config::get('session.lifetime');
        $response->headers->setCookie(
            new Cookie($name, $value, $time, $path, $domain, null, false, false)
        );
        return $response;
    }

    static public function removeCookieFromResponse($response, $name) {
        $domain = Config::get('session.domain');
        $path = Config::get('session.path');
        $response->headers->setCookie(
            new Cookie($name, null, time()-3600, $path, $domain, null, false, false)
        );
        return $response;
    }
}