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
if($decoded_array['qt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. qt_idx', '');
    exit;
}

$query = "select *, a1.idx as mt_idx from member_t a1 where mt_level in (3,5) and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query);
if($row_m['mt_idx']) {
    $query = "select * from qna_t where idx = ".$decoded_array['qt_idx'];
    $row = $DB->fetch_assoc($query);

    if($row) {
        $DB->del_query("qna_t", " idx = ".$decoded_array['qt_idx']);

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 문의 삭제', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '문의 수정', $jwt);
        }
    } else {
        echo result_data('false', '문의가 존재하지 않습니다.', $arr);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>