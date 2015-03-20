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
						<div>
							<div class="col-md-6 col-md-offset-2">
								<h4>Email sent to: {{ $verifiedEmail }}</h4>
							</div>

							<form class="form-horizontal" role="form" method="POST" action="/auth/Social/email">
								<input type="hidden" name="_token" value="{{ csrf_token() }}">
								<input type="hidden" name="user" value="{{ $user }}">
								<input type="hidden" name="providerName" value="{{ $providerName }}">
								<input type="hidden" name="email" value="{{ $verifiedEmail }}">
									<button type="submit" class="btn btn-primary btn-lg top-buffer col-lg-6 col-lg-offset-3">
									  <span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> Resend email
									</button>
							</form>
						</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
