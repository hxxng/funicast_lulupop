<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['search_txt']=="") {
    echo result_data('false', '잘못된 접근입니다. search_txt', '');
    exit;
}
if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}

$query2 = "select * from member_t where mt_id = '".$decoded_array['mt_id']."' and mt_level = 3";
$member = $DB->fetch_assoc($query2);
if($member['idx']) {
    $DB->insert_query("search_log_t", array("mt_idx" => $member['idx'], "slt_txt" => $decoded_array['search_txt'], 'slt_wdate' => "now()"));
}

$payload['data'] = array("mt_idx" => $member['idx'], "search_txt" => $decoded_array['search_txt']);

if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
    echo result_data('true', '[debug] 검색 기록 등록', $payload);
} else {
    $jwt = JWT::encode($payload, $secret_key);
    echo result_data('true', '검색 기록 등록', $jwt);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>