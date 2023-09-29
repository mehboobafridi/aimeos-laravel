<?php


if (!session_id())
	session_start();

    define('SPAPI_APP_ID', 'amzn1.sp.solution.5b275cf1-9284-4876-865a-4782a7277984');

if ($_POST) {
    
	$data = [];

	$state = bin2hex(random_bytes(32));
	$data['state'] = $state;

	$_SESSION['spapi_auth_state'] = $state;
	$_SESSION['spapi_auth_time'] = time();

	//changed to Germany.
	$oauthUrl = 'https://vendorcentral.amazon.de/apps/authorize/consent';
	
	$query = [
		'application_id' => SPAPI_APP_ID,
		'state' => $state,
		'version' => 'beta',
		'redirect_uri' => 'https://pure-spapi.easybay.pk/callbackAuth.php',
	];
	$oauthUrl .= '?'.http_build_query($query);
	$data['url'] = $oauthUrl;

	header('Location: '.$oauthUrl);
	exit;

	echo '<pre> ';
	echo PHP_EOL;
	print_r($_POST);
	print_r($data);
	echo PHP_EOL;
	echo '</pre>';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<form method="POST">
    <input type="hidden" name="time" value="<?php echo time()?>" />
    <input type="submit" value="Authorize" />
</form>
</body>
</html>