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
if($decoded_array['filter']=="") {
    echo result_data('false', '잘못된 접근입니다. filter', '');
    exit;
}

$item_count = trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select * from trade_t where tt_status = 1 and mt_idx = ".$row['mt_idx'];
    if($decoded_array['filter'] == "sale") {
        $query .= " and tt_sale_status = 1";
    } else if($decoded_array['filter'] == "soldout") {
        $query .= " and tt_sale_status = 2";
    }
    $query .= " order by tt_wdate desc";
    $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
    $count = $DB->count_query($query);
    if($list) {
        foreach ($list as $row_p) {
            if($row_p['tt_sale_status'] == 1) {
                $tt_sale_status = "판매중";
            } else {
                $tt_sale_status = "판매완료";
            }
            if($row_p['tt_img1']) {
                $tt_img = $ct_img_url."/".$row_p['tt_img1']."?cache=".strtotime($row_p['tt_udate']);
            } else {
                $tt_img = null;
            }
            $query = "select * from product_category_t where idx = ".$row_p['tt_cate_idx'];
            $pc_name = $DB->fetch_assoc($query);

            $arr['list'][] = array(
                "tt_idx" => $row_p['idx'],
                "tt_cate_idx" => $row_p['tt_cate_idx'],
                "tt_cate_name" => $pc_name['pc_name'],
                "tt_title" => $row_p['tt_title'],
                "mt_idx" => $row_p['mt_idx'],
                "tt_sale_status" => $tt_sale_status,
                "tt_price" => (int)$row_p['tt_price'],
                "tt_img" => $tt_img,
            );
        }
    }

    $arr['count'] = (int)$count;
    $n_page = ceil($count / 20);
    $arr['maxpage'] = (int)$n_page;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 판매 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '판매 리스트', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>