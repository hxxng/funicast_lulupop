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
    $query = "select cart_t.*, (SELECT pt_image1 FROM product_t WHERE idx=pt_idx) as pt_image1 from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_status = 6 and cart_t.mt_idx = ".$row_m['mt_idx'];
    $list = $DB->select_query($query." limit ".$item_count.", ".($item_count+20));
    $count = $DB->count_query($query);
    $n_page = ceil($count[0] / 20);
    if($list) {
        foreach ($list as $row) {
            $query = "select * from review_product_t where ot_pcode = '".$row['ot_pcode']."'";
            $review = $DB->fetch_assoc($query);
            if($review) {
                $review_idx = $review['idx'];
            } else {
                $review_idx = null;
            }
            $arr['list'][] = array(
                "ot_code" => $row['ot_code'],
                "ot_pcode" => $row['ot_pcode'],
                "pt_idx" => $row['pt_idx'],
                "pt_image1" => $ct_img_url."/".$row['pt_image1']."?cache=".strtotime($row['pt_udate']),
                "pt_title" => $row['pt_title'],
                "ct_opt_value" => $row['ct_opt_value'],
                "ct_opt_qty" => (int)$row['ct_opt_qty'],
                "ct_price" => (int)$row['ct_price'],
                "review_idx" => $review_idx,
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
        echo result_data('true', '[debug] 나의 후기 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '나의 후기 리스트', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>