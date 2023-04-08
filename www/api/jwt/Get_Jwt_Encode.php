<?
	include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";

	require 'vendor/autoload.php';

	use \Firebase\JWT\JWT;

	$token_id = base64_encode("token_id");
	$issue_date = time();
	$not_before = $issue_date;
	$expire_date = $not_before + 60*60;
	$server_name = "server_name";
	$secret_key = "secret_key";

	$payload = array(
		'iat' => $issue_date,
		'jti' => $token_id,
		'iss' => $server_name,
		'nbf' => $not_before,
		'exp' => $expire_date,
		'data' => [
			"url" => 'url',
			"mt_id" => 'mt_id',
			"mt_name" => 'mt_name',
			"mt_level" => 'mt_level'
		]
	);

	echo "encode:\n";
	$jwt = JWT::encode($payload, $secret_key);
	printr($jwt);

	echo "decode:\n";
	$decoded = JWT::decode($jwt, $secret_key, array('HS256'));
	printr($decoded);

	include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>
