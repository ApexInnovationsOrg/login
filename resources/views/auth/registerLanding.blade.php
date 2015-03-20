@extends('app')

@section('content')
<div class="container-fluid">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Create New Account</div>
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

					<form class="form-horizontal" role="form" method="POST" action="/auth/register1">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<input type="hidden" name="prevInput" value="{{ isset($prevInput) ? $prevInput : '' }}">
						<div class="form-group">
							<label class="col-md-4 control-label">Do you have a license key?</label>
							<div class="btn-group" role="group" aria-label="No License">
							  <button type="button" class="btn btn-default">Yes</button>
							  <button type="button" class="btn btn-default">No</button>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" class="btn btn-primary">
									Next
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
