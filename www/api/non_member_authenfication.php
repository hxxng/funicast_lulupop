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

$arr = array();
$query = "
select *, a1.idx as mt_idx from member_t a1
where a1.mt_level = 5 and a1.mt_id = '".$decoded_array['mt_id']."'
";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    if($row['mt_authenfication'] == "Y") {
        if($decoded_array['debug_jwt']==DEBUG_JWT) {
            echo result_data('true', '[debug] 비회원 휴대폰 인증 여부', $payload);
        }else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '비회원 휴대폰 인증 여부', $jwt);
        }
    } else {
        if($decoded_array['debug_jwt']==DEBUG_JWT) {
            echo result_data('false', '[debug] 비회원 휴대폰 인증 여부', $payload);
        }else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('false', '비회원 휴대폰 인증 여부', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 비회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>