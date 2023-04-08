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

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level in (3,5) and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $query = "
        select *, a1.idx as ct_idx from cart_t a1
        where a1.mt_idx = '" . $row['mt_idx'] . "' and ct_select = 0 and ct_status = 0 and ct_direct != 1";
        $count = $DB->count_query($query);

        $arr = array();
        unset($list);
        $sql_query = $query . " order by a1.idx asc ";
        $list = $DB->select_query($sql_query);

        if ($list) {
            $qq = 0;
            $qq_index = 0;
            $sum_price = $sum_send_cost = 0;
            $sell_price = $send_cost = 0;

            foreach ($list as $row_c) {
                $query_pt = "select * from product_t where idx = '" . $row_c['pt_idx'] . "'  and pt_show = 'Y' and pt_sale_now = 'Y'";
                $row_pt = $DB->fetch_query($query_pt);
                if ($row_pt['idx']) {
                    $arr['policy'] = array(
                        "pt_delivery_price" => (int)$row_pt['pt_delivery_price'],
                        "pt_free_delivery_chk" => $row_pt['pt_delivery_free_chk'],
                        "pt_free_delivery_price" => (int)$row_pt['pt_delivery_free_price'],
                    );

                    $ct_opt_name_ex = explode('/', $row_c['ct_opt_name']);
                    $ct_opt_value_ex = explode('/', $row_c['ct_opt_value']);

                    $pt_price = $row_c['pt_price'];
                    $ot_price_sale = $row_pt['pt_selling_price'];
                    $ct_price = $row_pt['pt_price'] * $row_c['ct_opt_qty'];

                    $sum_price += $ct_price;
                    $sell_price += $ct_price;

                    $arr['items'][] = array(
                        'ct_idx' => $row_c['ct_idx'],
                        'ot_pcode' => $row_c['ot_pcode'],
                        'pt_idx' => $row_c['pt_idx'],
                        'pt_title' => $row_c['pt_title'],
                        "pt_selling_price" => (int)$ot_price_sale,
                        'pt_sale_type_chk' => $row_c['pt_sale_type_chk'],
                        'pt_discount_per' => (int)$row_pt['pt_discount_per'],
                        'pt_price' => (int)$pt_price,
                        'ct_opt_name' => $row_c['ct_opt_name'],
                        'ct_opt_value' => $row_c['ct_opt_value'],
                        'ct_opt_qty' => (int)$row_c['ct_opt_qty'],
                        'pt_image1' => $ct_img_url . '/' . $row_pt['pt_image1']."?cache=".strtotime($row_pt['pt_udate']),
                        'num' => $qq_index,
                        'sell_price' => $sell_price,
                    );
                    $qq_index++;
                } else {
                    $arr['policy'] = null;
                    $arr['items'] = [];
                    $DB->del_query('cart_t', "idx = '" . $row['idx'] . "'");
                }
                $qq++;
            }
            $arr['sum_price'] = $sum_price;
        } else {
            $arr['policy'] = null;
            $arr['items'] = [];
            $arr['sum_price'] = 0;
        }
        $arr['count'] = (int)$count;
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 장바구니 리스트', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '장바구니 리스트', $jwt);
        }
    } else {
        $query = "
            select *, a1.idx as ct_idx from cart_t a1
            where a1.nmt_id = '" . $decoded_array['mt_id'] . "' and ct_select = 0 and ct_status = 0 and ct_direct != 1";
        $count = $DB->count_query($query);

        $arr = array();
        unset($list);
        $sql_query = $query . " order by a1.idx asc ";
        $list = $DB->select_query($sql_query);

        if ($list) {
            $qq = 0;
            $qq_index = 0;
            $sum_price = $sum_send_cost = 0;
            $sell_price = $send_cost = 0;

            foreach ($list as $row_c) {
                $query_pt = "select * from product_t where idx = '" . $row_c['pt_idx'] . "'  and pt_show = 'Y' and pt_sale_now = 'Y'";
                $row_pt = $DB->fetch_query($query_pt);
                if ($row_pt['idx']) {
                    $query = "select * from policy_t where idx = 1";
                    $row_p = $DB->fetch_query($query);

                    $arr['policy'] = array(
                        "pt_delivery_price" => (int)$row_p['pt_delivery_price'],
                        "pt_free_delivery_chk" => $row_p['pt_free_delivery_chk'],
                        "pt_free_delivery_price" => (int)$row_p['pt_free_delivery_price'],
                    );

                    $ct_opt_name_ex = explode('/', $row_c['ct_opt_name']);
                    $ct_opt_value_ex = explode('/', $row_c['ct_opt_value']);

                    $pt_price = $row_c['pt_price'];
                    $ot_price_sale = $row_pt['pt_selling_price'];
                    $ct_price = $row_pt['pt_price'] * $row_c['ct_opt_qty'];

                    $sum_price += $ct_price;
                    $sell_price += $ct_price;

                    $arr['items'][] = array(
                        'ct_idx' => $row_c['ct_idx'],
                        'ot_pcode' => $row_c['ot_pcode'],
                        'pt_idx' => $row_c['pt_idx'],
                        'pt_title' => $row_c['pt_title'],
                        "pt_selling_price" => (int)$ot_price_sale,
                        'pt_sale_type_chk' => $row_p['pt_sale_type_chk'],
                        'pt_discount_per' => (int)$row_pt['pt_discount_per'],
                        'pt_price' => (int)$pt_price,
                        'ct_opt_name' => $row_c['ct_opt_name'],
                        'ct_opt_value' => $row_c['ct_opt_value'],
                        'ct_opt_qty' => (int)$row_c['ct_opt_qty'],
                        'pt_image1' => $ct_img_url . '/' . $row_pt['pt_image1']."?cache=".strtotime($row_pt['pt_udate']),
                        'num' => $qq_index,
                        'sell_price' => $sell_price,
                    );
                    $qq_index++;
                } else {
                    $DB->del_query('cart_t', "idx = '" . $row['idx'] . "'");
                }
                $qq++;
            }
            $arr['sum_price'] = $sum_price;
        } else {
            $arr['policy'] = null;
            $arr['items'] = [];
        }
        $arr['count'] = (int)$count;
        $payload['data'] = $arr;

        if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
            echo result_data('true', '[debug] 비회원 장바구니 리스트', $payload);
        } else {
            $jwt = JWT::encode($payload, $secret_key);
            echo result_data('true', '비회원 장바구니 리스트', $jwt);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>