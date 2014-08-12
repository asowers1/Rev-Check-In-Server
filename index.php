<!DOCTYPE html>

<?php 

?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="assets/ico/favicon.png">

    <title>Rev Check-in</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="assets/css/main.css" rel="stylesheet">

    <link href="assets/css/font-awesome.min.css" rel="stylesheet">

    <link href='http://fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Raleway:400,300,700' rel='stylesheet' type='text/css'>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
    
    <script src="assets/js/modernizr.custom.js"></script>
    
  </head>

  <body>

	<!-- Menu -->

	
	<!-- MAIN IMAGE SECTION -->
	<div id="headerwrap">
		<div class="container">
			<div class="row">
				<?php
					include("Mobile-Detect/Mobile_Detect.php");
					$detect = new Mobile_Detect();
					$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'phone') : 'computer');
						// Check for any mobile device.
						if ($deviceType=='phone'){
						
							echo '<div class="col-lg-8 col-lg-offset-2"><h2>Rev Check-in</h2><h3 style="color:#fff;">By Push Interactive, LLC</h3>';
						}else
							echo '<div class="col-lg-12"><h1>Rev Check-in</h1><h2>By Push Interactive, LLC</h2>';
					
					?>
					<div class="spacer"></div>
					<i class="fa fa-angle-down"></i>
				</div>
			</div><!-- row -->
		</div><!-- /container -->
	</div><!-- /headerwrap -->

	<!-- WELCOME SECTION -->
    <div class="container">
      <div class="row mt">
      	<div class="col-lg-8">
	        <h1>Check in and check out who's at the incubator automatically.</h1>
	        <p>Using iBeacons, there's no need to check in or out, the app does everything for you. It just works.</p>
      	</div>
      	<div class="col-lg-4">
      		<p class="pull-right"><br><button type="button" class="btn btn-green">Coming soon</button></p>
      	</div>
      </div><!-- /row -->
    </div><!-- /.container -->
    
<!-- PORTFOLIO SECTION -->
    <div id="portfolio">
    	<div class="container"
    		<p><h2>Made with next generation technologies.</h2></p>
	    	<div class="row mt">
				<ul class="grid effect-2" id="grid">
					<li><a href="http://hhvm.com"><img src="img/hhvm.png"></a></li>
					<li><a href="http://hacklang.org"><img src="img/hack.png"></a></li>
					<li><a href="http://www.ubuntu.com"><img src="img/ubuntu_logo.jpg"></a></li>
					<li><a href="https://developer.apple.com/ibeacon/"><img src="img/beacon.jpg"></a></li>
					<li><a href="https://developer.apple.com/swift/"><img src="img/swift.jpg"></a></li>
					<li><center><a href="https://developer.apple.com/ios8/"><img src="img/ios8.png"></a></center></li>
					
				</ul>
	    	</div><!-- row -->
	    </div><!-- container -->
    </div><!-- portfolio -->

	<!-- SOCIAL FOOTER --->
	<section id="contact"></section>
	<div id="sf">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 dg">
					<h4 class="ml">THE DEVELOPER</h4>
					<p class="centered"><a href="http://www.linkedin.com/pub/andrew-sowers/4b/921/529/"><i class="fa fa-linkedin"></i></a></p>
					<p class="ml">> Andrew Sowers</p>
				</div>
				<div class="col-lg-6 lg">
					<h4 class="ml">PUSH INTERACTIVE, LLC</h4>
					<p class="centered"><a href="http://experiencepush.com"><i class="fa fa-rocket"></i></a></p>
					<p class="ml">> Push Interactive's website</p>
				</div>
			</div><!-- row -->
		</div><!-- container -->
	</div><!-- Social Footer -->
	
	<!-- CONTACT FOOTER --->
	<div id="cf">
		<div class="container">
			<div class="row">
				<div class="col-lg-8">
		        	<div id="mapwrap">
						<iframe height="400" width="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.google.com/maps/embed/v1/place?q=314%20East%20State%20Street%2C%20%20Ithaca%2C%20NY%2014850&key=AIzaSyCfZBkf2VcCTPzx8Ae8y50BcpSjSkSwf6k"></iframe>
					</div>	
				</div><!--col-lg-8-->
				<div class="col-lg-4">
					<h4>ADDRESS<br/>Located In Beautiful Ithaca NY</h4>
					<br>
					<p>
						314 East State Street, 
						Ithaca, NY 14850
					</p>
					<p>
						Phone: (607) 255-2327  <br/>
						web: <a href="http://www.revithaca.com/">revithaca.com</a>
					</p>
				</div><!--col-lg-4-->
			</div><!-- row -->
		</div><!-- container -->
	</div><!-- Contact Footer -->
	

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
	<script src="assets/js/masonry.pkgd.min.js"></script>
	<script src="assets/js/imagesloaded.js"></script>
    <script src="assets/js/classie.js"></script>
	<script src="assets/js/AnimOnScroll.js"></script>
	<script>
		new AnimOnScroll( document.getElementById( 'grid' ), {
			minDuration : 0.4,
			maxDuration : 0.7,
			viewportFactor : 0.2
		} );
	</script>
  </body>
</html>
