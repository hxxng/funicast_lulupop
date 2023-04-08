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
    $query = "select *, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname from community_t 
        where ct_status = 1 
        and idx not in (SELECT report_idx FROM report_t where reporter_idx=".$row_m['mt_idx']." and rt_table = 'community_t')
        and idx not in (SELECT ht_hide_idx FROM hide_t where mt_idx=".$row_m['mt_idx']." and ht_table = 'community_t')
        and (instr(ct_hashtag, '".$decoded_array['search_txt']."') or instr(ct_title, '".$decoded_array['search_txt']."') or instr(ct_content, '".$decoded_array['search_txt']."'))";
} else {
    $query = "select *, (SELECT mt_nickname FROM member_t WHERE idx=mt_idx) as mt_nickname from community_t 
        where ct_status = 1 
        and (instr(ct_hashtag, '".$decoded_array['search_txt']."') or instr(ct_title, '".$decoded_array['search_txt']."') or instr(ct_content, '".$decoded_array['search_txt']."'))";
}
if($decoded_array['filter']) {
    $query .=" and ct_cate_idx = ".$decoded_array['filter'];
}
$list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
$count = $DB->count_query($query);

$n_page = ceil($count / 20);

if($list) {
    foreach ($list as $row_c) {
        $arr['list'][] = array(
            "ct_idx" => $row_c['idx'],
            "ct_title" => $row_c['ct_title'],
            "mt_nickname" => $row_c['mt_nickname'],
            "ct_img" => $ct_img_url."/".$row_c['ct_img1']."?cache=".strtotime($row_c['ct_udate']),
        );
    }
    $arr['count'] = (int)$count;
    $arr['maxpage'] = (int)$n_page;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 커뮤니티 검색', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '커뮤니티 검색', $jwt);
    }
} else {
    echo result_data("false", "검색결과가 존재하지 않습니다.", "");
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>