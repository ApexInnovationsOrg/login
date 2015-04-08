<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use SSH;
use Crypt;
use Input;
use Illuminate\Http\Request;

class ServerController extends Controller {

    public function deploy() {
    	$decrypted = '';
    	if(Input::get('data'))
    	{
    		$decrypted = Crypt::decrypt(Input::get('data'));
    	}
    	if($decrypted == '$p00ker##n')
    	{
	    	SSH::into('staging')->run(array(
			    'cd /websites/login.apexwebtest.com',
			    'git pull origin master',
			    'composer update',
			    'composer dumpautoload',
			    'php artisan cache:clear',
			    'php artisan route:clear'
	    	), function($line)
	    	{
	    		echo $line.PHP_EOL;
	    	});
	    }
	    else
	    {
	    	echo 'Deployed.';
	    }
    }
}
