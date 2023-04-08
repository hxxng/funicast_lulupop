<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['ft_id']=="") {
    echo result_data('false', '잘못된 접근입니다. ft_id', '');
    exit;
}
if($decoded_array['mt_id']=="") {
    echo result_data('false', '잘못된 접근입니다. mt_id', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['ft_id']."'";
$row2 = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select * from follow_t where mt_idx = ".$row['mt_idx']." and ft_mt_idx = ".$row2['mt_idx'];
    $list = $DB->fetch_assoc($query);
    if($list['idx'] > 0) {
        if($list['ft_hide'] == "N") {
            $DB->update_query("follow_t",  array("ft_hide" => "Y"), " idx = ".$list['idx']);
            $arr = "Y";
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 팔로우 숨김', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '팔로우 숨김', $jwt);
            }
        } else {
            $DB->update_query("follow_t",  array("ft_hide" => "N"), " idx = ".$list['idx']);

            $arr = "N";
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 팔로우 숨김 취소', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '팔로우 숨김 취소', $jwt);
            }
        }
    } else {
        echo result_data("false", "팔로우 상태가 아닙니다.", "");
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>