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

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select *, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname, (SELECT mt_image FROM member_t WHERE idx=mt_idx) as mt_image, 
       (SELECT mt_id FROM member_t WHERE idx=mt_idx) as mt_id, (SELECT mt_udate FROM member_t WHERE idx=mt_idx) as mt_udate 
        from trade_t where tt_status = 1 and idx = ".$decoded_array['tt_idx'];
    $row_c = $DB->fetch_assoc($query);
    if($row_c['idx'] > 0) {
        $query = "select count(*) as cnt, reporter_idx from report_t where rt_table = 'trade_t' and report_idx = ".$decoded_array['tt_idx']." and rt_status = 1 and reporter_idx = ".$row['mt_idx'];
        $count_report = $DB->fetch_assoc($query);
        if($count_report['cnt'] > 0) {
            if($row['mt_idx'] == $count_report['reporter_idx']) {
                echo result_data("false", "신고된 게시글입니다.","");
                exit;
            }
        }
        $query = "select * from report_t where rt_table = 'trade_t' and report_idx = ".$decoded_array['tt_idx']." and rt_status = 2";
        $count_report2 = $DB->count_query($query);
        if($count_report2 > 0) {
            echo result_data("false", "신고 처리된 게시글입니다.","");
            exit;
        }

        $hashtag = explode(" ", $row_c['tt_hashtag']);

        for($i=1; $i<=10; $i++) {
            if($row_c['tt_img'.$i]) {
                $img[] = $ct_img_url."/".$row_c['tt_img'.$i]."?cache=".strtotime($row_c['tt_udate']);
            }
        }

        $query = "select * from hide_t where ht_hide_idx = ".$decoded_array['tt_idx']." and mt_idx = ".$row['mt_idx']." and ht_table = 'trade_t'";
        $hide = $DB->fetch_assoc($query);
        if($hide['idx'] > 0) {
            $hide_status = "Y";
        } else {
            $hide_status = "N";
        }

        $i = 0;
        $query = "select comment_t.*, rt_status from comment_t left join member_t on member_t.idx = comment_t.mt_idx and mt_level = 3 
                left join report_t on report_t.report_idx = comment_t.idx and rt_table = 'comment_t'
                where ct_table = 'trade_t' and ct_idx = ".$decoded_array['tt_idx']." and ct_parent_idx is null and mt_id is not null group by comment_t.idx";
        $list = $DB->select_query($query);
        if($list) {
            foreach ($list as $row) {
                if($row['rt_status'] > 1) {
                    $query = "select comment_t.*, rt_status from comment_t left join member_t on member_t.idx = comment_t.mt_idx and mt_level = 3 
                            left join report_t on report_t.report_idx = comment_t.idx and rt_table = 'comment_t'
                            where ct_table = 'trade_t' and ct_idx = ".$decoded_array['tt_idx']." and (rt_status not in (2,3) or rt_status is null) and ct_parent_idx = ".$row['idx']." and mt_id is not null";
                    $comment = $DB->count_query($query);
                    if($comment > 0) {
                        $i++;
                    }
                } else {
                    $query = "select comment_t.*, rt_status from  comment_t left join member_t on member_t.idx = comment_t.mt_idx and mt_level = 3 
                            left join report_t on report_t.report_idx = comment_t.idx and rt_table = 'comment_t'
                            where ct_table = 'trade_t' and ct_idx = ".$decoded_array['tt_idx']." and ct_parent_idx = ".$row['idx']." and mt_id is not null";
                    $comment = $DB->count_query($query);
                    $i+=$comment;
                }
                $i++;
            }
        }

        $comment_cnt = $i;

        $query = "select * from product_category_t where idx = ".$row_c['tt_cate_idx'];
        $pc_name = $DB->fetch_assoc($query);

        if($row_c['tt_sale_status'] == 1) {
            $tt_sale_status = "판매중";
        } else {
            $tt_sale_status = "판매완료";
        }
        if($row_c['tt_product_status'] == 1) {
            $tt_product_status = "새상품";
        } else {
            $tt_product_status = "중고상품";
        }
        if($row_c['tt_exchange'] == "Y") {
            $tt_exchange = "교환가능";
        } else {
            $tt_exchange = "교환불가";
        }

        $arr = array(
            "tt_idx" => $row_c['idx'],
            "tt_cate_idx" => $row_c['tt_cate_idx'],
            "tt_cate_name" => $pc_name['pc_name'],
            "tt_title" => $row_c['tt_title'],
            "tt_content" => $row_c['tt_content'],
            "tt_amount" => (int)$row_c['tt_amount'],
            "tt_price" => (int)$row_c['tt_price'],
            "tt_sale_status" => $tt_sale_status,
            "tt_product_status" => $tt_product_status,
            "tt_exchange" => $tt_exchange,
            "mt_nickname" => $row_c['mt_nickname'],
            "mt_idx" => $row_c['mt_idx'],
            "mt_id" => $row_c['mt_id'],
            'mt_image' => ($row_c['mt_image'] ? $ct_img_url . '/' . $row_c['mt_image']."?cache=".strtotime($row_c['mt_udate']) : $ct_member_no_img_url),
            "ct_hashtag" => $hashtag,
            "ct_img" => $img,
            "hide_yn" => $hide_status,
            "comment_cnt" => (int)$comment_cnt,
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