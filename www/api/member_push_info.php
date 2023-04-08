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

$query = "select *, a1.idx as mt_idx from member_t a1 where mt_level in (3,5) and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query);

if($row_m['mt_idx']) {
    $arr['mt_pushing'] = $row_m['mt_pushing'];
    $arr['mt_pushing1'] = $row_m['mt_pushing1'];
    $arr['mt_pushing2'] = $row_m['mt_pushing2'];
    $arr['mt_pushing3'] = $row_m['mt_pushing3'];
    $arr['mt_pushing4'] = $row_m['mt_pushing4'];

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 알림 정보', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '알림 정보', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>