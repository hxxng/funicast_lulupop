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

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level = 3
";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    unset($arr_query);
    $arr_query = array(
        "mt_level" => '1',
        "mt_status" => "N",
        "mt_rdate" => "now()",
    );
    $DB->update_query('member_t', $arr_query, "idx = '" . $row['mt_idx'] . "'");
    $DB->del_query("point_log_t", " mt_idx = ".$row['mt_idx']);

    $payload['data'] = $arr_query;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 회원탈퇴', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '회원탈퇴', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr_query);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>