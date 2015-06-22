<?php
	include('include/settings.php');
	$loggedin = false;
	if(isset($_POST['username']) && isset($_POST['password'])) {
		if($_POST['username'] == ADMIN_USERNAME && ($_POST['password'] = md5($_POST['password'])) == ADMIN_PASSWORD) {
			setcookie('username', $_POST['username']);
			setcookie('password', $_POST['password']);
			header('Location: ?result=1');
		}
		else {
			header('Location: ?result=0');
		}
		die();
	}
	if(isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
		if($_COOKIE['username'] != ADMIN_USERNAME || $_COOKIE['password'] != ADMIN_PASSWORD) {
			setcookie('username', '', time() - 3600);
			setcookie('password', '', time() - 3600);
			unset($_COOKIE['username']);
			unset($_COOKIE['password']);
		}
		else {
			$loggedin = true;
		}
	}
	if($loggedin && isset($_POST['action'])) {
		if($_POST['action'] == 0 && isset($_POST['name']) && isset($_FILES['art'])) {
			if($_FILES['art']['error'] != UPLOAD_ERR_OK) {
				header('Location: ?error=-1');
			}
			else if(htmlspecialchars($_POST['name']) == '') {
				header('Location: ?error=0');
			}
			else if(!($_FILES['art']['type'] == 'image/gif' || $_FILES['art']['type'] == 'image/jpeg' || $_FILES['art']['type'] == 'image/png' || $_FILES['art']['type'] == 'image/pjpeg')) {
				header('Location: ?error=1');
			}
			else if(!move_uploaded_file($_FILES['art']['tmp_name'], 'art/' . htmlspecialchars($_POST['name'] . substr($_FILES['art']['name'], strrpos($_FILES['art']['name'], '.'))))) {
				header('Location: ?error=2');
			}
			else {
				header('Location: ?success=0');
			}
			die();
		}
		else if($_POST['action'] == 1 && isset($_POST['name']) && isset($_POST['art'])) {
			if(htmlspecialchars($_POST['name']) == '') {
				header('Location: ?error=0');
			}
			else if(!rename('art/' . $_POST['art'], 'art/' . htmlspecialchars($_POST['name'] . substr($_POST['art'], strrpos($_POST['art'], '.'))))) {
				header('Location: ?error=2');
			}
			else {
				header('Location: ?success=1');
			}
		}
		else if($_POST['action'] == 2 && isset($_POST['art'])) {
			if(!unlink('art/' . $_POST['art'])) {
				header('Location: ?error=3');
			}
			else {
				header('Location: ?success=2');
			}
		}
	}
	$arts = array();
	if($handle = opendir('art')) {
		while(($entry = readdir($handle)) !== false) {
			if($entry != '.' && $entry != '..' && (getimagesize('art/' . $entry) ? true : false)) {
				$name = substr($entry, 0, strrpos($entry, '.'));
				$arts[$name] = $entry;
			}
		}
		closedir($handle);
	}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8"/>
		
		<title><?=TITLE . ' - ' . DESCRIPTION?></title>
		<meta name="description" content="<?=DESCRIPTION?>"/>
		<meta name="keywords" content="<?=KEYWORDS . ', arty, cms, artycms'?>"/>
		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<meta name="generator" content="ArtyCMS">
		
		<!-- Twitter Cards -->
		<meta name="twitter:card" content="summary"/>
		<meta name="twitter:title" content="<?=TITLE?>"/>
		<meta name="twitter:site" content="@<?=TWITTER_USERNAME?>"/>
		<meta name="twitter:creator" content="@<?=TWITTER_USERNAME?>"/>
		<meta name="twitter:url" content="<?='http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']?>"/>
		<meta name="twitter:description" content="<?=DESCRIPTION?>"/>
		
		<!-- Open Graph -->
		<meta property="og:type" content="website"/>
		<meta property="og:title" content="<?=TITLE . ' - ' . DESCRIPTION?>"/>
		<meta property="og:site_name" content="<?=TITLE?>"/>
		<meta property="og:url" content="<?='http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/' . $_SERVER['REQUEST_URI']?>"/>
		<meta property="og:description" content="<?=DESCRIPTION?>"/>
		
		<link rel="stylesheet" href="assets/css/bootstrap.min.css"/>
		<link rel="stylesheet" href="assets/css/index.css"/>
		
		<link rel="icon" type="image/png" href="assets/img/favicon.png">
	</head>
	<body>
		<header class="container">
			<h1 id="header-title"><?=TITLE?></h1>
			<p><?=CATCHPHRASE?></p>
		</header>
		<div class="line"></div>
