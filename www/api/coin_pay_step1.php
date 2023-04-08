<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}
if($decoded_array['ct_amount']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_amount', '');
    exit;
}
if($decoded_array['ct_price']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_price', '');
    exit;
}
if($decoded_array['ct_pay_type']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_pay_type', '');
    exit;
}

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level = 3
";
$row = $DB->fetch_query($query);

if($row['mt_idx'] < 1) {
	echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
	exit;
}
$ct_code = get_ot_code();

$arr = array(
    "ct_code" => $ct_code,
    "ct_amount" => $decoded_array['ct_amount'],
    "ct_price" => $decoded_array['ct_price'],
    "ct_pay_type" => $decoded_array['ct_pay_type'],
    "ct_type" => 1,
    "ct_status" => 1,
    "mt_idx" => $row['mt_idx'],
    "ct_wdate" => "now()",
);

$DB->insert_query("coin_t", $arr);
$idx = $DB->insert_id();

unset($arr);
$arr['ct_idx'] = $idx;
$arr['ct_code'] = $ct_code;
$arr['ct_amount'] = (int)$decoded_array['ct_amount'];
$arr['ct_price'] = (int)$decoded_array['ct_price'];
$payload['data'] = $arr;

if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
	echo result_data('true', '[debug] 코인 결제 요청', $payload);
} else {
	$jwt = JWT::encode($payload, $secret_key);
	echo result_data('true', '코인 결제 요청', $jwt);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>