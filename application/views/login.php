<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>JBOCD</title>
	
	<link href="<?php echo asset_url(); ?>css/metro-bootstrap.css" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>css/metro-bootstrap-responsive.css" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>css/iconFont.css" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>css/main.css" rel="stylesheet">
    <link href="<?php echo asset_url(); ?>js/prettify/prettify.css" rel="stylesheet">


    <!-- Load JavaScript Libraries -->
    <script src="<?php echo asset_url(); ?>js/jquery/jquery.min.js"></script>
    <script src="<?php echo asset_url(); ?>js/jquery/jquery.widget.min.js"></script>
    <script src="<?php echo asset_url(); ?>js/jquery/jquery.mousewheel.js"></script>
    <script src="<?php echo asset_url(); ?>js/prettify/prettify.js"></script>

    <!-- Metro UI CSS JavaScript plugins -->
	<script>
		$(function(){
			if ((document.location.host.indexOf('.dev') > -1) || (document.location.host.indexOf('modernui') > -1) ) {
				$("<script/>").attr('src', '<?php echo asset_url(); ?>js/metro/metro-loader.js').appendTo($('head'));
			} else {
				$("<script/>").attr('src', '<?php echo asset_url(); ?>js/metro.min.js').appendTo($('head'));
			}
		})
	</script>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body class="metro">
	<div class=" login">
		<div class="container">
			<?php echo form_open('login/authenticate'); ?>
			<div class="grid parent">
				<div class="row float-middle">
						<span class="span6">
							<blockquote class="place-right">
								<h1 class="header"><i class="icon-cloud"></i> JBOCD</h1>
								<small>BETA</small>
							</blockquote>
							<!--<h1 class="text-right header">JBOCD</h1>-->
						</span>
						<span class="span5">
							<div class="input-control text" data-role="input-control">
								<?php echo form_input(array('placeholder'=>'Login ID', 'name'=>'login', 'autofocus'=>'', 'data-state'=>(isset($error)?'error':''))); ?>
								<button class="btn-clear" tabindex="-1" type="button"></button>
							</div>
							<div class="input-control password" data-role="input-control">
								<?php echo form_password(array('placeholder'=>'Password', 'name'=>'pw', 'data-state'=>(isset($error)?'error':''))); ?>
								<button class="btn-reveal" tabindex="-1" type="button"></button>
							</div>
							<?php echo form_submit(array('name'=>'submit', 'value'=>'Login')); ?>
						</span>
				</div>
				
			</div>
		</div>
	</div>
</body>

</html>
