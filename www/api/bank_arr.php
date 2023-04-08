<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
$arr_bank = array('경남','광주','국민','기업','농협',
    '단위농협', '대구', '부산', '산업', '새마을', '산림', '수협', '신한', '신협', '씨티', '우리', '우체국', '저축', '전북', '제주', '카카오', '케이', '토스', '하나', 'SC제일'
);

$payload['data'] = $arr_bank;

if($decoded_array['debug_jwt']==DEBUG_JWT) {
    echo result_data('true', '[debug] 은행 리스트', $payload);
}else {
    $jwt = JWT::encode($payload, $secret_key);
    echo result_data('true', '은행 리스트', $jwt);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>