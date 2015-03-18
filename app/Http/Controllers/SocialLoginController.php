<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class SocialLoginController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('home');
		//return 'home';
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show()
	{
		// if(isset($_POST['token'])){
		// 	$token = $_POST['token'];
		// 	$janrainApiKey = "ac6cd0fbe0e3710586b35343813023bf1ba570b6";
		// 	$engageUrl = 'https://rpxnow.com/api/v2/auth_info';
		// 	$curl = curl_init();
		// 	curl_setopt($curl, CURLOPT_URL, $engageUrl);
		// 	curl_setopt($curl, CURLOPT_POST, true);
		// 	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		// 	curl_setopt($curl, CURLOPT_POSTFIELDS, array('token' => $token, 'apiKey' => $janrainApiKey));
		// 	$authInfo = curl_exec($curl);
		// 	curl_close($curl);
		// };

		dd(Request::all());
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


}
