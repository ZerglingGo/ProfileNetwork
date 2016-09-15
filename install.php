<?php
if(!is_writable(".")) {
	echo "Please check your directory writable.";
	exit;
}

if(isset($_POST['name']) && isset($_POST['id']) && isset($_POST['pw']) && isset($_POST['host']) && isset($_POST['table'])) {
	file_put_contents("config.php",
		"<?php\n".
		"define('DB_NAME',  '".$_POST['name']."');\n".
		"define('DB_USER',  '".$_POST['id']."');\n".
		"define('DB_PASS',  '".$_POST['pw']."');\n".
		"define('DB_HOST',  '".$_POST['host']."');\n".
		"define('DB_TABLE', '".$_POST['table']."');\n".
		"define('DB_CHARSET', 'utf8mb4');\n".
		"define('DB_COLLATE', 'utf8mb4_unicode_ci');\n"
	);

	require_once("config.php");

	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	$q = "CREATE TABLE `".DB_TABLE."` (".
		"`userkey` char(60) NOT NULL,".
		"`uuid` char(8) NOT NULL,".
		"`nickname` varchar(255) NOT NULL,".
		"`description` varchar(255) NOT NULL,".
		"`homepage` varchar(255) NOT NULL,".
		"`image` varchar(255) NOT NULL".
		") ENGINE=InnoDB DEFAULT CHARSET=".DB_CHARSET." COLLATE=".DB_COLLATE.";";
	$mysqli->query($q);

	echo "Successfully Installed!";
	exit;
}
?>
<!doctype html>
<html lang="ko">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>ProfileNetwork Install</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha256-U5ZEeKfGNOja007MMD3YBI0A3OSZOQbeG6z2f2Y0hu8=" crossorigin="anonymous"></script>
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700">
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/icon?family=Material+Icons">
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha256-916EbMg70RQy9LHiGkXzG8hSg9EdNy97GazNG/aiY1w=" crossorigin="anonymous" />
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/4.0.2/bootstrap-material-design.min.css" integrity="sha256-X/mlyZAafJ8j5e74pWh4+qNUD1zurCvLA6hODVobQX0=" crossorigin="anonymous" />
		<style>
			.form_wrap {
				width: 500px;
				margin: 200px auto 0;
				display: none;
			}
		</style>
	</head>
	<body>
		<div class="form_wrap">
			<form class="form-horizontal" id="install" action="#" method="POST">
				<div class="form-group">
					<label for="name" class="col-sm-3 control-label">DB Name</label>
					<div class="col-sm-9">
						<input type="text" class="form-control" id="name" name="name" required>
					</div>
				</div>
				<div class="form-group">
					<label for="id" class="col-sm-3 control-label">Username</label>
					<div class="col-sm-9">
						<input type="text" class="form-control" id="id" name="id" required>
					</div>
				</div>
				<div class="form-group">
					<label for="pw" class="col-sm-3 control-label">Password</label>
					<div class="col-sm-9">
						<input type="password" class="form-control" id="pw" name="pw" required>
					</div>
				</div>
				<div class="form-group">
					<label for="host" class="col-sm-3 control-label">DB Host</label>
					<div class="col-sm-9">
						<input type="text" class="form-control" id="host" name="host" value="localhost" required>
					</div>
				</div>
				<div class="form-group">
					<label for="table" class="col-sm-3 control-label">Table Name</label>
					<div class="col-sm-9">
						<input type="text" class="form-control" id="table" name="table" value="profilenetwork" required>
					</div>
				</div>
				<div class="form-group submit">
					<div class="col-sm-offset-3 col-sm-9">
						<button type="submit" id="submit" class="btn btn-default">Install</button>
					</div>
				</div>
			</form>
		</div>

		<script>
		$(function() {
			$(".form_wrap").fadeIn(1000);

			$("#install").submit(function(e) {
				var formObj = $(this);
				var formURL = formObj.attr("action");
				var formData = new FormData(this);
				$.ajax({
					url: formURL,
					type: 'POST',
					data:  formData,
					mimeType:"multipart/form-data",
				    contentType: false,
				    cache: false,
				    processData:false,
					success: function(data, textStatus, jqXHR) {
						$("#submit").text(data);
						setTimeout(function() {
							location.href = "index.php";
						}, 3000);
					},
					error: function(jqXHR, textStatus, errorThrown) {
						$("#key").val("");
						var oldText = $("#submit").text();
						$("#submit").text(jqXHR.responseText);
						setTimeout(function() {
							$("#submit").text(oldText);
						}, 3000);
					}
				});
				e.preventDefault();
			});
		});
		</script>
	</body>
</html>
