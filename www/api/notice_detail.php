<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['nt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. nt_idx', '');
    exit;
}

$query = "select * from notice_t where idx = ".$decoded_array['nt_idx'];

$row = $DB->fetch_assoc($query);

if($row) {
    unset($arr);
    $arr['list'] = array(
        "nt_idx" => $row['idx'],
        "nt_title" => $row['nt_title'],
        "nt_content" => $row['nt_content'],
        "nt_wdate" => substr($row['nt_wdate'], 0, 10),
    );
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 공지사항 상세보기', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '공지사항 상세보기', $jwt);
    }
} else {
    echo result_data('false', '공지사항 리스트가 존재하지 않습니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>