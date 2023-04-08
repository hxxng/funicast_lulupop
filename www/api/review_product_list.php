<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}
if($decoded_array['pt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. pt_idx', '');
    exit;
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
		select *, a1.idx as mt_idx from member_t a1
		where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level = 3
	";
$row_m = $DB->fetch_query($query);

if($row_m['mt_idx']) {
    $where = " and review_product_t.idx not in (SELECT report_idx FROM report_t where reporter_idx=".$row_m['mt_idx']." and rt_table = 'review_product_t') ";
} else {
    $where = "";
}

$query = "select review_product_t.*, rt_status, reporter_idx, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname, (SELECT mt_image FROM member_t WHERE idx=mt_idx) as mt_image, 
       (SELECT idx FROM member_t WHERE idx=mt_idx) as mt_idx, (SELECT mt_udate FROM member_t WHERE idx=mt_idx) as mt_udate 
        from review_product_t 
        left join report_t on report_t.report_idx = review_product_t.idx and rt_table = 'review_product_t' 
        where pt_idx = ".$decoded_array['pt_idx'].$where." 
        group by review_product_t.idx order by rpt_wdate desc";
$list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));

$count_query = "SELECT COUNT(idx) AS cnt 
        FROM (select review_product_t.idx from review_product_t 
        left join report_t on report_t.report_idx = review_product_t.idx and rt_table = 'review_product_t'
        where pt_idx = ".$decoded_array['pt_idx'].$where." 
        group by review_product_t.idx ) A ";
$count = $DB->fetch_query($count_query);

$n_page = ceil($count[0] / 20);

if($list) {
    unset($arr);
    foreach ($list as $row) {
        if($row['rt_status'] != 2 || $row['rt_status'] == null) {
            if($row_m['mt_idx'] > 0 && $row_m['mt_idx'] == $row['reporter_idx']) {
                $arr['review_product'][] = array(
                    "rpt_idx" => $row['idx'],
                    "mt_idx" => $row['mt_idx'],
                    "mt_nickname" => $row['mt_nickname'],
                    "mt_image" => ($row['mt_image'] ? $ct_img_url . '/' . $row['mt_image']."?cache=".strtotime($row['mt_udate']) : $ct_member_no_img_url),
                    "rpt_content" => "신고된 게시글입니다.",
                    "rpt_wdate" => substr($row['rpt_wdate'],0,10),
                    "rpt_score" => round($row['rpt_score'],1),
                    "rpt_image" => $rpt_image,
                    "pt_opt_value" => $row['pt_opt_value'],
                );
            } else {
                $rpt_image = [];
                for($i=1; $i<=5; $i++) {
                    if($row['rpt_img'.$i] != "") {
                        $rpt_image[]['rpt_img'] = $ct_img_url.'/'.$row['rpt_img'.$i]."?cache=".$row['rpt_wdate'];
                    }
                }
                $arr['review_product'][] = array(
                    "rpt_idx" => $row['idx'],
                    "mt_idx" => $row['mt_idx'],
                    "mt_nickname" => $row['mt_nickname'],
                    "mt_image" => ($row['mt_image'] ? $ct_img_url . '/' . $row['mt_image']."?cache=".strtotime($row['mt_udate']) : $ct_member_no_img_url),
                    "rpt_content" => $row['rpt_content'],
                    "rpt_wdate" => substr($row['rpt_wdate'],0,10),
                    "rpt_score" => round($row['rpt_score'],1),
                    "rpt_image" => $rpt_image,
                    "pt_opt_value" => $row['pt_opt_value'],
                );
            }
        }
    }
    $arr['count'] = (int)$count[0];
    $arr['maxpage'] = (int)$n_page;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 상품 후기 전체 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '상품 후기 전체 리스트', $jwt);
    }
} else {
    echo result_data('false', '상품 후기 리스트가 존재하지 않습니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>