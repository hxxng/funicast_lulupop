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
if($decoded_array['pt_idx']=="") {
    echo result_data('false', '잘못된 접근입니다. pt_idx', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_level = 3 and a1.mt_id = '".$decoded_array['mt_id']."'";
$row = $DB->fetch_assoc($query);

if($row['mt_idx']) {
    $query = "select * from member_draw_t where mt_idx = ".$row['mt_idx'];
    $count = $DB->count_query($query);
    if($count > 0) {
        $query = "select * from product_t where idx = ".$decoded_array['pt_idx'];
        $p_info = $DB->fetch_assoc($query);

        $DB->update_query("member_draw_t", array("mdt_rdate" => "now()"), " mt_idx = ".$row['mt_idx']);

        $ot_code = get_ot_code();
        $ot_pcode = get_ot_pcode();
        $arr = array(
            "ot_code" => $ot_code,
            "mt_idx" => $row['mt_idx'],
            "ot_pay_type" => 9,
            "ot_status" => 2,
            "ot_name" => $decoded_array['ot_name'],
            "ot_hp" => $decoded_array['ot_hp'],
            "ot_zip" => $decoded_array['ot_zip'],
            "ot_add1" => $decoded_array['ot_add1'],
            "ot_add2" => $decoded_array['ot_add2'],
            "ot_b_name" => $decoded_array['ot_b_name'],
            "ot_b_hp" => $decoded_array['ot_b_hp'],
            "ot_b_zip" => $decoded_array['ot_b_zip'],
            "ot_b_addr1" => $decoded_array['ot_b_addr1'],
            "ot_b_addr2" => $decoded_array['ot_b_addr2'],
            "ot_requests" => $decoded_array['ot_requests'],
            "ot_wdate" => "now()",
            "ot_pt_name" => $p_info['pt_title'],
            "ot_qty" => 1,
        );
        $arr2 = array(
            "ot_code" => $ot_code,
            "ot_pcode" => $ot_pcode,
            "mt_idx" => $row['mt_idx'],
            "pt_idx" => $decoded_array['pt_idx'],
            "pt_code" => $p_info['pt_code'],
            "pt_title" => $p_info['pt_title'],
            "ct_opt_qty" => 1,
            "ct_wdate" => "now()",
            "ct_select" => 2,
            "ct_status" => 2,
        );
        $DB->insert_query("order_t", $arr);
        $DB->insert_query("cart_t", $arr2);
    } else {
        echo result_data("false", "뽑기한 상품이 없습니다.", "");
        exit;
    }

    unset($arr);
    $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = ".$row['mt_idx']." and cart_t.ot_code = '".$ot_code."'";
    $sql_query = $query." order by ot_wdate desc";
    $list_ot = $DB->select_query($sql_query);
    $list_fetch = $DB->fetch_assoc($sql_query);
    if($list_ot) {
        foreach ($list_ot as $row_ot) {
            $query_p = "select * from product_t where idx = " . $row_ot['pt_idx'];
            $p_info = $DB->fetch_assoc($query_p);
            if($p_info['pt_sale_chk'] == "Y") {
                //할인 전 금액
                $ct_opt_before_price = (int)$p_info['pt_selling_price'];
            } else {
                $ct_opt_before_price = $row_ot['ct_opt_price'];
            }
            if ($p_info) {
                $items[] = array(
                    'pt_idx' => $row_ot['pt_idx'],
                    'pt_title' => $row_ot['pt_title'],
                    'ct_opt_name' => $row_ot['ct_opt_name'],
                    'ct_opt_value' => $row_ot['ct_opt_value'],
                    'ct_opt_qty' => (int)$row_ot['ct_opt_qty'],
                    'ct_price' => (int)$row_ot['ct_price'],
                    'pt_image1' => ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".time() : $ct_no_img_url),
                );
            }
            $product_price += $row_ot['ct_price'];

            if($p_info['pt_stock_chk'] == "Y") {
                //재고 빼기
                $DB->update_query('product_t', array("pt_stock" => $p_info['pt_stock'] - $row_ot['ct_opt_qty']), " idx = ".$row_ot['pt_idx']);
            }

            $arr['info'] = array(
                'ot_wdate' => str_replace('-', '.', substr($list_fetch['ot_wdate'], 0, 10)),
                'ot_pay_type' => $arr_ct_method[$list_fetch['ot_pay_type']],
                'ot_use_point' => ($list_fetch['ot_use_point'] == null ? 0 : (int)$list_fetch['ot_use_point']),
                'ot_use_coupon_price' => ($list_fetch['ot_use_coupon_price'] == null ? 0 : (int)$list_fetch['ot_use_coupon_price']),
                'ot_price' => (int)$list_fetch['ot_price'],                //주문금액
                'ot_product_price' => (int)$product_price,    //상품금액
                'ot_delivery_charge' => ($list_fetch['ot_delivery_charge'] == 0 ? "무료배송" : (int)$list_fetch['ot_delivery_charge']),
                'ot_point' => (int)$list_fetch['ot_point']
            );
            $arr['orderer'] = array(
                'ot_name' => $list_fetch['ot_name'],
                'ot_hp' => $list_fetch['ot_hp'],
                'ot_zip' => $list_fetch['ot_zip'],
                'ot_add1' => $list_fetch['ot_add1'],
                'ot_add2' => $list_fetch['ot_add2'],
            );
            $arr['recipient'] = array(
                'ot_b_name' => $list_fetch['ot_b_name'],
                'ot_b_hp' => $list_fetch['ot_b_hp'],
                'ot_b_zip' => $list_fetch['ot_b_zip'],
                'ot_b_addr1' => $list_fetch['ot_b_addr1'],
                'ot_b_addr2' => $list_fetch['ot_b_addr2'],
                'ot_requests' => $list_fetch['ot_requests'],
            );

            $ot_deposit_date = date("Y-m-d H:i:s",strtotime("+1 week", strtotime($list_fetch['ot_wdate'])));
            if($list_fetch['ot_pay_type'] == 1) {
                $query = "select * from policy_t where idx = 1";
                $policy = $DB->fetch_assoc($query);
                $arr['deposit'] = array(
                    "pt_account_name" => $policy['pt_account_name'],
                    "pt_account_bank" => $policy['pt_account_bank'],
                    "pt_account_number" => $policy['pt_account_number'],
                    "ot_deposit_date" => $ot_deposit_date,
                );
            }
            $arr['items'] = $items;
        }
    }

    $payload['data'] = $arr;

    if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
        echo result_data('true', '[debug] 뽑기 선택상품 배송 요청', $payload);
    } else {
        $jwt = JWT::encode($payload, $secret_key);
        echo result_data('true', '뽑기 선택상품 배송 요청', $jwt);
    }
} else {
    echo result_data('false', '존재하지 않는 회원입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>