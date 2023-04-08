<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

$query = "select tt_agree1, tt_agree2 from terms_t a1";

$arr = array();
unset($list);
$sql_query = $query." order by a1.idx desc";
$list = $DB->select_query($sql_query);

if($list){
    foreach ($list as $row) {
        $arr['agree1'] = $row['tt_agree1'];
        $arr['agree2'] = $row['tt_agree2'];
    }
}

$payload['data'] = $arr;

if($decoded_array['debug_jwt']==DEBUG_JWT) {
    echo result_data('true', '[debug] 이용약관/개인정보처리방침', $payload);
}else {
    $jwt = JWT::encode($payload, $secret_key);
    echo result_data('true', '이용약관/개인정보처리방침', $jwt);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>