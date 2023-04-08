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
if($decoded_array['ot_code']=="") {
    echo result_data('false', '잘못된 접근입니다. ot_code', '');
    exit;
}

$query = "
    select *, a1.idx as mt_idx from member_t a1
    where a1.mt_id = '".$decoded_array['mt_id']."' and mt_level in (3,5)
";
$row = $DB->fetch_query($query);

if($row['mt_idx']) {
    if($row['mt_level'] == 3) {
        $arr = array();

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_status in (1,2,3,4,5,6,7,8) and order_t.mt_idx = " . $row['mt_idx'] . " and cart_t.ot_code = '" . $decoded_array['ot_code'] . "'";
        $sql_query = $query . " order by ot_wdate desc";
        $list_ot = $DB->select_query($sql_query);
        $list_fetch = $DB->fetch_assoc($sql_query);
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

                $arr['info'] = array(
                    'ot_status' => $arr_ct_status[$list_fetch['ot_status']],
                    'ot_code' => $list_fetch['ot_code'],
                    'mt_idx' => $list_fetch['mt_idx'],
                    'ot_wdate' => substr($list_fetch['ot_wdate'], 0, 10),
                    'ot_pay_type' => $arr_ct_method[$list_fetch['ot_pay_type']],
                    'ot_use_point' => (int)$list_fetch['ot_use_point'],
                    'ot_use_coupon_price' => (int)$list_fetch['ot_use_coupon_price'],
                    'ot_price' => (int)$list_fetch['ot_price'],                //주문금액
                    'ot_product_price' => $product_price,    //상품금액
                    'ot_delivery_charge' => (int)$list_fetch['ot_delivery_charge'],
                    'ot_point' => (int)$list_fetch['ot_point'],
                    'ct_request_status' => $arr_ct_status[$row_ot['ct_status']],
                );

                $arr['delivery'] = array(
                    'ot_delivery_com' => $list_fetch['ot_delivery_com'],
                    'ot_delivery_number' => $list_fetch['ot_delivery_number'],
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
                    'ot_requests' => $list_fetch['ot_requests'] == "" ? null : $list_fetch['ot_requests'],
                );

                if ($list_fetch['ot_pay_type'] == 1 && $list_fetch['ot_status'] < 3) {
                    $arr['deposit'] = array(
                        "ot_account_num" => $list_fetch['ot_account_num'],
                        "ot_bank" => $list_fetch['ot_bank'],
                        "ot_duedate" => $list_fetch['ot_duedate'],
                    );
                    $arr['barcode'] = null;
                } else if($list_fetch['ot_pay_type'] == 4 && $list_fetch['ot_status'] < 3) {
                    if ($list_fetch['ot_barcode'] != "" && $list_fetch['ot_barcode'] != null) {
                        $arr['barcode'] = $list_fetch['ot_barcode'];
                    } else {
                        $arr['barcode'] = null;
                    }
                    $arr['deposit'] = null;
                } else {
                    $arr['deposit'] = null;
                    $arr['barcode'] = null;
                }

                $arr['items'] = $items;
            }
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 결제 상세보기', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '결제 상세보기', $jwt);
            }
        } else {
            echo result_data('false', '존재하지 않는 주문정보입니다.', $arr);
        }
    } else {
        //비회원
        $arr = array();

        $query = "select * from order_t left join cart_t on cart_t.ot_code = order_t.ot_code where ct_status in (1,2,3,4,5,6,7,8) and order_t.nmt_id = '" . $decoded_array['mt_id'] . "' and cart_t.ot_code = '" . $decoded_array['ot_code'] . "'";
        $sql_query = $query . " order by ot_wdate desc";
        $list_ot = $DB->select_query($sql_query);
        $list_fetch = $DB->fetch_assoc($sql_query);
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

                $arr['info'] = array(
                    'ot_code' => $list_fetch['ot_code'],
                    'nmt_id' => $list_fetch['nmt_id'],
                    'ot_wdate' => substr($list_fetch['ot_wdate'], 0, 10),
                    'ot_pay_type' => $arr_ct_method[$list_fetch['ot_pay_type']],
                    'ot_price' => (int)$list_fetch['ot_price'],                //주문금액
                    'ot_use_point' => (int)$list_fetch['ot_use_point'],
                    'ot_use_coupon_price' => (int)$list_fetch['ot_use_coupon_price'],
                    'ot_point' => (int)$list_fetch['ot_point'],
                    'ot_product_price' => $product_price,    //상품금액
                    'ot_delivery_charge' => (int)$list_fetch['ot_delivery_charge'],
                    'ct_request_status' => $arr_ct_status[$row_ot['ct_status']],
                );

                $arr['delivery'] = array(
                    'ot_delivery_com' => $list_fetch['ot_delivery_com'],
                    'ot_delivery_number' => $list_fetch['ot_delivery_number'],
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

                if ($list_fetch['ot_pay_type'] == 1 && $list_fetch['ot_status'] < 3) {
                    $ot_deposit_date = date("Y-m-d H:i:s", strtotime("+1 week", strtotime($list_fetch['ot_wdate'])));

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
            $payload['data'] = $arr;

            if ($decoded_array['debug_jwt'] == DEBUG_JWT) {
                echo result_data('true', '[debug] 비회원 결제 상세보기', $payload);
            } else {
                $jwt = JWT::encode($payload, $secret_key);
                echo result_data('true', '비회원 결제 상세보기', $jwt);
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