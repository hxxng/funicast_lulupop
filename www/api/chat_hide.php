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
if($decoded_array['op_id']=="") {
    echo result_data('false', '잘못된 접근입니다. op_id', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['op_id']."'";
$row2 = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select * from chat_t where (mt_idx = ".$row['mt_idx']." or ct_mt_idx = ".$row['mt_idx'].") && (mt_idx = ".$row2['mt_idx']." or ct_mt_idx = ".$row2['mt_idx'].")";
    $list = $DB->fetch_assoc($query);
    if($list['idx'] > 0) {
        $query = "select * from hide_t where ht_hide_idx = ".$list['idx']." and ht_table = 'chat_t' and mt_idx = ".$row['mt_idx'];
        $count = $DB->count_query($query);
        if($count > 0) {
            $DB->del_query("hide_t", " ht_table = 'chat_t' and mt_idx = ".$row['mt_idx']." and ht_hide_idx = ".$list['idx']);
            $arr = $row;

            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 채팅방 숨김 취소', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '채팅방 숨김 취소', $jwt);
            }
        } else {
            $DB->insert_query("hide_t", array("ht_table" => "chat_t", "mt_idx" => $row['mt_idx'], "ht_hide_idx" => $list['idx'], "ht_wdate" => "now()"));
            $arr = $row;

            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 채팅방 숨김', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '채팅방 숨김', $jwt);
            }
        }
    } else {
        echo result_data("false", "채팅방이 존재하지 않습니다.", "");
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>