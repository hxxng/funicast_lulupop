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
if($decoded_array['cdt_content']=="") {
    echo result_data('false', '잘못된 접근입니다. cdt_content', '');
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
    $query = "select * from chat_t where (mt_idx = ".$row['mt_idx']." and ct_mt_idx = ".$row2['mt_idx'].") or (ct_mt_idx = ".$row['mt_idx']." and mt_idx = ".$row2['mt_idx'].")";
    $list = $DB->fetch_assoc($query);
    if($list['idx'] > 0) {
        $ct_idx = $list['idx'];
    } else {
        $DB->insert_query("chat_t", array("mt_idx" => $row['mt_idx'], "ct_mt_idx" => $row2['mt_idx'], "ct_wdate" => "now()"));
        $ct_idx = $DB->insert_id();
    }

    $arr = array(
        "ct_idx" => $ct_idx,
        "cdt_sender_idx" => $row['mt_idx'],
        "cdt_recipient_idx" => $row2['mt_idx'],
        "cdt_content" => $decoded_array['cdt_content'],
        "cdt_wdate" => "now()",
    );
    $DB->insert_query("chat_detail_t", $arr);
    $DB->update_query("chat_t", array("ct_ldate" => "now()", "ct_out_idx" => null), " idx = ".$ct_idx);

    if($row2['mt_pushing'] == "Y" || $row2['mt_pushing3'] == "Y") {
        $chk = "Y";
    } else {
        $chk = "N";
    }
    $token_list = array($row2['mt_fcm']);
    $message = $row['mt_nickname']."이(가) dm를 보내었습니다.";
    $title = "룰루팝 채팅";

    $message_status = send_notification2($token_list, $title, $message, "Chatting_Page", $row['mt_id'], $chk);

    if ($message_status) {
        unset($arr_query);
        $plt_set = array(
            'plt_title'=>$title,
            'plt_content'=>$message,
            'plt_table'=>"chat_t",
            'plt_type'=> 3,
            'plt_index'=>$row['mt_id'],
            'mt_idx'=>$row['mt_idx'],
            'op_idx'=>$row2['mt_idx'],
            'plt_wdate'=>'now()'
        );
        $DB->insert_query("pushnotification_log_t", $plt_set);
    }

    $query = "select * from chat_detail_t where (cdt_sender_idx = ".$row['mt_idx']." and cdt_recipient_idx = ".$row2['mt_idx'].") or (cdt_recipient_idx = ".$row['mt_idx']." and cdt_sender_idx = ".$row2['mt_idx'].") order by cdt_wdate desc";
    $list = $DB->select_query($query);

    if($list) {
        foreach ($list as $row_dt) {
            $strDate = substr($row_dt['cdt_wdate'],0,10)."(".substr($row_dt['cdt_wdate'],11,5).")";

            $arr['chat'][] = array(
                "cdt_idx" => $row_dt['idx'],
                "ct_idx" => $row_dt['ct_idx'],
                "cdt_sender_idx" => $row_dt['cdt_sender_idx'],
                "cdt_recipient_idx" => $row_dt['cdt_recipient_idx'],
                "cdt_content" => $row_dt['cdt_content'],
                "cdt_wdate" => $strDate,
            );
        }
    }
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 채팅 보내기', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '채팅 보내기', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>