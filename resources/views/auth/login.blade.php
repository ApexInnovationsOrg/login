@extends('app')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Login</div>
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

						<div class="form-group" style="display:none">
							<div class="col-md-6 col-md-offset-4">
								<div class="checkbox">
									<label>
										<input type="checkbox" name="remember"> Remember Me
									</label>
								</div>
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<p>
									<a href="/password/email">Forgot Your Password?</a> &nbsp;|&nbsp;
									<a href="https://www.apexinnovations.com/CreateAccount.php">Create Account</a>
								</p>
								<button type="submit" class="btn btn-primary" style="margin-right: 15px;">
									Login
								</button>
							</div>
						</div>
					</form>
					<hr/>
					<div id="janrainEngageEmbed" class="col-md-6 col-centered">
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script async="async" src="/assets/javascript/frontend.js"></script>
@endsection
