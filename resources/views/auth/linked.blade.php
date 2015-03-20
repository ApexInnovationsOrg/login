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
					<div class="col-lg-8 col-md-offset-2">
					@if($successful)
							<h3>Success!</h3>
							<h4>Your {{ $provider }} account is now linked with your Apex Innovations account.</h4>
							<a href="/auth/login">
								<button type="button" class="btn btn-success btn-lg top-buffer col-lg-6 col-lg-offset-3">
								  <span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Back to login
								</button>
							</a>
					@else
							<h3>Oh no!</h3>
							<h4>Something went wrong linking your accounts.</h4>
							<a href="/auth/login">
								<button type="button" class="btn btn-primary btn-lg top-buffer col-lg-6 col-lg-offset-3">
								  <span aria-hidden="true"></span> Back to login
								</button>
							</a>
					</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
