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
if($decoded_array['rt_category']=="") {
    echo result_data('false', '잘못된 접근입니다. rt_category', '');
    exit;
}
if($decoded_array['rt_content']=="") {
    echo result_data('false', '잘못된 접근입니다. rt_content', '');
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
        $query = "select * from report_t where rt_type = 2 and rt_table = 'chat_t' and reporter_idx = ".$row['mt_idx']." and report_idx = ".$list['idx'];
        $count = $DB->count_query($query);
        if($count > 0) {
            echo result_data("false", "이미 신고 처리한 채팅입니다.", "");
            exit;
        } else {
            $arr = array(
                "rt_type" => 2,
                "rt_status" => 1,
                "rt_category" => $decoded_array['rt_category'],
                "reporter_idx" => $row['mt_idx'],
                "report_idx" => $list['idx'],
                "rt_content" => $decoded_array['rt_content'],
                "rt_table" => "chat_t",
                "rt_wdate" => "now()",
            );

            $DB->insert_query("report_t", $arr);
        }

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 채팅 신고', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '채팅 신고', $jwt);
        }
    } else {
        echo result_data("false", "채팅방이 존재하지 않습니다.", "");
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>