<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

if($decoded_array['ct_price']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_price', '');
    exit;
}

$point = $decoded_array['ct_price'] * 0.03;

$arr['point'] = (int)$point;

$payload['data'] = $arr;

if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
    echo result_data('true', '[debug] 적립예상포인트', $payload);
} else {
    $jwt = JWT::encode($payload, $secret_key);
    echo result_data('true', '적립예상포인트', $jwt);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>