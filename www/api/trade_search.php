<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;

if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

if($decoded_array['search_txt']=="") {
    echo result_data('false', '잘못된 접근입니다. search_txt', '');
    exit;
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query_m = "select *, a1.idx as mt_idx from member_t a1 where mt_level = 3 and mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_query($query_m);

if($row_m['mt_idx']) {
    $query = "select *, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname from trade_t 
        where tt_status = 1 
        and idx not in (SELECT report_idx FROM report_t where reporter_idx=".$row_m['mt_idx']." and rt_table = 'trade_t')
        and idx not in (SELECT ht_hide_idx FROM hide_t where mt_idx=".$row_m['mt_idx']." and ht_table = 'trade_t')
        and (instr(tt_hashtag, '".$decoded_array['search_txt']."') or instr(tt_title, '".$decoded_array['search_txt']."') or instr(tt_content, '".$decoded_array['search_txt']."'))";
} else {
    $query = "select *, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname from trade_t 
        where tt_status = 1 
        and (instr(tt_hashtag, '".$decoded_array['search_txt']."') or instr(tt_title, '".$decoded_array['search_txt']."') or instr(tt_content, '".$decoded_array['search_txt']."'))";
}

if($decoded_array['filter']) {
    $query .=" and tt_cate_idx = ".$decoded_array['filter'];
}
$list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
$count = $DB->count_query($query);

$n_page = ceil($count / 20);

if($list) {
    foreach ($list as $row_c) {
        if($row_c['tt_sale_status'] == 1) {
            $tt_sale_status = "판매중";
        } else {
            $tt_sale_status = "판매완료";
        }
        if($row_c['tt_img1']) {
            $tt_img = $ct_img_url."/".$row_c['tt_img1']."?cache=".strtotime($row_c['tt_udate']);
        } else {
            $tt_img = null;
        }
        $arr['list'][] = array(
            "tt_idx" => $row_c['idx'],
            "tt_cate_idx" => $row_c['tt_cate_idx'],
            "tt_title" => $row_c['tt_title'],
            "mt_idx" => $row_c['mt_idx'],
            "mt_nickname" => $row_c['mt_nickname'],
            "tt_sale_status" => $tt_sale_status,
            "tt_price" => (int)$row_c['tt_price'],
            "tt_img" => $tt_img,
        );
    }
    $arr['count'] = (int)$count;
    $arr['maxpage'] = (int)$n_page;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 중고거래 검색', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '중고거래 검색', $jwt);
    }
} else {
    echo result_data("false", "검색결과가 존재하지 않습니다.", "");
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>