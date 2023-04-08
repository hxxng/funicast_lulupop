<?
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
use \Firebase\JWT\JWT;


if($decoded_array['debug_jwt'] && ($decoded_array['debug_jwt']!=DEBUG_JWT)){
    echo result_data('false', '정상적인 접근이 아닙니다. DEBUG_JWT', '');
    exit();
}

$item_count 		= trim($decoded_array['item_count']) ? trim($decoded_array['item_count']) : 0;

$query = "select a1.* from product_t a1 left join cart_t on a1.idx = pt_idx and ct_select = 2 where pt_show = 'Y' and (pt_sale_now = 'Y' or pt_sale_now = 'N') ";
$count_query = "SELECT COUNT(idx) AS cnt 
        FROM ( select a1.idx from  product_t a1 left join cart_t on a1.idx = pt_idx where pt_show = 'Y' and (pt_sale_now = 'Y' or pt_sale_now = 'N') ";
if($decoded_array['pct_idx']) {
    $query .= " and pct_idx = ".$decoded_array['pct_idx'];
    $count_query .= " and pct_idx = ".$decoded_array['pct_idx'];
}
if($decoded_array['pct_m_idx']) {
    $query .= " and pct_m_idx = ".$decoded_array['pct_m_idx'];
    $count_query .= " and pct_m_idx = ".$decoded_array['pct_m_idx'];
}
$count_query .= " group by a1.idx ) A";
$query .= " group by a1.idx ";
if($decoded_array['order']) {
    if($decoded_array['order'] == "hot") {
        $order = " count(pt_idx) desc";
    } else if($decoded_array['order'] == "low") {
        $order = "pt_price ";
    } else if($decoded_array['order'] == "high") {
        $order = "pt_price desc";
    }
    $query .=" order by ".$order;
} else {
    $query .=" order by pt_wdate desc";
}
$query .= " limit ".$item_count.", ".($item_count+20);
$list = $DB->select_query($query);
$count = $DB->fetch_query($count_query);

$n_page = ceil($count[0] / 20);

if($list) {
    unset($arr);
    foreach ($list as $row) {
        $arr['product'][] = array(
            "pt_idx" => $row['idx'],
            "pt_title" => $row['pt_title'],
            "pt_selling_price" => (int)$row['pt_selling_price'],
            "pt_sale_chk" => $row['pt_sale_type_chk'],
            "pt_discount_per" => (int)$row['pt_discount_per'],
            "pt_price" => (int)$row['pt_price'],
            "pt_image1" => $ct_img_url.'/'.$row['pt_image1']."?cache=".strtotime($row['pt_udate']),
        );
    }
    $arr['count'] = (int)$count['cnt'];
    $arr['maxpage'] = (int)$n_page;
    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 상품 서브 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '상품 서브 리스트', $jwt);
    }
} else {
    $arr['count'] = (int)$count['cnt'];
    $arr['maxpage'] = (int)$n_page;
    $arr['product'] = [];
    $payload['data'] = $arr;
    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 상품 서브 리스트', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '상품 서브 리스트', $jwt);
    }
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>