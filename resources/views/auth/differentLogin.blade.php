@extends('app')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Register</div>
				<div class="panel-body">
					@if (count($errors) > 0)
						<div class="alert alert-danger">
							<strong>Whoops!</strong> There were some problems with your input.<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif

						<form class="form-horizontal" role="form" method="POST" action="/auth/login">
							<input type="hidden" name="_token" value="{{ Session::token() }}">
							<div class="col-md-6 col-md-offset-4">
								<h3>Know your password?</h3>
							</div>
							<input type="hidden" name="_token" value="{{ csrf_token() }}">
							<input type="hidden" name="emailName" value="{{ isset($emailName) ? $emailName : old('emailName') }}">
							<input type="hidden" name="providerName" value="{{ isset($providerName) ? $providerName : old('providerName') }}">
							<div class="form-group">

								<div class="form-group">
									<label class="col-md-4 control-label">E-Mail Address</label>
									<div class="col-md-6">
										<input type="email" class="form-control" name="EmailLogin" value="{{ old('EmailLogin') }}">
									</div>
								</div>

								<div class="form-group">
									<label class="col-md-4 control-label">Password</label>
									<div class="col-md-6">
										<input type="password" class="form-control" name="Password">
									</div>
								</div>

							</div>
							<div class="form-group">
								<div class="col-md-6 col-md-offset-4">
									<button type="submit" class="btn btn-success ">
										<span class="glyphicon glyphicon-thumbs-up"></span> Link Accounts
									</button>
								</div>
							</div>
						</form>
					<hr />
					<div class="col-md-6 col-md-offset-4">
								<h3>Or authorize by email</h3>
					</div>
					<form class="form-horizontal" role="form" method="POST" action="/auth/Social/verifyEmail">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<input type="hidden" name="email" value="{{ $email }}">
						<input type="hidden" name="providerName" value="{{ $provider }}">
						<div class="form-group">
							<label class="col-md-4 control-label">E-mail Address</label>
							<div class="col-md-6">
								<input type="email" class="form-control" name="Login" value="{{ old('Login') }}">
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary ">
									<span class="glyphicon glyphicon-envelope"></span> Send Authorization Email
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
