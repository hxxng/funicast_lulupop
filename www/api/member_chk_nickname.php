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
if($decoded_array['mt_nickname']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_nickname', '');
    exit;
}

$arr = array();

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'
";
$row = $DB->fetch_assoc($query);
if($row['mt_idx']) {
    $query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_nickname = '".$decoded_array['mt_nickname']."' and mt_id != '".$decoded_array['mt_id']."'
    ";
    $chk_nickname = $DB->fetch_query($query);

    if($chk_nickname['mt_idx']) {
        $arr['chk_nickname'] = false;
        echo result_data('false', '이미 사용중인 닉네임입니다.', $arr);
    } else {
        $arr['chk_nickname'] = true;
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 닉네임 중복체크', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '닉네임 중복체크', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>