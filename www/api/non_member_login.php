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

$query = "select * from member_t where mt_id = '".$decoded_array['mt_id']."' and mt_level != 1";
$row = $DB->fetch_assoc($query);
$count = $DB->count_query($query);
if($count > 0) {
    unset($arr_query);
    $arr_query = array(
        "mt_ldate" => "now()",
        "mt_status" => "Y",
    );

    $where_query = "idx = '".$row['mt_idx']."'";

    $DB->update_query('member_t', $arr_query, $where_query);

    $arr = $row;
    $arr['mt_level'] = (int)$row['mt_level'];

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 비회원 로그인 정보', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '비회원 로그인 정보', $jwt);
    }
} else {
    $arr = array();
    unset($arr_query);
    $arr_query = array(
        "mt_id" => $decoded_array['mt_id'],
        "mt_level" => 5,
        "mt_wdate" => "now()",
        "mt_ldate" => "now()",
    );

    $DB->insert_query('member_t', $arr_query);
    $_last_mt_idx = $DB->insert_id();

    $query = "select *, idx as mt_idx from member_t where idx = ".$_last_mt_idx;
    $row = $DB->fetch_assoc($query);

    $arr = $row;
    $arr['mt_level'] = (int)$row['mt_level'];
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 비회원 회원가입', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '비회원 회원가입', $jwt);
    }
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>