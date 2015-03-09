@extends('master')

@section('content')

 <div class="site-wrapper">

      <div class="site-wrapper-inner">

        <div class="cover-container">

         
          <div class="inner cover">
            <div class="row">
            	<div class="col-md-12 cover-heading"><img src='images/lifepoint.png'/></div>
            </div>
            
            <hr class="thickNBlue"/>

            <p class="lead">Early Heart Attack Care Program</p>
            <p class="lead">
              <a href="//apexinnovations.com/CreateAccount.php?NIH=1&Acct=1"><img class="ehacButton" src="images/EHAC_Button.png"/></a>
            </p>
            <p>Powered by:</p>
            <div class="row">
            	<div class="col-md-3"></div>
            	<div class="col-md-6">
		            <h1>{{$name}}</h1>
                @foreach ($lessons as $lesson)
                  <h2>{{$lesson}}</h2>
                @endforeach
		        </div>
	            <div class="col-md-3"></div>
	        </div>
          </div>

        </div>

      </div>

    </div>


@stop