<?php
	if($loggedin) {
?>
		<div id="admin-area" class="container">
<?php
	if(isset($_GET['error'])) {
		switch($_GET['error']) {
			case 0:
				$error = ERROR_NAMENOTVALID;
				break;
			case 1:
				$error = ERROR_UNKNOWNFILETYPE;
				break;
			case 2:
				$error = ERROR_MOVINGFILE;
				break;
			case 3:
				$error = ERROR_UNABLEDELETEFILE;
				break;
			default:
				$error = ERROR_UNKNOWNERROR;
				break;
		}
		echo '			<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' . $error . '</div>';
	}
	else if(isset($_GET['success'])) {
		switch($_GET['success']) {
			case 0:
				$message = SUCCESS_ARTADDED;
				break;
			case 1:
				$message = SUCCESS_ARTRENAMED;
				break;
			case 2:
				$message = SUCCESS_ARTDELETED;
				break;
			default:
				$message = SUCCESS_ACTIONPERFORMED;
				break;
		}
		echo '			<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' . $message . '</div>';
	}
?>
			<h2><?=ADMINAREA_TITLE?></h2>
			<div class="row">
				<div class="col-md-4"><button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-add"><?=ADMINAREA_BUTTON_ADD?></button></div>
				<div class="col-md-4"><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-rename"><?=ADMINAREA_BUTTON_RENAME?></button></div>
				<div class="col-md-4"><button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-delete"><?=ADMINAREA_BUTTON_DELETE?></button></div>
			</div>
		</div>
		<div class="line"></div>
		<div id="modal-add" class="modal fade" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><?=MODAL_ADD_TITLE?></h4>
					</div>
					<form action="index.php" method="POST" enctype="multipart/form-data">
						<div class="modal-body">
							<div class="form-group">
								<label for="modal-add-name"><?=MODAL_ADD_NAME?></label>
								<input id="modal-add-name" name="name" type="text" class="form-control" placeholder="<?=MODAL_ADD_NAME_PLACEHOLDER?>"/>
							</div>
							<div class="form-group">
								<label for="modal-add-art"><?=MODAL_ADD_ART?></label>
								<input id="modal-add-art" name="art" type="file" class="form-control"/>
								<span class="help-block"><?=MODAL_ADD_ART_TIP?></span>
							</div>
							<input name="action" type="hidden" value="0"/>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?=MODAL_CANCEL?></button>
							<button type="submit" class="btn btn-success"><?=MODAL_ADD_SUBMIT?></button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div id="modal-rename" class="modal fade" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><?=MODAL_RENAME_TITLE?></h4>
					</div>
					<form action="index.php" method="POST">
						<div class="modal-body">
							<div class="form-group">
								<label for="modal-rename-art"><?=MODAL_RENAME_ART?></label>
								<select id="modal-rename-art" name="art" class="form-control">
<?php
	foreach($arts as $name => $file) {
		echo'									<option value="' . $file . '">' . $name . '</option>' . PHP_EOL;
	}
?>
								</select>
							</div>
							<div class="form-group">
								<label for="modal-rename-name"><?=MODAL_RENAME_NEWNAME?></label>
								<input id="modal-rename-name" name="name" type="text" class="form-control" placeholder="<?=MODAL_RENAME_NEWNAME_PLACEHOLDER?>"/>
							</div>
							<input name="action" type="hidden" value="1"/>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?=MODAL_CANCEL?></button>
							<button type="submit" class="btn btn-primary"><?=MODAL_RENAME_SUBMIT?></button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div id="modal-delete" class="modal fade" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><?=MODAL_DELETE_TITLE?></h4>
					</div>
					<form action="index.php" method="POST">
						<div class="modal-body">
							<div class="form-group">
								<label for="modal-delete-art"><?=MODAL_DELETE_ART?></label>
								<select id="modal-delete-art" name="art" class="form-control">
<?php
	foreach($arts as $name => $file) {
		echo'									<option value="' . $file . '">' . $name . '</option>' . PHP_EOL;
	}
?>
								</select>
							</div>
							<input name="action" type="hidden" value="2"/>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?=MODAL_CANCEL?></button>
							<button type="submit" class="btn btn-danger"><?=MODAL_DELETE_SUBMIT?></button>
						</div>
					</form>
				</div>
			</div>
		</div>
<?php
	}
