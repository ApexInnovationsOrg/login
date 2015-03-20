@extends('master')

@section('content')
	<h1>Users</h1>

	<ul>


		<li>{{ $Orgs->FirstName . ' ' . $Orgs->LastName }}</li>


	</ul>

@stop