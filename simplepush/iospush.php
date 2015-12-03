<?php
// echo json_encode("qwe");

// $url = 'https://www.studypool.com/simplepush/iospush.php?';
// foreach ($_GET as $key=>$value) {
// 	$url = $url . $key."=".$value."&";
// }
// $url = substr($url, 0, -1);
// $ch  = curl_init();
// curl_setopt($ch, CURLOPT_URL, $url);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //this prevent printing the 200json code
// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); //timeout 1s
// curl_setopt($ch, CURLOPT_TIMEOUT, 1); //timeout 1s
// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// $result = curl_exec($ch);
// curl_close($ch);


if(isset($_GET['token'])){
	$deviceToken = $_GET['token'];
}else{
	exit();
}


if(isset($_GET['unread']) && $_GET['unread']){
	$num = intval($_GET['unread']);
}else{
	$num = 1;
}

$title = "";

if(isset($_GET['title']) && $_GET['title']){
	$title = $_GET['title'];
} else {
	$title = "没六儿";
}

// Put your private key's passphrase here:
$passphrase = 'xiaorui';

// Put your alert message here:
$message = $title;

////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'MeiliuerPushAPN.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// Open a connection to the APNS server
// production: ssl://gateway.push.apple.com:2195
$fp = stream_socket_client(
	'ssl://gateway.sandbox.push.apple.com:2195', $err,
	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
	exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS' . PHP_EOL;


//default
$content = array("message"=>$message);


// Create the payload body
$body['aps'] = array(
	'alert' => $message,
	'content' => $content, 
	'badge' => $num,
    'sound' => 'chime.aiff'
	);

// Encode the payload as JSON
$payload = json_encode($body);

echo $payload;
// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result){
	echo 'Message not delivered' . PHP_EOL;
}
else{
	echo 'Message successfully delivered' . PHP_EOL;
	// echo $_GET['token'];
	// echo $_GET['title'];
	// echo $_GET['unread'];
	// echo $result;
	//echo $msg;
}

// Close the connection to the server
fclose($fp);
