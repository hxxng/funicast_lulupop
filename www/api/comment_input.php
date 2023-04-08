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
if($decoded_array['ct_content']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_content', '');
    exit;
}
if($decoded_array['ct_table']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_table', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    if($decoded_array['ct_table'] == "community_t") {
        $status = "ct_status";
    } else {
        $status = "tt_status";
    }
    $query = "select * from ".$decoded_array['ct_table']." where idx = ".$decoded_array['ct_idx']." and ".$status." = 1";
    $count = $DB->count_query($query);
    if($count > 0) {
        $arr = array(
            "ct_table" => $decoded_array['ct_table'],
            "ct_idx" => $decoded_array['ct_idx'],
            "mt_idx" => $row['mt_idx'],
            "ct_content" => $decoded_array['ct_content'],
            "ct_mt_idx" => $decoded_array['mt_idx'],
            "ct_status" => 1,
            "ct_wdate" => "now()",
        );
        if($decoded_array['ct_table'] == "community_t") {
            $page = "Community_Detail_Page";
        } else {
            $page = "UsedTrade_Detail_Comment_Page";
        }
        if($decoded_array['ct_parent_idx'] != "") {
            $query = "select * from comment_t where idx = ".$decoded_array['ct_parent_idx'];
            $count = $DB->count_query($query);
            $comment = $DB->fetch_assoc($query);
            if($count > 0) {
                $arr['ct_parent_idx'] = $decoded_array['ct_parent_idx'];

                if($row['mt_idx'] != $comment['mt_idx']) {
                    if($comment) {
                        $query = "select * from member_t where idx = ".$comment['mt_idx'];
                        $member = $DB->fetch_assoc($query);
                        if($member) {
                            $token_list = array($member['mt_fcm']);
                            $op_idx = $member['idx'];
                            if($member['mt_pushing'] == "Y" ||$member['mt_pushing3'] == "Y") {
                                $chk = "Y";
                            } else {
                                $chk = "N";
                            }
                        }

                        $message = $row['mt_nickname']."이(가) ".$comment['ct_content']." 댓글에 답글을 남겼습니다.";
                        $idx = $decoded_array['ct_idx'];
                    }
                    $title = "룰루팝";

                    send_notification2($token_list, $title, $message, $page, $idx, $chk);

                    unset($arr_query);
                    $plt_set = array(
                        'plt_title'=>$title,
                        'plt_content'=>$message,
                        'plt_table'=>$decoded_array['ct_table'],
                        'plt_type'=> 3,
                        'plt_index'=>$idx,
                        'mt_idx'=>1,
                        'op_idx'=>$op_idx,
                        'plt_wdate'=>'now()'
                    );
                    $DB->insert_query("pushnotification_log_t", $plt_set);
                }
            } else {
                echo result_data("false", "댓글의 답글을 달 수 없습니다.", "");
                exit;
            }
        }
        $DB->insert_query("comment_t", $arr);

        if($decoded_array['ct_table'] == "community_t") {
            $query = "select * from community_t where idx = ".$decoded_array['ct_idx'];
            $community = $DB->fetch_assoc($query);
            if($community) {
                $query = "select * from member_t where idx = ".$community['mt_idx'];
                $member = $DB->fetch_assoc($query);
                if($member) {
                    $token_list = array($member['mt_fcm']);
                    $op_idx = $member['idx'];
                }

                $message = $row['mt_nickname']."이(가) 댓글을 남겼습니다.";
                $idx = $community['idx'];
            }
            $title = "룰루팝 커뮤니티";
            if($member['mt_pushing'] == "Y" || $member['mt_pushing3'] == "Y") {
                $chk = "Y";
            } else {
                $chk = "N";
            }
            if($row['mt_idx'] != $community['mt_idx']) {
                send_notification2($token_list, $title, $message, $page, $idx, $chk);

                unset($arr_query);
                $plt_set = array(
                    'plt_title'=>$title,
                    'plt_content'=>$message,
                    'plt_table'=>$decoded_array['ct_table'],
                    'plt_type'=> 3,
                    'plt_index'=>$decoded_array['ct_idx'],
                    'mt_idx'=>$row['mt_idx'],
                    'op_idx'=>$op_idx,
                    'plt_wdate'=>'now()'
                );
                $DB->insert_query("pushnotification_log_t", $plt_set);
            }
        } else {
            $query = "select * from trade_t where idx = ".$decoded_array['ct_idx'];
            $trade = $DB->fetch_assoc($query);
            if($trade) {
                $query = "select * from member_t where idx = ".$trade['mt_idx'];
                $member = $DB->fetch_assoc($query);
                if($member) {
                    $token_list = array($member['mt_fcm']);
                    $op_idx = $member['idx'];
                }

                $message = $trade['tt_title']." 거래 게시물에 댓글이 달렸습니다.";
                $idx = $trade['idx'];
            }
            $title = "룰루팝 중고거래";
            if($member['mt_pushing'] == "Y" || $member['mt_pushing3'] == "Y") {
                $chk = "Y";
            } else {
                $chk = "N";
            }
            if($row['mt_idx'] != $trade['mt_idx']) {
                send_notification2($token_list, $title, $message, $page, $idx, $chk);

                unset($arr_query);
                $plt_set = array(
                    'plt_title'=>$title,
                    'plt_content'=>$message,
                    'plt_table'=>$decoded_array['ct_table'],
                    'plt_type'=> 3,
                    'plt_index'=>$decoded_array['ct_idx'],
                    'mt_idx'=>$row['mt_idx'],
                    'op_idx'=>$op_idx,
                    'plt_wdate'=>'now()'
                );
                $DB->insert_query("pushnotification_log_t", $plt_set);
            }
        }

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 댓글 달기', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '댓글 달기', $jwt);
        }
    } else {
        echo result_data("false", "게시글이 존재하지 않습니다.", "");
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>