<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

if($decoded_array['mt_login_type']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_login_type', '');
    exit;
}
if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}
if($decoded_array['mt_app_token']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_app_token', '');
    exit;
}

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'
";
$chk_id = $DB->fetch_query($query);

if($chk_id['mt_idx']) {
    echo result_data('false', '이미 사용중인 아이디입니다.', $arr);
} else {
    unset($arr_query);
    $arr_query = array(
        "mt_login_type" => $decoded_array['mt_login_type'],
        "mt_id" => $decoded_array['mt_id'],
        "mt_level" => 3,
        "mt_app_token" => $decoded_array['mt_app_token'],
        "mt_status" => "Y",
        "mt_wdate" => "now()",
        "mt_ldate" => "now()",
        "mt_grade" => 1,
        "mt_authenfication" => "N",
    );

    $DB->insert_query('member_t', $arr_query);
    $_last_mt_idx = $DB->insert_id();

    $arr['mt_idx'] = $_last_mt_idx;
    $arr['mt_id'] = $decoded_array['mt_id'];
    $arr['mt_level'] = 3;

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 회원가입 정보', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '회원가입 정보', $jwt);
    }
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>