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
					@if(empty($user))
						<script src="../../assets/javascript/frontend.js" type="text/javascript"></script>
						<form class="form-horizontal" role="form" method="POST" action="Social/register">
							<input type="hidden" name="Email" value="{{ $verifiedEmail }}">
							<input type="hidden" name="ConfirmEmail" value="{{ $verifiedEmail }}">
							<input type="hidden" name="hasLicense" value="0">

								<button id="registerNewAcct" type="button" class="btn btn-primary btn-lg top-buffer col-centered block col-lg-offset-3 bottom-buffer">
								  <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Register new account
								</button>
								<div class="row">
									
								</div>
							<div class="form-group" id="licensePrompt" style="display: none;">
								<label class="col-md-4 control-label">Do you have a License Key?</label>
								<div class="col-md-6">
									<div class="btn-group" role="group" aria-label="No License">
										  <button type="button" data-value="1" class="btn btn-default">Yes</button>
										  <button type="button" data-value="0" class="btn btn-default">No (Access EHAC and NIHSS only)</button>
									</div>
								</div>
							</div>
						

						</form>

					@else
							<div class="row bottom-buffer">	
								<div class="col-md-6 col-md-offset-2">
								<h4>Hmmm...</h4>
								</br>
								It looks like you might already have an account with us.
								Simply click the link to login with your "{{ $providerName }}" account for {{ $verifiedEmail }}
								</div>				
							</div>
							<div class="col-lg-12">
								<form class="form-horizontal" role="form" method="POST" action="Social/linkNow">
									<input type="hidden" name="_token" value="{{ csrf_token() }}">
									<input type="hidden" name="user" value="{{ $user }}">
									<input type="hidden" name="providerName" value="{{ $providerName }}">
									<input type="hidden" name="email" value="{{ $verifiedEmail }}">
									<div class="row">
										<button type="submit" class="btn btn-success btn-lg top-buffer col-centered block col-lg-offset-3 bottom-buffer">
										  <span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span> Login
										</button>
									</div>
								</form>
							</div>
					@endif

							<div class="row">
								
							</div>
							<div class="col-lg-12 col-centered text-center">
								<h3 class="bottom-buffer">Or</h3>
							</div>
							<form class="form-horizontal bottom-buffer" role="form" method="POST" action="Social/differentAccount">
								<input type="hidden" name="_token" value="{{ csrf_token() }}">
								<input type="hidden" name="providerName" value="{{ $providerName }}">
								<input type="hidden" name="email" value="{{ $verifiedEmail }}">
									<button type="submit" class="btn btn-primary btn-lg top-buffer col-centered block col-lg-offset-3 bottom-buffer">
									  <span class="glyphicon glyphicon-user" aria-hidden="true"></span> Link to an Apex account
									</button>
							</form>
						</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
