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

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row_m = $DB->fetch_assoc($query);
if($row_m['mt_idx']) {
    $query = "select product_t.*, wish_product_t.idx as wpt_idx from wish_product_t left join product_t on product_t.idx = wish_product_t.pt_idx 
            where pt_show = 'Y' and (pt_sale_now = 'Y' or pt_sale_now = 'N') and wpt_status = 'Y' and wish_product_t.mt_idx = ".$row_m['mt_idx'];
    $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
    $count = $DB->count_query($query);
    $n_page = ceil($count[0] / 20);

    if($list) {
        foreach ($list as $row) {
            $arr['list'][] = array(
                "wpt_idx" => $row['wpt_idx'],
                "pt_idx" => $row['idx'],
                "pt_title" => $row['pt_title'],
                "pt_image1" => $ct_img_url."/".$row['pt_image1']."?cache=".$row['pt_udate'],
                "pt_selling_price" => $row['pt_selling_price'],
                "pt_sale_chk" => $row['pt_sale_chk'],
                "pt_discount_per" => $row['pt_discount_per'],
                "pt_price" => $row['pt_price'],
            );
        }
    } else {
        $arr['list'] = [];
    }

    $arr['count'] = (int)$count;
    if($n_page < 1) {
        $n_page = 1;
    }
    $arr['maxpage'] = (int)$n_page;

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 나의 찜 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '나의 찜 리스트', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>