<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Create or log into your Apex Innovations account.">
	<title>Apex Innovations</title>

	<link href="/css/app.css" rel="stylesheet">


	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body class="sunburst">
<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="https://www.apexinnovations.com">
				<img class="brand-image" src="{{url('/grfx/ApexLogo500.svg')}}" alt="Apex Innovations, Education Healthcare Relies On"/>
				<img width="45" height="50" class="brand-icon" src="{{url('grfx/starIcon.svg')}}" alt="star icon">
			</a>
		</div>
		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav navbar-right text-uppercase text-center">
				<li>
					<a href="{{url('/')}}" style="cursor:pointer;" class=" activeNav">Login</a>
					<?php


					if (!isset($_SESSION['userID'])) {

					} else {
						echo "<a href='https://www.apexinnovations.com/MyCurriculum.php'>My Curriculum</a>";
					}
					?>
				</li>
				<?php
				if (!isset($_SESSION['userID'])) {
					echo '<li>
                                        <a href="https://www.apexinnovations.com/CreateAccount.php">Create Account</a>
                                    </li>';
				} else {
					echo '
                                    <li class="dropdown">
                                      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Account <span class="caret"></span></a>
                                      <ul class="dropdown-menu">
                                        <li><a href="https://login.apexinnovations.com/auth/logout" style="display:block;">Log off</a></li>
                                        <li><a target="_blank" href="https://www.apexinnovations.com/EditMyProfile.php" style="display:block;">Edit Profile</a></li>
                                        <li><a target="_blank" href="https://www.apexinnovations.com/RegisterLicenseKey.php" style="display:block;">Register License</a></li>
                                        <li><a target="_blank" href="https://www.apexinnovations.com/MyHistoricalData.php" style="display:block;">Test History</a></li>
                                      </ul>
                                    </li>';

				}

				?>


				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Products <span class="caret"></span></a>
					<ul class="dropdown-menu">
						<li><a href="https://www.apexinnovations.com/products.html#cardiac" style="display:block;">Cardiac Courseware</a></li>
						<li><a href="https://www.apexinnovations.com/products.html#neuro" style="display:block;">Neuro Courseware</a></li>
						<li><a href="https://www.apexinnovations.com/products.html#free" style="display:block;">Free Courseware</a></li>
						<li><a href="https://www.apexinnovations.com/products.html#mirule" style="display:block;">MI Rule Visions</a></li>
						<li><a href="https://www.apexinnovations.com/products.html" style="display:block;">All Products</a></li>
					</ul>
				</li>
				<!-- <a href="products.html">Products</a> -->

				<li>
					<a href="https://www.apexinnovations.com/store">Store</a>
				</li>
				<li>
					<a href="https://www.apexinnovations.com/team.html">Team</a>
				</li>
				<li>
					<a href="https://www.apexinnovations.com/contactUs.html">Contact Us</a>
				</li>
			</ul>
		</div>
		<!-- /.navbar-collapse -->
	</div>
	<!-- /.container -->
</nav>
<div class="row bigPadTop">&nbsp;</div>
	@yield('content')

	<!-- Scripts -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>
	<script src={{ asset('/assets/javascript/frontend.js') }}></script>
	<script src={{ asset('/assets/javascript/contactSupport.js') }}></script>
</body>
</html>
