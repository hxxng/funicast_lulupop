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
if($decoded_array['ot_pcode']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_pcode', '');
    exit;
}
if($decoded_array['filter']=="") {
    echo result_data('false', '잘못된 접근입니다. filter', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level in (3,5)
";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($decoded_array['filter'] == "cancel") {
        $ct_status = "ct_status in (7,8) and ";
        $title = "취소";
    } else if($decoded_array['filter'] == "exchange") {
        $ct_status = "ct_status in (80,81,82) and ";
        $title = "교환";
    } else if($decoded_array['filter'] == "refund") {
        $ct_status = "ct_status in (90,91) and ";
        $title = "반품";
    }
    if($row['mt_level'] == 3) {
        $arr = array();

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ".$ct_status." order_t.mt_idx = " . $row['mt_idx'] . " and cart_t.ot_pcode = '" . $decoded_array['ot_pcode'] . "'";
        $sql_query = $query . " order by ot_wdate desc";
        $list_ot = $DB->select_query($sql_query);
        $list_fetch = $DB->fetch_assoc($sql_query);

        if($list_fetch['ct_request_type'] == 1) {
            $ct_request_type = "상품 오배송/불량";
        } else if($list_fetch['ct_request_type'] == 2) {
            $ct_request_type = "물건 퀄리티문제";
        } else {
            $ct_request_type = "기타";
        }

        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_p = "select * from product_t where idx = " . $row_ot['pt_idx'];
                $p_info = $DB->fetch_assoc($query_p);
                if ($p_info) {
                    $items[] = array(
                        'ot_pcode' => $row_ot['ot_pcode'],
                        'pt_idx' => $row_ot['pt_idx'],
                        'pt_code' => $row_ot['pt_code'],
                        'pt_title' => $row_ot['pt_title'],
                        'ct_opt_name' => $row_ot['ct_opt_name'],
                        'ct_opt_value' => $row_ot['ct_opt_value'],
                        'pt_price' => (int)$row_ot['pt_price'],
                        'ct_opt_qty' => (int)$row_ot['ct_opt_qty'],
                        'ct_price' => (int)$row_ot['ct_price'],
                        'pt_image1' => ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".strtotime($p_info['pt_udate']) : $ct_no_img_url),
                        'ct_status_txt' => $arr_ct_status[$row_ot['ct_status']],
                    );
                }
                $product_price += $row_ot['ct_price'];

                if($title == "반품") {
                    $ct_request_status = $arr_refund_status[$list_fetch['ct_request_status']];
                } else if($title == "교환") {
                    $ct_request_status = $arr_exchange_status[$list_fetch['ct_request_status']];
                } else {
                    $ct_request_status = "취소완료";
                }

                $arr['info'] = array(
                    'ct_request_status' => $ct_request_status,
                    'ot_code' => $list_fetch['ot_code'],
                    'mt_idx' => $list_fetch['mt_idx'],
                    'ot_wdate' => substr($list_fetch['ot_wdate'], 0, 10),
                    'ot_pay_type' => $arr_ct_method[$list_fetch['ot_pay_type']],
                    'ot_use_point' => (int)$list_fetch['ot_use_point'],
                    'ot_use_coupon_price' => (int)$list_fetch['ot_use_coupon_price'],
                    'ot_price' => (int)$list_fetch['ot_price'],                //주문금액
                    'ot_product_price' => $product_price,    //상품금액
                    'ot_delivery_charge' => (int)$list_fetch['ot_delivery_charge'],
                    'ot_point' => (int)$list_fetch['ot_point']
                );
                $arr['reason'] = array(
                    'type' => $ct_request_type,
                    'ct_request_reason' => $list_fetch['ct_request_reason'],
                );

                if($decoded_array['filter'] == "exchange") {
                    $arr['delivery'] = array(
                        'ct_delivery_com' => $list_fetch['ct_delivery_com'],
                        'ct_delivery_number' => $list_fetch['ct_delivery_number'],
                    );
                    $arr['exchange_delivery'] = array(
                        'ct_request_name' => $list_fetch['ct_request_name'],
                        'ct_request_hp' => $list_fetch['ct_request_hp'],
                        'ct_request_zip' => $list_fetch['ct_request_zip'],
                        'ct_request_addr1' => $list_fetch['ct_request_addr1'],
                        'ct_request_addr2' => $list_fetch['ct_request_addr2'],
                        'ct_request_delivery' => $list_fetch['ct_request_delivery'],
                    );
                    $arr['collect_delivery'] = array(
                        'ct_collect_name' => $list_fetch['ct_collect_name'],
                        'ct_collect_hp' => $list_fetch['ct_collect_hp'],
                        'ct_collect_zip' => $list_fetch['ct_collect_zip'],
                        'ct_collect_addr1' => $list_fetch['ct_collect_addr1'],
                        'ct_collect_addr2' => $list_fetch['ct_collect_addr2'],
                    );
                } else if($decoded_array['filter'] == "cancel") {
                    $arr['account'] = array(
                        'ct_account_holder' => $list_fetch['ct_account_holder'],
                        'ct_bank_name' => $list_fetch['ct_bank_name'],
                        'ct_bank_number' => $list_fetch['ct_bank_number'],
                    );
                } else if($decoded_array['filter'] == "refund") {
                    $arr['delivery'] = array(
                        'ct_collect_com' => $list_fetch['ct_collect_com'],
                        'ct_collect_number' => $list_fetch['ct_collect_number'],
                    );
                    $arr['collect_delivery'] = array(
                        'ct_collect_name' => $list_fetch['ct_collect_name'],
                        'ct_collect_hp' => $list_fetch['ct_collect_hp'],
                        'ct_collect_zip' => $list_fetch['ct_collect_zip'],
                        'ct_collect_addr1' => $list_fetch['ct_collect_addr1'],
                        'ct_collect_addr2' => $list_fetch['ct_collect_addr2'],
                    );
                    $arr['account'] = array(
                        'ct_account_holder' => $list_fetch['ct_account_holder'],
                        'ct_bank_name' => $list_fetch['ct_bank_name'],
                        'ct_bank_number' => $list_fetch['ct_bank_number'],
                    );
                }

                $arr['items'] = $items;
            }
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] '.$title.' 상세보기', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', ''.$title.' 상세보기', $jwt);
            }
        } else {
            echo result_data('false', '존재하지 않는 주문정보입니다.', $arr);
        }
    } else {
        //비회원
        $arr = array();

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ".$ct_status." order_t.nmt_id = '" . $decoded_array['mt_id'] . "' and cart_t.ot_pcode = '" . $decoded_array['ot_pcode'] . "'";
        $sql_query = $query . " order by ot_wdate desc";
        $list_ot = $DB->select_query($sql_query);
        $list_fetch = $DB->fetch_assoc($sql_query);

        if($list_fetch['ct_request_type'] == 1) {
            $ct_request_type = "상품 오배송/불량";
        } else if($list_fetch['ct_request_type'] == 2) {
            $ct_request_type = "물건 퀄리티문제";
        } else {
            $ct_request_type = "기타";
        }

        if ($list_ot) {
            foreach ($list_ot as $row_ot) {
                $query_p = "select * from product_t where idx = " . $row_ot['pt_idx'];
                $p_info = $DB->fetch_assoc($query_p);
                if ($p_info) {
                    $items[] = array(
                        'ot_pcode' => $row_ot['ot_pcode'],
                        'pt_idx' => $row_ot['pt_idx'],
                        'pt_code' => $row_ot['pt_code'],
                        'pt_title' => $row_ot['pt_title'],
                        'ct_opt_name' => $row_ot['ct_opt_name'],
                        'ct_opt_value' => $row_ot['ct_opt_value'],
                        'pt_price' => (int)$row_ot['pt_price'],
                        'ct_opt_qty' => (int)$row_ot['ct_opt_qty'],
                        'ct_price' => (int)$row_ot['ct_price'],
                        'pt_image1' => ($p_info['pt_image1'] ? $ct_img_url . '/' . $p_info['pt_image1']."?cache=".strtotime($p_info['pt_udate']) : $ct_no_img_url),
                        'ct_status_txt' => $arr_ct_status[$row_ot['ct_status']],
                    );
                }
                $product_price += $row_ot['ct_price'];

                if($title == "반품") {
                    $ct_request_status = $arr_refund_status[$list_fetch['ct_request_status']];
                } else if($title == "교환") {
                    $ct_request_status = $arr_exchange_status[$list_fetch['ct_request_status']];
                } else {
                    $ct_request_status = "취소완료";
                }

                $arr['info'] = array(
                    'ct_request_status' => $ct_request_status,
                    'ot_code' => $list_fetch['ot_code'],
                    'mt_idx' => $list_fetch['mt_idx'],
                    'ot_wdate' => substr($list_fetch['ot_wdate'], 0, 10),
                    'ot_pay_type' => $arr_ct_method[$list_fetch['ot_pay_type']],
                    'ot_use_point' => (int)$list_fetch['ot_use_point'],
                    'ot_use_coupon_price' => (int)$list_fetch['ot_use_coupon_price'],
                    'ot_price' => (int)$list_fetch['ot_price'],                //주문금액
                    'ot_product_price' => $product_price,    //상품금액
                    'ot_delivery_charge' => (int)$list_fetch['ot_delivery_charge'],
                    'ot_point' => (int)$list_fetch['ot_point']
                );

                $arr['reason'] = array(
                    'type' => $ct_request_type,
                    'ct_request_reason' => $list_fetch['ct_request_reason'],
                );

                if($decoded_array['filter'] == "exchange") {
                    $arr['delivery'] = array(
                        'ct_delivery_com' => $list_fetch['ct_delivery_com'],
                        'ct_delivery_number' => $list_fetch['ct_delivery_number'],
                    );
                    $arr['exchange_delivery'] = array(
                        'ct_request_name' => $list_fetch['ct_request_name'],
                        'ct_request_hp' => $list_fetch['ct_request_hp'],
                        'ct_request_zip' => $list_fetch['ct_request_zip'],
                        'ct_request_addr1' => $list_fetch['ct_request_addr1'],
                        'ct_request_addr2' => $list_fetch['ct_request_addr2'],
                        'ct_request_delivery' => $list_fetch['ct_request_delivery'],
                    );
                    $arr['collect_delivery'] = array(
                        'ct_collect_name' => $list_fetch['ct_collect_name'],
                        'ct_collect_hp' => $list_fetch['ct_collect_hp'],
                        'ct_collect_zip' => $list_fetch['ct_collect_zip'],
                        'ct_collect_addr1' => $list_fetch['ct_collect_addr1'],
                        'ct_collect_addr2' => $list_fetch['ct_collect_addr2'],
                    );
                } else if($decoded_array['filter'] == "cancel") {
                    $arr['account'] = array(
                        'ct_account_holder' => $list_fetch['ct_account_holder'],
                        'ct_bank_name' => $list_fetch['ct_bank_name'],
                        'ct_bank_number' => $list_fetch['ct_bank_number'],
                    );
                } else if($decoded_array['filter'] == "refund") {
                    $arr['delivery'] = array(
                        'ct_collect_com' => $list_fetch['ct_collect_com'],
                        'ct_collect_number' => $list_fetch['ct_collect_number'],
                    );
                    $arr['collect_delivery'] = array(
                        'ct_collect_name' => $list_fetch['ct_collect_name'],
                        'ct_collect_hp' => $list_fetch['ct_collect_hp'],
                        'ct_collect_zip' => $list_fetch['ct_collect_zip'],
                        'ct_collect_addr1' => $list_fetch['ct_collect_addr1'],
                        'ct_collect_addr2' => $list_fetch['ct_collect_addr2'],
                    );
                    $arr['account'] = array(
                        'ct_account_holder' => $list_fetch['ct_account_holder'],
                        'ct_bank_name' => $list_fetch['ct_bank_name'],
                        'ct_bank_number' => $list_fetch['ct_bank_number'],
                    );
                }

                $arr['items'] = $items;
            }
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 '.$title.' 상세보기', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 '.$title.' 상세보기', $jwt);
            }
        } else {
            echo result_data('false', '존재하지 않는 주문정보입니다.', $arr);
        }
    }
} else {
    echo result_data('false', '존재하지 않는 회원정보입니다.', $arr);
}

include $_SERVER['DOCUMENT_ROOT']."/tail_inc.php";
?>