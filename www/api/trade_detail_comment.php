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
if($decoded_array['tt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. tt_idx', '');
    exit;
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select comment_t.*, rt_status, mt_nickname, mt_image 
                from comment_t 
                left join member_t on member_t.idx = comment_t.mt_idx and mt_level = 3 
                left join report_t on report_t.report_idx = comment_t.idx and rt_table = 'comment_t'
                where ct_table = 'trade_t' and ct_idx = ".$decoded_array['tt_idx']." and ct_parent_idx is null and mt_id is not null
                order by IF(ISNULL(ct_parent_idx), comment_t.idx, ct_parent_idx) ";
    $comment_list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
    $comment_cnt = $DB->fetch_query("SELECT COUNT(idx) AS cnt FROM (select comment_t.idx
                                from comment_t 
                                left join member_t on member_t.idx = comment_t.mt_idx and mt_level = 3 
                                left join report_t on report_t.report_idx = comment_t.idx and rt_table = 'comment_t'
                                where ct_table = 'trade_t' and ct_idx = ".$decoded_array['tt_idx']." and ct_parent_idx is null and mt_id is not null group by comment_t.idx 
                                ) A ");
    if($comment_list) {
        foreach ($comment_list as $row_cc) {
            $comment2 = [];
            $query = "select comment_t.*, mt_nickname, mt_image, mt_udate from comment_t left join member_t on member_t.idx = comment_t.mt_idx and mt_level = 3 
                    where ct_parent_idx=".$row_cc['idx']." and mt_id is not null";
            $count = $DB->count_query($query);
            $comment_list2 = $DB->select_query($query);
            if($comment_list2) {
                foreach ($comment_list2 as $row_cc2) {
                    $chk = "Y";
                    $query = "select count(*) as cnt, reporter_idx, rt_status from report_t where rt_table = 'comment_t' and report_idx = ".$row_cc2['idx'];
                    $count_report_c2 = $DB->fetch_assoc($query);
                    if($count_report_c2['cnt'] > 0) {
                        if($count_report_c2['rt_status'] == 2 || $count_report_c2['rt_status'] == 3) {
                            continue;
                        } else {
                            if($row['mt_idx'] == $count_report_c2['reporter_idx']) {
                                $ct_content2 = "신고된 댓글입니다.";
                            } else {
                                $ct_content2 = $row_cc2['ct_content'];
                            }
                        }
                    } else {
                        $ct_content2 = $row_cc2['ct_content'];
                    }
                    $query = "select * from like_comment_t where ct_idx = ".$row_cc2['idx']." and lct_status = 'Y'";
                    $like_comment = $DB->fetch_assoc($query);
                    if($like_comment['idx'] > 0) {
                        $like_yn2 = "Y";
                    } else {
                        $like_yn2 = "N";
                    }
                    $comment2[] = array(
                        "cmt_idx" => $row_cc2['idx'],
                        "ct_parent_idx" => $row_cc2['ct_parent_idx'],
                        "ct_content" => $ct_content2,
                        "ct_like" => (int)$row_cc2['ct_like'],
                        "mt_nickname" => $row_cc2['mt_nickname'],
                        "mt_idx" => $row_cc2['mt_idx'],
                        'mt_image' => ($row_cc2['mt_image'] ? $ct_img_url . '/' . $row_cc2['mt_image']."?cache=".strtotime($row_cc2['mt_udate']) : $ct_member_no_img_url),
                        "like_yn" => $like_yn2,
                    );
                }
            } else {
                $comment2 = [];
            }
            $chk = "Y";
            $query = "select count(*) as cnt, reporter_idx, rt_status from report_t where rt_table = 'comment_t' and report_idx = ".$row_cc['idx'];
            $count_report_c = $DB->fetch_assoc($query);
            if($count_report_c['cnt'] > 0) {
                if($count_report_c['rt_status'] == 2) {
                    if($count > 0) {
                        $ct_content = "신고처리된 댓글입니다.";
                    } else {
                        $chk = "N";
                    }
                } else if($count_report_c['rt_status'] == 3) {
                    if($count > 0) {
                        $ct_content = "관리자에 의해 삭제된 댓글입니다.";
                    } else {
                        $chk = "N";
                    }
                } else {
                    if($row['mt_idx'] == $count_report_c['reporter_idx']) {
                        $ct_content = "신고된 댓글입니다.";
                    } else {
                        $ct_content = $row_cc['ct_content'];
                    }
                }
            } else {
                $ct_content = $row_cc['ct_content'];
            }

            if($chk == "Y") {
                $comment[] = array(
                    "cmt_idx" => $row_cc['idx'],
                    "ct_parent_idx" => $row_cc['ct_parent_idx'],
                    "ct_content" => $ct_content,
                    "ct_like" => (int)$row_cc['ct_like'],
                    "mt_nickname" => $row_cc['mt_nickname'],
                    "mt_idx" => $row_cc['mt_idx'],
                    'mt_image' => ($row_cc['mt_image'] ? $ct_img_url . '/' . $row_cc['mt_image']."?cache=".strtotime($row_cc['mt_udate']) : $ct_member_no_img_url),
                    'comment2_count' => (int)$count,
                    "comment2" => $comment2,
                );
            } else {
                $comment['list'] = [];
            }
        }

        $arr['list'] = $comment;

        $arr['count'] = (int)$comment_cnt[0];
        $n_page = ceil($comment_cnt[0] / 20);
        if($n_page == 0) {
            $arr['maxpage'] = 1;
        } else {
            $arr['maxpage'] = (int)$n_page;
        }

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 게시글 댓글리스트', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '게시글 댓글리스트', $jwt);
        }
    } else {
        $arr['list'] = [];

        $arr['count'] = (int)$comment_cnt[0];
        $n_page = ceil($comment_cnt[0] / 20);
        if($n_page == 0) {
            $arr['maxpage'] = 1;
        } else {
            $arr['maxpage'] = (int)$n_page;
        }

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 게시글 댓글리스트', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '게시글 댓글리스트', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>