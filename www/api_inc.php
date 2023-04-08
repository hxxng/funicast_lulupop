<?
header("Content-Type: application/json");
require $_SERVER['DOCUMENT_ROOT'].'/api/jwt/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'].'/config_inc.php';
use \Firebase\JWT\JWT;

$issue_date = time();
$not_before = $issue_date;
$expire_date = $not_before + 60*60;
$server_name = SERVER_NAME;
$secret_key = SECRETKEY;

if($_POST['jwt_data']) {
    $jwt_decoded = JWT::decode($_POST['jwt_data'], $secret_key, array('HS256'));
    $decoded_array = json_decode(json_encode($jwt_decoded), true);
}

if($_POST['debug_jwt']==DEBUG_JWT) {
    $decoded_array = json_decode(json_encode($_POST), true);
}

$payload = array(
    'iat' => $issue_date,
    'jti' => $secret_key,
    'iss' => $server_name,
    'nbf' => $not_before,
    'exp' => $expire_date,
);

//$DB->insert_query("zzz", array("pagename"=>$_SERVER['PHP_SELF'] ,"contents"=>json_encode($decoded_array), "remoteip"=>$_SERVER['REMOTE_ADDR'], "regdate"=>"now()"));
?>
