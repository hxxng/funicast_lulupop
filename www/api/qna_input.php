<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['pt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. pt_idx', '');
    exit;
}
if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}
if($decoded_array['qt_content']=="") {
    echo result_data('false', '잘못된 접근입니다. qt_content', '');
    exit;
}

$query_m = "select *, a1.idx as mt_idx from member_t a1 where mt_level = 3 and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query_m);
if($row_m['mt_idx']) {
    if($decoded_array['qt_secret'] == "") {
        $qt_secret = "N";
    } else {
        $qt_secret = $decoded_array['qt_secret'];
    }
    $arr = array(
        "mt_idx" => $row_m['mt_idx'],
        "pt_idx" => $decoded_array['pt_idx'],
        "qt_content" => $decoded_array['qt_content'],
        "qt_status" => 1,
        "qt_wdate" => "now()",
        "qt_secret" => $qt_secret,
    );
    $DB->insert_query("qna_t", $arr);
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 문의 작성 완료', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '문의 작성 완료', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>