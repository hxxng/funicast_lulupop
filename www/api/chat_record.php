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
    $query = "select * from chat_t where (mt_idx = ".$row['mt_idx']." and ct_mt_idx = ".$row2['mt_idx'].") 
    or (mt_idx = ".$row2['mt_idx']." and ct_mt_idx = ".$row['mt_idx'].") 
    and (instr(ct_out_idx, ".$row['mt_idx'].") = 0 or instr(ct_out_idx, ".$row['mt_idx'].") is null)";
    $chat = $DB->fetch_assoc($query);

    if($chat['idx']) {
        $query = "select * from chat_detail_t where ct_idx = ".$chat['idx']." and (instr(cdt_out_idx, ".$row['mt_idx'].") = 0 or instr(cdt_out_idx, ".$row['mt_idx'].") is null) order by cdt_wdate desc";
        $list = $DB->select_query($query);

        if($list) {
            foreach ($list as $row_dt) {
                $query = "select count(*) as cnt, reporter_idx from report_t where rt_table = 'chat_t' and report_idx = ".$chat['idx']." and rt_status = 1 and reporter_idx = ".$row['mt_idx'];
                $count_report = $DB->fetch_assoc($query);
                if($count_report['cnt'] > 0) {
                    if($row['mt_idx'] == $count_report['reporter_idx']) {
                        echo result_data("false", "신고된 채팅입니다.","");
                        exit;
                    }
                }
                $query = "select * from report_t where rt_table = 'chat_t' and report_idx = ".$chat['idx']." and rt_status = 2";
                $count_report2 = $DB->count_query($query);
                if($count_report2 > 0) {
                    echo result_data("false", "신고 처리된 채팅입니다.","");
                    exit;
                }
                if($row_dt['cdt_sender_idx'] == $row['mt_idx']) {
                    $mine = "Y";
                } else {
                    $mine = "N";
                }
                $strDate = substr($row_dt['cdt_wdate'],0,10)."(".substr($row_dt['cdt_wdate'],11,5).")";

                if($row2['mt_image']) {
                    $mt_image = $ct_img_url."/".$row2['mt_image']."?cache=".strtotime($row2['mt_udate']);
                } else {
                    $mt_image = null;
                }

                $arr['chat'][] = array(
                    "cdt_idx" => $row_dt['idx'],
                    "ct_idx" => $row_dt['ct_idx'],
                    "cdt_sender_idx" => $row_dt['cdt_sender_idx'],
                    "cdt_recipient_idx" => $row_dt['cdt_recipient_idx'],
                    "cdt_content" => $row_dt['cdt_content'],
                    "cdt_wdate" => $strDate,
                    "mine" => $mine,
                    "mt_image" => $mt_image,
                );

                $DB->update_query("chat_detail_t", array("cdt_read" => "Y"), " ct_idx = ".$row_dt['ct_idx']." and cdt_recipient_idx = ".$row['mt_idx']);
            }
            $query = "select * from hide_t where ht_table = 'chat_t' and mt_idx = ".$row['mt_idx']." and ht_hide_idx = ".$chat['idx'];
            $count = $DB->count_query($query);
            if($count > 0) {
                $hide_yn = "Y";
            } else {
                $hide_yn = "N";
            }

            $arr['mt_nickname'] = $row2['mt_nickname'];
            $arr['hide_yn'] = $hide_yn;

            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 채팅 기록', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '채팅 기록', $jwt);
            }
        } else {
            $arr['mt_nickname'] = $row2['mt_nickname'];
            $arr['chat'] = [];
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 채팅 기록이 없습니다.', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '채팅 기록이 없습니다.', $jwt);
            }
        }
    } else {
        $arr['mt_nickname'] = $row2['mt_nickname'];
        $arr['chat'] = [];
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 존재하지 않는 채팅입니다.', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '존재하지 않는 채팅입니다.', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>