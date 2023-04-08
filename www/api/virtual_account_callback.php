<?php
include $_SERVER['DOCUMENT_ROOT']."/lib_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/api_inc.php";
include $_SERVER['DOCUMENT_ROOT']."/lib/Point_class.php";

$postData = file_get_contents('php://input');
$json = json_decode($postData);

if ($json->status == 'DONE') {
    // handle deposit result
    $ot_code = $json->orderId;

    $test = "select * from order_t where ot_code = '".$ot_code."'";
    $row = $DB->fetch_assoc($test);

    if($row['mt_idx']) {
        $member = $DB->fetch_assoc("select * from member_t where idx = ".$row['mt_idx']);
        $test = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.mt_idx = " . $row['mt_idx'] . " and cart_t.ot_code = '" . $ot_code . "'";
    } else {
        $member = $DB->fetch_assoc("select * from member_t where mt_id = '".$row['nmt_id']."'");
        $test = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where order_t.nmt_id = '" . $row['nmt_id'] . "' and cart_t.ot_code = '" . $ot_code . "'";
    }

    $sql_test = $test . " order by ot_wdate desc";
    $list_ot = $DB->select_query($sql_test);
    $list_fetch = $DB->fetch_assoc($sql_test);

    if ($list_ot) {
        foreach ($list_ot as $row_ot) {
            $test_p = "select * from product_t where idx = " . $row_ot['pt_idx'];
            $p_info = $DB->fetch_assoc($test_p);
            if ($p_info['pt_sale_chk'] == "Y") {
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

            if ($p_info['pt_stock_chk'] == "Y") {
                //재고 빼기
                $DB->update_query('product_t', array("pt_stock" => $p_info['pt_stock'] - $row_ot['ct_opt_qty']), " idx = " . $row_ot['pt_idx']);
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

            $arr['items'] = $items;
        }

        //포인트차감쿼리
        $objPoint = new Point_class(array('db' => $DB, 'mt_idx' => $list_fetch['mt_idx']));
        if ($list_fetch['ot_use_point'] > 0) $objPoint->insert_point(array('point' => (-1) * $list_fetch['ot_use_point'], 'ot_code' => $list_fetch['ot_code'], 'plt_memo' => "주문번호 " . $list_fetch['ot_code'] . " 결제", 'plt_status'=>2));

        $arr2 = array(
            "ot_status" => 2,
            "ot_pdate" => "now()",
        );

        $DB->update_query('order_t', $arr2, " ot_code = '".$ot_code."'");
        $DB->update_query('cart_t', array("ct_select" => 2, "ct_status" => 2, "ct_pdate" => "now()"), " ot_code = '".$ot_code."'");
    }

    if($member['mt_pushing'] == "Y" || $member['mt_pushing2'] == "Y") {
        $chk = "Y";
    } else {
        $chk = "N";
    }

    $result = new stdClass();
    $result->info = $arr['info'];
    $result->orderer = $arr['orderer'];
    $result->recipient = $arr['recipient'];
    $result->items = $items;

    $token_list = array($member['mt_fcm']);
    $message = "구매하신 상품이 배송 준비중입니다.";
    $title = "룰루팝";

    $message_status = send_notification2($token_list, $title, $message, "Product_Order_Finish_Page", json_encode($result), $chk);

    if ($message_status) {
        unset($arr_query);
        $plt_set = array(
            'plt_title' => $title,
            'plt_content' => $message,
            'plt_table' => "order_t_pay",
            'plt_type' => 2,
            'plt_index' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK),
            'mt_idx' => 1,
            'op_idx' => $member['idx'],
            'plt_wdate' => 'now()'
        );
        $DB->insert_query("pushnotification_log_t", $plt_set);
    }
}
include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>

