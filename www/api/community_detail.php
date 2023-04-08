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

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select *, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname, (SELECT mt_image FROM member_t WHERE idx=mt_idx) as mt_image, 
       (SELECT mt_id FROM member_t WHERE idx=mt_idx) as mt_id, (SELECT mt_udate FROM member_t WHERE idx=mt_idx) as mt_udate 
        from community_t where ct_status = 1 and idx = ".$decoded_array['ct_idx'];
    $row_c = $DB->fetch_assoc($query);
    if($row_c['idx'] > 0) {
        $view_cnt = $DB->count_query("select * from community_view_log_t where mt_idx = ".$row['mt_idx']." and ct_idx = ".$row_c['idx']);
        if($view_cnt < 1) {
            $DB->insert_query('community_view_log_t', array("ct_idx" => $row_c['idx'], "mt_idx" => $row['mt_idx'], "cvlt_wdate" => "now()"));
        }
        $query = "select count(*) as cnt, reporter_idx from report_t where rt_table = 'community_t' and report_idx = ".$decoded_array['ct_idx']." and rt_status = 1 and reporter_idx = ".$row['mt_idx'];
        $count_report = $DB->fetch_assoc($query);
        if($count_report['cnt'] > 0) {
            if($row['mt_idx'] == $count_report['reporter_idx']) {
                echo result_data("false", "신고된 게시글입니다.","");
                exit;
            }
        }
        $query = "select * from report_t where rt_table = 'community_t' and report_idx = ".$decoded_array['ct_idx']." and rt_status = 2";
        $count_report2 = $DB->count_query($query);
        if($count_report2 > 0) {
            echo result_data("false", "신고 처리된 게시글입니다.","");
            exit;
        }

        $hashtag = explode(" ", $row_c['ct_hashtag']);

        for($i=1; $i<=10; $i++) {
            if($row_c['ct_img'.$i]) {
                $img[] = $ct_img_url."/".$row_c['ct_img'.$i]."?cache=".strtotime($row_c['ct_udate']);
            }
        }

        $query = "select * from follow_t where mt_idx = ".$row['mt_idx']." and ft_mt_idx = ".$row_c['mt_idx'];
        $follow = $DB->fetch_assoc($query);
        if($follow['idx'] > 0) {
            $follow_status = "Y";
        } else {
            $follow_status = "N";
        }

        $query = "select * from like_community_t where ct_idx = ".$decoded_array['ct_idx']." and mt_idx = ".$row['mt_idx'];
        $like = $DB->fetch_assoc($query);
        if($like['idx'] > 0) {
            $lct_status = $like['lct_status'];
        } else {
            $lct_status = "N";
        }

        $query = "select * from hide_t where ht_hide_idx = ".$decoded_array['ct_idx']." and mt_idx = ".$row['mt_idx']." and ht_table = 'community_t'";
        $hide = $DB->fetch_assoc($query);
        if($hide['idx'] > 0) {
            $hide_status = "Y";
        } else {
            $hide_status = "N";
        }

        if($row_c['ct_comment_chk'] == "Y") {
            $comment2_cnt = 0;
            $query = "select comment_t.*, rt_status, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname, (SELECT mt_image FROM member_t WHERE idx=mt_idx) as mt_image from comment_t 
                left join report_t on report_t.report_idx = comment_t.idx and rt_table = 'comment_t'
                where ct_table = 'community_t' and ct_idx = ".$decoded_array['ct_idx']." and ct_parent_idx is null
                order by IF(ISNULL(ct_parent_idx), comment_t.idx, ct_parent_idx) ";
            $comment_list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
            $comment_cnt = $DB->fetch_query("SELECT COUNT(idx) AS cnt FROM (select comment_t.idx
                                from comment_t 
                                left join report_t on report_t.report_idx = comment_t.idx and rt_table = 'comment_t'
                                where ct_table = 'community_t' and ct_idx = ".$decoded_array['ct_idx']." and ct_parent_idx is null group by comment_t.idx 
                                ) A ");
            if($comment_list) {
                foreach ($comment_list as $row_cc) {
                    $query = "select * from like_comment_t where ct_idx = ".$row_cc['idx']." and mt_idx = ".$row['mt_idx']." and lct_status = 'Y'";
                    $like_comment = $DB->fetch_assoc($query);
                    if($like_comment['idx'] > 0) {
                        $like_yn = "Y";
                    } else {
                        $like_yn = "N";
                    }
                    $comment2 = [];
                    $query = "select *, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname, (SELECT mt_image FROM member_t WHERE idx=mt_idx) as mt_image, 
                        (SELECT mt_udate FROM member_t WHERE idx=mt_idx) as mt_udate from comment_t where ct_table = 'community_t' and ct_parent_idx=".$row_cc['idx'];
                    $count = $DB->count_query($query);
                    $comment_list2 = $DB->select_query($query);
                    if($comment_list2) {
                        $comment2_cnt += $count;
                        foreach ($comment_list2 as $row_cc2) {
                            $chk = "Y";
                            $query = "select count(*) as cnt, reporter_idx, rt_status from report_t where rt_table = 'comment_t' and report_idx = ".$row_cc2['idx'];
                            $count_report_c2 = $DB->fetch_assoc($query);
                            if($count_report_c2['cnt'] > 0) {
                                if($count_report_c2['rt_status'] == 2 || $count_report_c2['rt_status'] == 3) {
                                    $comment2_cnt -= $count;
                                    $count = 0;
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
                            $query = "select * from like_comment_t where ct_idx = ".$row_cc2['idx']." and mt_idx = ".$row['mt_idx']." and lct_status = 'Y'";
                            $like_comment2 = $DB->fetch_assoc($query);
                            if($like_comment2['idx'] > 0) {
                                $like_yn2 = "Y";
                            } else {
                                $like_yn2 = "N";
                            }
                            if($row_cc2['ct_mt_idx']) {
                                $query = "select * from member_t where idx = ".$row_cc2['ct_mt_idx'];
                                $ct_mt = $DB->fetch_assoc($query);
                                if($ct_mt) {
                                    $ct_mt_nickname = "@".$ct_mt['mt_nickname'];
                                }
                            } else {
                                $ct_mt_nickname = null;
                            }
                            $comment2[] = array(
                                "cmt_idx" => $row_cc2['idx'],
                                "ct_parent_idx" => $row_cc2['ct_parent_idx'],
                                "ct_content" => $ct_content2,
                                "ct_like" => (int)$row_cc2['ct_like'],
                                "mt_nickname" => $row_cc2['mt_nickname'],
                                "mt_idx" => $row_cc2['mt_idx'],
                                "ct_mt_nickname" => $ct_mt_nickname,
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
                                $comment_cnt[0] -= 1;
                            }
                        } else if($count_report_c['rt_status'] == 3) {
                            if($count > 0) {
                                $ct_content = "관리자에 의해 삭제된 댓글입니다.";
                            } else {
                                $chk = "N";
                                $comment_cnt[0] -= 1;
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
                        $comment['list'][] = array(
                            "cmt_idx" => $row_cc['idx'],
                            "ct_parent_idx" => $row_cc['ct_parent_idx'],
                            "ct_content" => $ct_content,
                            "ct_like" => (int)$row_cc['ct_like'],
                            "mt_nickname" => $row_cc['mt_nickname'],
                            "mt_idx" => $row_cc['mt_idx'],
                            'mt_image' => ($row_cc['mt_image'] ? $ct_img_url . '/' . $row_cc['mt_image']."?cache=".strtotime($row_cc['mt_udate']) : $ct_member_no_img_url),
                            "like_yn" => $like_yn,
                            'count' => (int)$count,
                            "comment2" => $comment2,
                        );
                    } else {
                        $comment['list'] = [];
                    }
                }
            } else {
                $comment['list'] = [];
            }
        } else {
            $comment['list'] = [];
        }
        $comment['count'] = (int)$comment_cnt[0];
        $n_page = ceil($comment_cnt[0] / 20);
        if($n_page == 0) {
            $comment['maxpage'] = 1;
        } else {
            $comment['maxpage'] = (int)$n_page;
        }

        $query = "select * from product_category_t where idx = ".$row_c['ct_cate_idx'];
        $pc_name = $DB->fetch_assoc($query);

        $arr = array(
            "ct_idx" => $row_c['idx'],
            "ct_cate_idx" => $row_c['ct_cate_idx'],
            "ct_cate_name" => $pc_name['pc_name'],
            "ct_title" => $row_c['ct_title'],
            "ct_content" => $row_c['ct_content'],
            "ct_like" => (int)$row_c['ct_like'],
            "ct_comment" => (int)$comment_cnt[0]+$comment2_cnt,
            "mt_nickname" => $row_c['mt_nickname'],
            "mt_idx" => $row_c['mt_idx'],
            "mt_id" => $row_c['mt_id'],
            'mt_image' => ($row_c['mt_image'] ? $ct_img_url . '/' . $row_c['mt_image']."?cache=".strtotime($row_c['mt_udate']) : $ct_member_no_img_url),
            "ct_hashtag" => $hashtag,
            "ct_img" => $img,
            "ct_like_yn" => $lct_status,
            "follow_yn" => $follow_status,
            "hide_yn" => $hide_status,
            "ct_comment_chk" => $row_c['ct_comment_chk'],
            "comment" => $comment,
        );

        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 게시글 상세보기', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '게시글 상세보기', $jwt);
        }
    } else {
        echo result_data("false", "해당 게시글이 존재하지않습니다.", "");
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>