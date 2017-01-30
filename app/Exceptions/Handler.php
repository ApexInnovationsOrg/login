<?php namespace App\Exceptions;

use Exception;
use Mail;
use Session;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = [
		'Symfony\Component\HttpKernel\Exception\HttpException'
	];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		$caller = $_SERVER['SCRIPT_NAME'];
		$backtrace = '';
		foreach(debug_backtrace() as $key => $val){
			if($val['function'] == 'include' || $val['function'] == 'include_once' || $val['function'] == 'require_once' || $val['function'] == 'require'){
				$backtrace .= '#' . $key . ' ' . $val['function'] . '(' . $val['args'][0] . ') called at [' . $val['file'] . ':' . $val['line'] . ']<br />';
			}else{
				$backtrace .= '#' . $key . ' ' . $val['function'] . '() called at [' . $val['file'] . ':' . $val['line'] . ']<br />';
			}
		}
		
		$get = $post = $session = '';
		foreach ($_GET as $key => $value) $get .= output_keyvalues($key, $value);
		foreach ($_POST as $key => $value) $post .= output_keyvalues($key, $value);
		foreach (Session::all() as $key => $value){

				$session .= $key . ': ' . print_r($value,1) . ',' . PHP_EOL;

		}

		if ($get != '') $get = substr($get, 0, -5);
		if ($post != '') $post = substr($post, 0, -5);

		    if ($e instanceof \Exception) {
				$error = $e->getMessage() . ' (#' . $e->getCode() . ') ' . 
						' [' . $e->getFile() . ':' . $e->getLine() . ']';
		        // emails.exception is the template of your email
		        // it will have access to the $error that we are passing below
		        Mail::send('emails.exception', ['caller'=>$_SERVER['SCRIPT_NAME'],'backtrace'=>$backtrace,'post' => $post, 'get' => $get,'session'=>$session, 'error' => $error ], function ($m) {
		            $m->to('developers@apexinnovations.com', 'Apex Developers')->subject('Login system error');
		        });
		    }

		    return parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
		return parent::render($request, $e);
	}

}
