<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once("config.php");

$type = "load";

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$mysqli->set_charset(DB_CHARSET);

if(isset($_GET['uuid'])) {
	$stmt = $mysqli->prepare("SELECT * FROM ".DB_TABLE." WHERE uuid = ?");
	$stmt->bind_param("s", $_GET['uuid']);
	$stmt->execute();

	$result = $stmt->get_result();
	$row = $result->fetch_assoc();

	unset($row['userkey']);
	unset($row['uuid']);
	header("Content-Type: application/json");
	echo json_encode($row);
	exit;
} elseif(isset($_GET['create'])) {
	$type = "create";
}

if(isset($_POST['type'])) {
	if($_POST['type'] === "create") {
		$key = $_POST['key'];

		$q = "SELECT * FROM ".DB_TABLE;
		$result = $mysqli->query($q);

		while($row = $result->fetch_assoc()) {
			if(password_verify($key, $row['userkey'])) {
				header("HTTP/1.1 403 Forbidden");
				echo "Already exists key";
				exit;
			}
		}

		$hashed = password_hash($key, PASSWORD_BCRYPT);
		$uuid = bin2hex(random_bytes(4));
		$desc = $uuid."'s Profile";
		$homepage = "about:blank";
		$tempimg = "http://placehold.it/250x250";

		$stmt = $mysqli->prepare("INSERT INTO ".DB_TABLE." (`userkey`, `uuid`, `nickname`, `description`, `homepage`, `image`) VALUES (?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("ssssss", $hashed, $uuid, $uuid, $desc, $homepage, $tempimg);
		$stmt->execute();

		echo "Successfully Created!";
		exit;
	} elseif($_POST['type'] === "load") {
		$key = $_POST['key'];

		$q = "SELECT * FROM ".DB_TABLE;
		$result = $mysqli->query($q);

		while($row = $result->fetch_assoc()) {
			if(password_verify($key, $row['userkey'])) {
				unset($row['userkey']);
				header("Content-Type: application/json");
				echo json_encode($row);
				exit;
			}
		}

		header("HTTP/1.1 401 Unauthorized");
		echo "Invalid key";
		exit;
	} elseif($_POST['type'] === "save") {
		$stmt = $mysqli->prepare("SELECT * FROM ".DB_TABLE." WHERE uuid = ?");
		$stmt->bind_param("s", $_POST['uuid']);
		$stmt->execute();

		$result = $stmt->get_result();
		$row = $result->fetch_assoc();

		if(password_verify($_POST['key'], $row['userkey'])) {
			$stmt = $mysqli->prepare("UPDATE ".DB_TABLE." SET nickname = ? , description = ? , homepage = ? , image = ? WHERE uuid = ?");
			$stmt->bind_param("sssss", $_POST['nickname'], $_POST['description'], $_POST['homepage'], $_POST['image'], $_POST['uuid']);
			$stmt->execute();

			echo "Successfully Saved!";
			exit;
		} else {
			header("HTTP/1.1 401 Unauthorized");
			echo "Invalid key";
			exit;
		}
	}
}
?>
<!doctype html>
<html lang="ko">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>ProfileNetwork</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.0/jquery.min.js" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" crossorigin="anonymous"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha256-U5ZEeKfGNOja007MMD3YBI0A3OSZOQbeG6z2f2Y0hu8=" crossorigin="anonymous"></script>
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Roboto:300,400,500,700">
		<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/icon?family=Material+Icons">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha256-916EbMg70RQy9LHiGkXzG8hSg9EdNy97GazNG/aiY1w=" crossorigin="anonymous" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/4.0.2/bootstrap-material-design.min.css" integrity="sha256-X/mlyZAafJ8j5e74pWh4+qNUD1zurCvLA6hODVobQX0=" crossorigin="anonymous" />
		<style>
			.form_wrap {
				width: 500px;
				margin: 200px auto 0;
				display: none;
			}

			.form_temp {
				display: none;
			}
		</style>
	</head>
	<body>
		<div class="form_wrap">
			<form class="form-horizontal" id="profile" action="#" method="POST">
				<input type="hidden" id="type" name="type" value="<?php echo $type; ?>">
				<div class="form-group">
					<label for="key" class="col-sm-3 control-label">Key</label>
					<div class="col-sm-9">
						<input type="password" class="form-control" id="key" name="key" required>
					</div>
				</div>
				<div class="form-group submit">
					<div class="col-sm-offset-3 col-sm-4 text-center">
						<button type="submit" id="submit" class="btn btn-default"><?php echo ucfirst($type); ?> Profile</button>
                    </div>
                    <?php if($type != "create") { ?>
					<div class="col-sm-5 text-center">
						<button type="button" id="create" class="btn btn-default">Create Profile</button>
                    </div>
                    <?php } else { ?>
                    <div class="col-sm-5 text-center">
                        <button type="button" id="back" class="btn btn-default">Back</button>
                    </div>
                    <?php } ?>
				</div>
			</form>
		</div>

		<div class="form_temp">
			<input class="form-group" type="hidden" id="uuid" name="uuid">
			<div class="form-group">
				<label for="nickname" class="col-sm-3 control-label">Nickname</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="nickname" name="nickname" autocomplete="off" required>
				</div>
			</div>
			<div class="form-group">
				<label for="description" class="col-sm-3 control-label">Description</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="description" name="description" autocomplete="off" required>
				</div>
			</div>
			<div class="form-group">
				<label for="homepage" class="col-sm-3 control-label">Homepage</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="homepage" name="homepage" autocomplete="off" required>
				</div>
			</div>
			<div class="form-group">
				<label for="image" class="col-sm-3 control-label">Image URL</label>
				<div class="col-sm-9">
					<input type="text" class="form-control" id="image" name="image" autocomplete="off" required>
				</div>
			</div>
			<div class="form-group">
				<label for="json" class="col-sm-3 control-label">JSON Code</label>
				<div class="col-sm-9">
					<p class="form-control-static" id="json"></p>
				</div>
			</div>
		</div>

		<script>
		$(function() {
			if($("#type").val() === "save") {
				$("#type").val("load");
			}
			$(".form_wrap").fadeIn(1000);

			$("#profile").submit(function(e) {
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
						if($("#type").val() === "create") {
							$("#type").val("load");
							$("#key").val("");
							$("#submit").text(data);
							setTimeout(function() {
								$("#submit").text("Load Profile");
							}, 3000);
							return;
						} else if($("#type").val() === "save") {
							$("#submit").text(data);
							setTimeout(function() {
								$("#submit").text("Save Profile");
							}, 3000);
							return;
						}
						var json = $.parseJSON(data);
						$("#type").val("save");
						$("#key").prop("readonly", true);
						$("#nickname").val(json.nickname);
						$("#description").val(json.description);
						$("#homepage").val(json.homepage);
						$("#image").val(json.image);
						$("#uuid").val(json.uuid);
						$("#json").html("<a href='/"+json.uuid+".json'>"+json.uuid+"</a>");
						$(".form_temp .form-group").hide().insertBefore($(".submit")).slideDown("slow");
						$("#submit").text("Welcome, " + json.nickname + "!");
						setTimeout(function() {
							$("#submit").text("Save Profile");
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

            $("#create").on('click', function() {
                location.href = "index.php?create";
            });

            $("#back").on('click', function() {
                location.href = "index.php";
            });
		});
		</script>
	</body>
</html>
