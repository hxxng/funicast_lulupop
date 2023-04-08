<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['et_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. et_idx', '');
    exit;
}

$query = "select * from event_t where et_status = 1 and idx = ".$decoded_array['et_idx'];
$row = $DB->fetch_assoc($query);

if($row) {
    unset($arr);
    $arr['event'] = array(
        "et_idx" => $row['idx'],
        "et_title" => $row['et_title'],
        "et_url" => $row['et_url'],
        "et_img" => $ct_img_url.'/'.$row['et_img']."?cache=".strtotime($row['et_udate']),
        "et_sdate" => substr($row['et_sdate'],0,10),
        "et_edate" => substr($row['et_edate'],0,10),
        "et_wdate" => substr($row['et_wdate'],0,10),
        "et_content" => $row['et_content'],
    );

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 이벤트 상세보기', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '이벤트 상세보기', $jwt);
    }
} else {
    echo result_data('false', '이벤트가 존재하지 않습니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>