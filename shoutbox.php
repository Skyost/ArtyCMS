<?php
	if(!isset($_POST['action'])) {
		die();
	}
	print_r($_POST);
	if($_POST['action'] == 0 && isset($_POST['username']) && isset($_POST['message'])) {
		$username = filter_var(trim($_POST["username"]), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
		$message = filter_var(trim($_POST["message"]), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
		$data = parse_messages();
		array_push($data, array(
				'username' => filter_var(trim($_POST["username"]), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH),
				'message' => filter_var(trim($_POST["message"]), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH)
			)
		);
		if(!file_put_contents('messages.json', json_encode($data))) {
			die('Unable to write to file');
		}
	}
	else {
		die();
	}
	
	function parse_messages() {
		$json = file_get_contents('messages.json');
		if(!$json) {
			die('Unable to read file');
		}
		$data = json_decode($json, true);
		if($data == null) {
			die('Unable to parse JSON file');
		}
		unset($json);
		return $data;
	}
?>