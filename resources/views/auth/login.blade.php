@extends('app')

@section('content')
<div class="container-fluid">

		<div id="useChromeModal" class="modal fade" role="dialog">
		<div class="modal-dialog" style="width: 550px; padding-top: 25vh;">
		<div class="modal-content" style="padding-left: 0.27em; padding-right: 0.27em;">
		<div class="modal-body text-center">
		<div class="text-right">
		<button type="button" class="close" style="font-size: 1.7em; color: red; opacity: 1;" onclick="$('#useChromeModal').modal('hide');" aria-label="Close">×</button>
		<br>
		</div>
		<h2 style="color: black; font-family: Helvetica, Arial, sans-serif !important;">Please Use Chrome!</h2>
		<br><br>
		<img src="/images/chrome_logo.png" alt="Chrome" style="width: 80%; height: 80%; max-width: 180px; max-height: 180px;">
		<br><br><br>
		<div style="font-variant: normal; font-size: 1.3em; line-height: 1.2em; font-family: Helvetica, Arial, sans-serif !important;">To best experience our courseware, we highly recommend you use the Chrome browser.</div>
		<br><br>
		<a class="btn btn-lg btn-primary" style="color: white; font-family: Helvetica, Arial, sans-serif !important;" href="https://www.google.com/chrome/
" target="_blank">Download Chrome</a>
		</div>
		</div>
		</div>
		</div>

		  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha256-3edrmyuQ0w65f8gfBsqowzjJe2iM6n0nKciPUp8y+7E=" crossorigin="anonymous"></script>
		  <script type="text/javascript">
		  $(document).ready(function() {
			  
			setTimeout(function() {
				
				var browser = "Unknown";
				
				//Check if browser is IE
				if (navigator.userAgent.search("MSIE") >= 0) {
					// insert conditional IE code here
                browser = "Microsoft Internet Explorer";
				}
				//Check if browser is Opera
				else if (navigator.userAgent.search("OPR") >= 0 || navigator.userAgent.search("Opera") >= 0) {
					// insert conditional Opera code here
					 browser = "Opera";
				}
				//Check if browser is Chrome
				else if (navigator.userAgent.search("Chrome") >= 0) {
					// insert conditional Chrome code here
					browser = "Google Chrome";
				}
				//Check if browser is Firefox 
				else if (navigator.userAgent.search("Firefox") >= 0) {
					// insert conditional Firefox Code here
					browser = "Mozilla Firefox";
				}
				//Check if browser is Safari
				else if (navigator.userAgent.search("Safari") >= 0 && navigator.userAgent.search("Chrome") < 0) {
					// insert conditional Safari code here
					browser = "Apple Safari";
				}

				if (browser !== "Google Chrome" && browser !== "Mozilla Firefox") {
					$("#useChromeModal").modal("show");
				}
			}, 300);
		  });
		  </script>

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
					<div id="janrainEngageEmbed" class="col-md-6 col-md-offset-3">
						<!-- janrain content -->
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script async="async" src="/assets/javascript/frontend.js"></script>
@endsection
