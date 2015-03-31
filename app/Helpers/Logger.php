<?php namespace App\Helpers;
/**
 * 
 * User: EM
 * Date: 3/31/15
 * Time: 11:16
 */

use App\UserLogs;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Cookie;

class Logger extends BasicObject {

    protected $info = null;
    protected $EventID = null;
    protected $UserID = null;

    public function __construct($info,$EventID,$UserID)
    {
        $this->info = $info;
        $this->EventID = $EventID;
        $this->UserID = $UserID;
    }

    public function SaveLog()
    {
        $log = new UserLogs;
        $log->info    = $this->info;
        $log->EventID = $this->EventID;
        $log->UserID  = $this->UserID;
        $log->save();
    }

}