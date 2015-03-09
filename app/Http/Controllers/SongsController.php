<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Users;

class SongsController extends Controller {

	//
	public function index()
	{
		//$songs = ['Boyfriend','Be Alright', 'Fall'];

		$Orgs = Users::where('LastName','Muller')->first();

		return view('songs.index', compact('Orgs'));
	}

	public function show($id)
	{
		$song = $this->getSongs()[$id];
		return view('songs.show',compact('song'));
	}

	private function getSongs()

	{

		$songs = ['Boyfriend','Be Alright', 'Fall'];
		return $songs;	

	}

}
