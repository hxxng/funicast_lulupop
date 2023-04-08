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

$query = "select * from policy_t where idx = 1";
$delivery = $DB->fetch_query($query);

$query = "select avg(rpt_score) as rpt_score FROM review_product_t where pt_idx = ".$decoded_array['pt_idx'];
$rpt = $DB->fetch_query($query);

$mt_idx = null;
if($decoded_array['mt_id']) {
    $query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
    $row_m = $DB->fetch_assoc($query);
    if($row_m) {
        $mt_idx = $row_m['mt_idx'];
        if($row_m['mt_level'] == 3) {
            $query = "select * from wish_product_t where pt_idx = ".$decoded_array['pt_idx']." and mt_idx = ".$row_m['mt_idx'];
            $wish = $DB->fetch_assoc($query);
            if($wish['idx'] > 0) {
                $wpt_status = $wish['wpt_status'];
            } else {
                $wpt_status = "N";
            }
        } else {
            $wpt_status = "N";
        }
    } else {
        $wpt_status = "N";
    }
} else {
    $wpt_status = "N";
}

$query = "select * from product_t a1 where pt_show = 'Y' and (pt_sale_now = 'Y' or pt_sale_now = 'N') and idx = ".$decoded_array['pt_idx'];
$row = $DB->fetch_query($query);

if($row) {
    $DB->update_query("product_t", array("pt_view" => $row['pt_view'] + 1), " idx = ".$row['idx']);
    $DB->insert_query("product_click_log_t", array("pt_idx" => $row['idx'], "mt_idx" => $mt_idx, "pclt_wdate" => "now()"));

    $info = array(
        "pt_idx" => $row['idx'],
        "pt_title" => $row['pt_title'],
        "pt_selling_price" => (int)$row['pt_selling_price'],
        "pt_sale_chk" => $row['pt_sale_type_chk'],
        "pt_discount_per" => (int)$row['pt_discount_per'],
        "pt_price" => (int)$row['pt_price'],
        "pt_delivery_price" => (int)$row['pt_delivery_price'],
        "pt_free_delivery_chk" => $row['pt_delivery_free_chk'],
        "pt_free_delivery_price" => (int)$row['pt_delivery_free_price'],
        "rpt_score" => round($rpt['rpt_score'],1),
        "pt_sale_now" => $row['pt_sale_now'],
    );

    for($i=1; $i<=10; $i++) {
        if($row['pt_image'.$i] != ""){
            $image[]["pt_image"] = $ct_img_url.'/'.$row['pt_image'.$i]."?cache=".strtotime($row['pt_udate']);
        }
    }

    $query = "select * from product_option_t where pt_idx = ".$decoded_array['pt_idx']." group by pot_name";
    $option_list = $DB->select_query($query);
    if($option_list) {
        for($i=0; $i<3; $i++) {
            $query2 = "select * from product_option_t where pt_idx = ".$decoded_array['pt_idx']." and pot_name = '".$option_list[$i]['pot_name']."'";
            $option_list2 = $DB->select_query($query2);
            if($option_list2) {
                $option["option".($i+1)]['title'] = $option_list[$i]['pot_name'];
                foreach ($option_list2 as $o_row) {
                    $option["option".($i+1)]['item'][]["pot_value"] = $o_row['pot_value'];
                }
            }
        }
    }

    $qq = 0;
    $qq_index = 0;
    $sum_price = $sum_send_cost = 0;
    $sell_price = $send_cost = 0;

    $policy = array(
        "pt_delivery_price" => (int)$row['pt_delivery_price'],
        "pt_free_delivery_chk" => $row['pt_delivery_free_chk'],
        "pt_free_delivery_price" => (int)$row['pt_delivery_free_price'],
    );

    $pt_price = $row['pt_price'];
    $ot_price_sale = $row['pt_selling_price'];
    $ct_price = $row['pt_price'];

    $sum_price += $ct_price;
    $sell_price += $ct_price;

    $items = array(
        'pt_idx' => $row['idx'],
        'pt_title' => $row['pt_title'],
        "pt_selling_price" => (int)$ot_price_sale,
        'pt_sale_type_chk' => $row['pt_sale_type_chk'],
        'pt_discount_per' => (int)$row['pt_discount_per'],
        'pt_price' => (int)$pt_price,
        'ct_opt_name' => $row['ct_opt_name'],
        'pt_image1' => $ct_img_url.'/'.$row['pt_image1'],
        'num' => $qq_index,
        'sell_price' => $sell_price,
    );
    $items['sum_price'] = $sum_price;

    $wish_chk = $wpt_status;

    $arr['product'] = array(
        "info" => $info,
        "detail" => $row['pt_content'],
        "pt_delivery_comment" => $row['pt_delivery_comment'],
        "pt_refund_comment" => $row['pt_refund_comment'],
        "pt_image" => $image,
        "option" => $option,
        "policy" => $policy,
        "items" => $items,
        "wish_chk" => $wish_chk,
    );

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 상품 상세보기', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '상품 상세보기', $jwt);
    }
} else {
    echo result_data('false', '상품이 존재하지 않습니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>