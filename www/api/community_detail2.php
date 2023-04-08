<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

if($decoded_array['ct_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. ct_idx', '');
    exit;
}

$query = "select *, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname, (SELECT mt_image FROM member_t WHERE idx=mt_idx) as mt_image,
       (SELECT mt_udate FROM member_t WHERE idx=mt_idx) as mt_udate 
        from community_t where ct_status = 1 and idx = ".$decoded_array['ct_idx'];
$row_c = $DB->fetch_assoc($query);
if($row_c['idx'] > 0) {
    if($row_c['ct_hashtag']) {
        $hashtag = explode(" ", $row_c['ct_hashtag']);
    }

    for($i=1; $i<=10; $i++) {
        if($row_c['ct_img'.$i]) {
            $img[] = $ct_img_url."/".$row_c['ct_img'.$i]."?cache=".strtotime($row_c['ct_udate']);
        }
    }

    $query = "select * from product_category_t where idx = ".$row_c['ct_cate_idx'];
    $pc_name = $DB->fetch_assoc($query);

    $comment2_cnt = 0;
    $query = "select comment_t.*, rt_status, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname, (SELECT mt_image FROM member_t WHERE idx=mt_idx) as mt_image from comment_t 
                left join report_t on report_t.report_idx = comment_t.idx and rt_table = 'comment_t'
                where ct_table = 'community_t' and ct_idx = ".$decoded_array['ct_idx']." and ct_parent_idx is null
                order by IF(ISNULL(ct_parent_idx), comment_t.idx, ct_parent_idx) ";
    $comment_list = $DB->select_query($query);
    $comment_cnt = $DB->fetch_query("SELECT COUNT(idx) AS cnt FROM (select comment_t.idx
                                from comment_t 
                                left join report_t on report_t.report_idx = comment_t.idx and rt_table = 'comment_t'
                                where ct_table = 'community_t' and ct_idx = ".$decoded_array['ct_idx']." and ct_parent_idx is null group by comment_t.idx 
                                ) A ");
    if($comment_list) {
        foreach ($comment_list as $row_cc) {
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
                        }
                    }
                }
            }
            $chk = "Y";
            $query = "select count(*) as cnt, reporter_idx, rt_status from report_t where rt_table = 'comment_t' and report_idx = ".$row_cc['idx'];
            $count_report_c = $DB->fetch_assoc($query);
            if($count_report_c['cnt'] > 0) {
                if ($count_report_c['rt_status'] == 2) {
                    if ($count > 0) {
                        $ct_content = "신고처리된 댓글입니다.";
                    } else {
                        $chk = "N";
                        $comment_cnt[0] -= 1;
                    }
                } else if ($count_report_c['rt_status'] == 3) {
                    if ($count > 0) {
                        $ct_content = "관리자에 의해 삭제된 댓글입니다.";
                    } else {
                        $chk = "N";
                        $comment_cnt[0] -= 1;
                    }
                }
            }
        }
    } else {
        $comment['list'] = [];
    }

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
        'mt_image' => ($row_c['mt_image'] ? $ct_img_url . '/' . $row_c['mt_image']."?cache=".strtotime($row_c['mt_udate']) : $ct_member_no_img_url),
        "ct_hashtag" => $hashtag,
        "ct_img" => $img,
    );

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 게시글 정보', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '게시글 정보', $jwt);
    }
} else {
    echo result_data("false", "해당 게시글이 존재하지않습니다.", "");
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>