?>
		<section id="articles" class="container">
<?php
	if(sizeof($arts) == 0) {
		echo '			<h2 style="font-family: \'Pacifico\', cursive; font-size: 4em; text-align: center;">' . PAGE_NOART . '</h2><div class="line"></div>';
	}
	foreach($arts as $name => $file) {
?>
			<article>
				<h2><?=$name?></h2>
				<a href="art/<?=$file?>" target="blank"><img id="art-<?=strtolower(str_replace(' ', '-', $name))?>" src="assets/img/loading.gif" data-src="art/<?=$file?>"/></a>
			</article>
			<div class="line"></div>
<?php
	}
?>
		</section>
		<div class="line"></div>
		<footer class="container">
			<div id="shoutbox">
				<span id="shoutbox-title">&nbsp;<span style="color: #27ae60;padding: 0em 0.2em 0em 0.1em;">&#9632;</span>&nbsp;<?=SHOUTBOX_TITLE?></span>
				<div id="shoutbox-messages">
				</div>
				<input id="shoutbox-username" class="form-control" type="text" placeholder="<?=SHOUTBOX_USERNAME?>" maxlength="15" onkeypress="sendMessage(event);"/>
				<input id="shoutbox-message" class="form-control" type="text" placeholder="<?=SHOUTBOX_MESSAGE?>" maxlength="100" onkeypress="sendMessage(event);"/>
			</div>
			<p><strong><?=COPYRIGHT?> - Powered by <a href="https://github.com/<?=APP_AUTHOR . '/' . APP_NAME?>" target="_blank"><?=APP_NAME . ' v' . APP_VERSION?></a></strong>
<?php
	echo '			<br><span id="footer-heart" ';
	if($loggedin) {
		echo 'title="' . HEART_LOGOUT_TITLE . '" onClick="disconnect();"';
	}
	else {
		echo 'title="' . HEART_LOGIN_TITLE . '" data-toggle="modal" data-target="#modal-login"';
	}
	echo '>&#9829;</span></p>' . PHP_EOL;
?>
		</footer>
		<div id="modal-login" class="modal fade" tabindex="-1" role="dialog">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><?=MODAL_LOGIN_TITLE?></h4>
					</div>
					<form action="index.php" method="POST">
						<div class="modal-body">
							<div class="form-group">
								<label for="modal-login-username"><?=MODAL_LOGIN_USERNAME?></label>
								<input id="modal-login-username" name="username" type="text" class="form-control" placeholder="<?=MODAL_LOGIN_USERNAME_PLACEHOLDER?>"/>
							</div>
							<div class="form-group">
								<label for="modal-login-password"><?=MODAL_LOGIN_PASSWORD?></label>
								<input id="modal-login-password" name="password" type="password" class="form-control" placeholder="<?=MODAL_LOGIN_PASSWORD_PLACEHOLDER?>"/>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal"><?=MODAL_CANCEL?></button>
							<button type="submit" class="btn btn-primary"><?=MODAL_LOGIN_SUBMIT?></button>
<?php
	if($loginerror = isset($_GET['result']) && !$_GET['result']) {
		echo '					<span style="color: #E74C3C; float: left;"><strong>' . MODAL_LOGIN_ERROR . '</strong></span>';
	}
?>
						</div>
					</form>
				</div>
			</div>
		</div>
		<script type="text/javascript" src="assets/js/jquery.min.js"></script>
		<script type="text/javascript" src="assets/js/js.cookie.min.js"></script>
		<script type="text/javascript" src="assets/js/jquery.waypoints.min.js"></script>
		<script type="text/javascript" src="assets/js/index.js"></script>
		<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
<?php
	if($loginerror) {
?>
		<script type="text/javascript">
			$('#modal-login').modal();
		</script>
<?php
	}
?>
	</body>
</html>