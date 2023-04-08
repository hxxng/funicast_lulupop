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
if($decoded_array['ct_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_idx', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select * from community_t where ct_status = 1 and idx = ".$decoded_array['ct_idx'];
    $row_c = $DB->fetch_assoc($query);
    if($row_c['idx'] > 0) {
        $query = "select * from like_community_t where mt_idx = ".$row['mt_idx']." and ct_idx = ".$decoded_array['ct_idx'];
        $like = $DB->fetch_assoc($query);
        $lct_status = "Y";
        if($like['idx'] > 0) { 
            if($like['lct_status'] == "Y") {
                $lct_status = "N";
            }
            $DB->update_query("like_community_t", array("lct_status" => $lct_status), " idx = ".$like['idx']);
        } else {
            $DB->insert_query("like_community_t", array("mt_idx" => $row['mt_idx'], "ct_idx" => $decoded_array['ct_idx'], "lct_status" => "Y", "lct_wdate"=> "now()"));
        }

        if($lct_status == "Y") {
            $query = "select * from member_t where idx = " . $row_c['mt_idx'];
            $row_m = $DB->fetch_assoc($query);
            if ($row_m['mt_pushing'] == "Y" || $row_m['mt_pushing3'] == "Y") {
                $chk = "Y";
            } else {
                $chk = "N";
            }
            $token_list = array($row_m['mt_fcm']);
            $message = $row['mt_nickname']."이(가) 좋아요를 남겼습니다.";
            $title = "룰루팝 커뮤니티";

            $op_idx = $row_m['idx'];

            send_notification2($token_list, $title, $message, "Community_Detail_Page", $row_c['idx'], $chk);

            unset($arr_query);
            $plt_set = array(
                'plt_title' => $title,
                'plt_content' => $message,
                'plt_table' => "community_t",
                'plt_type' => 3,
                'plt_index' => $row_c['idx'],
                'mt_idx' => $row['mt_idx'],
                'op_idx' => $op_idx,
                'plt_wdate' => 'now()'
            );
            $DB->insert_query("pushnotification_log_t", $plt_set);
        }

        $arr = $lct_status;
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 게시글 좋아요', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '게시글 좋아요', $jwt);
        }
    } else {
        echo result_data("false", "해당 게시글이 존재하지않습니다.", "");
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>